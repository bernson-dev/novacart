<?php
require dirname(__DIR__) . '/upload/system/library/novacart/seo_url_history.php';

use Novacart\SeoUrlHistory;

function historyRow($keyword, $language = 1, $store = 0, $query = 'product_id=42') {
	return array('store_id' => $store, 'language_id' => $language, 'query' => $query, 'keyword' => $keyword);
}

function expectHistory($expected, $before, $after) {
	$actual = SeoUrlHistory::replacements($before, $after);
	if ($actual !== $expected) {
		throw new RuntimeException('Unexpected SEO history: ' . json_encode($actual));
	}
}

$old = historyRow('old');
$new = historyRow('new');
expectHistory(array($old), array($old), array($new));
expectHistory(array(), array($old), array($old));
expectHistory(array(), array($old), array());
expectHistory(array(), array($old), array(historyRow('new', 2)));
expectHistory(array(), array($old), array(historyRow('new', 1, 2)));
expectHistory(array(), array($old), array(historyRow('new', 1, 0, 'product_id=43')));
expectHistory(array(), array(historyRow('')), array($new));
expectHistory(array($old), array($old, historyRow('old', 2)), array($new, historyRow('old', 2)));
expectHistory(array(), array($old), array($old, $new));
expectHistory(array($old), array($old), array(historyRow('0')));
function expectRoute($expected, $queries) {
	if (SeoUrlHistory::routeFromQueries($queries) !== $expected) {
		throw new RuntimeException('Unexpected historical route: ' . json_encode($queries));
	}
}

expectRoute(array('route' => 'product/product', 'params' => array('product_id' => 42)), array('product_id=42'));
expectRoute(array('route' => 'product/product', 'params' => array('path' => '2_5', 'product_id' => 42)), array('category_id=2', 'category_id=5', 'product_id=42'));
expectRoute(array('route' => 'blog/article', 'params' => array('blog_category_id' => '3', 'article_id' => 7)), array('blog_category_id=3', 'article_id=7'));
expectRoute(array(), array('product_id=42', 'product_id=43'));
expectRoute(array(), array('category_id=2', 'article_id=7'));
expectRoute(array(), array('account/login'));
expectRoute(array(), array('product_id=0'));
// Exercise the actual history SQL paths with a deterministic in-memory adapter.
define('DB_PREFIX', 'oc_');

class HistoryTestDb {
	public $active = array();
	public $history = array();
	public $available = true;

	public function escape($value) {
		return addslashes((string)$value);
	}

	private function result($rows) {
		return (object)array('rows' => array_values($rows), 'row' => $rows ? reset($rows) : array(), 'num_rows' => count($rows));
	}

	public function query($sql) {
		if (strpos($sql, 'information_schema.TABLES') !== false) {
			return $this->result($this->available ? array(array('exists' => 1)) : array());
		}
		$isHistory = strpos($sql, '`oc_seo_url_history`') !== false;
		$rows = $isHistory ? $this->history : $this->active;
		$conditions = array();
		preg_match_all("/(store_id|language_id|keyword|`query`) = '([^']*)'/", $sql, $matches, PREG_SET_ORDER);
		foreach ($matches as $match) {
			$conditions[trim($match[1], '`')] = stripslashes($match[2]);
		}
		$matchesRow = function ($row) use ($conditions) {
			foreach ($conditions as $key => $value) {
				if (strcasecmp((string)$row[$key], $value) !== 0) {
					return false;
				}
			}
			return true;
		};
		if (strpos($sql, 'DELETE FROM') === 0 && $isHistory) {
			$this->history = array_values(array_filter($rows, function ($row) use ($matchesRow) {
				return !$matchesRow($row);
			}));
			return $this->result(array());
		}
		if (strpos($sql, 'SELECT ') === 0) {
			$rows = array_values(array_filter($rows, $matchesRow));
			if (strpos($sql, "keyword <> ''") !== false) {
				$rows = array_values(array_filter($rows, function ($row) {
					return $row['keyword'] !== '';
				}));
			}
			if (strpos($sql, 'SELECT DISTINCT') === 0) {
				$unique = array();
				foreach ($rows as $row) {
					$unique[$row['query']] = $row;
				}
				$rows = array_values($unique);
			}
			return $this->result($rows);
		}
		throw new RuntimeException('Unexpected SQL: ' . $sql);
	}
}

function expectResolved($expected, $actual, $message) {
	if ($expected !== $actual) {
		throw new RuntimeException($message . ': ' . json_encode($actual));
	}
}

$db = new HistoryTestDb();
$db->active = array(historyRow('final'), historyRow('category-new', 1, 0, 'category_id=5'), historyRow('uk-final', 2));
$db->history = array(historyRow('old'), historyRow('middle'), historyRow('category-old', 1, 0, 'category_id=5'), historyRow('old', 2), historyRow('foreign', 1, 8));
$service = new SeoUrlHistory($db);
$product = array('route' => 'product/product', 'params' => array('product_id' => 42));
expectResolved($product, $service->resolvePath(array('old'), 0, 1), 'First historical alias');
expectResolved($product, $service->resolvePath(array('middle'), 0, 1), 'Second historical alias');
expectResolved(array('route' => 'product/product', 'params' => array('path' => '5', 'product_id' => 42)), $service->resolvePath(array('category-old', 'final'), 0, 1), 'Renamed parent category');
expectResolved(array(), $service->resolvePath(array('unknown', 'final'), 0, 1), 'Unknown parent must not resolve');
expectResolved(array(), $service->resolvePath(array('foreign'), 0, 1), 'Store isolation');
expectResolved(array(), $service->resolvePath(array('middle'), 0, 2), 'Language isolation');
expectResolved(array(), $service->resolvePath(array('final'), 0, 1), 'Active alias is not history');
$db->active[] = historyRow('old', 1, 0, 'product_id=99');
expectResolved(array(), $service->resolvePath(array('old'), 0, 1), 'Reused active alias wins');
$service->record(array(), array(historyRow('old', 1, 0, 'product_id=99')));
$db->active = array_values(array_filter($db->active, function ($row) { return $row['query'] !== 'product_id=99'; }));
expectResolved(array(), $service->resolvePath(array('old'), 0, 1), 'Retired reused alias must not resurrect original owner');
expectResolved($product, $service->resolvePath(array('old'), 0, 2), 'Reuse must not delete history in another language');
$db->active = array();
expectResolved(array(), $service->resolvePath(array('middle'), 0, 1), 'Missing current alias');
$db->available = false;
expectResolved(array(), (new SeoUrlHistory($db))->resolvePath(array('middle'), 0, 1), 'Missing table fallback');

echo "SEO URL history regression: OK\n";
