<?php
/**
 * Regression checks for design/seo_url duplicate audit.
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

define('DB_PREFIX', 'oc_');
define('DIR_SYSTEM', dirname(__DIR__) . '/upload/system/');
define('DIR_APPLICATION', dirname(__DIR__) . '/upload/admin/');

require DIR_SYSTEM . 'engine/registry.php';
require DIR_SYSTEM . 'engine/model.php';
require DIR_APPLICATION . 'model/design/seo_url.php';

class SeoUrlAuditTestDb {
	public $settings = array();
	public $seo_urls = array();
	public $languages = array(
		1 => 'ru-ru',
		2 => 'uk-ua'
	);

	public $entities = array(
		'product' => array(),
		'category' => array(),
		'manufacturer' => array(),
		'information' => array(),
		'article' => array(),
		'blog_category' => array()
	);

	public function escape($value) {
		return addslashes((string)$value);
	}

	public function query($sql) {
		$result = new stdClass();
		$result->num_rows = 0;
		$result->row = array();
		$result->rows = array();

		if (strpos($sql, 'FROM `oc_seo_url`') !== false && strpos($sql, 'ORDER BY `seo_url_id` ASC') !== false) {
			$result->rows = $this->seo_urls;
			$result->num_rows = count($result->rows);
			$result->row = $result->num_rows ? $result->rows[0] : array();
			return $result;
		}

		if (strpos($sql, 'FROM `oc_language`') !== false) {
			$language_id = $this->extractInt($sql, 'language_id');

			if (isset($this->languages[$language_id])) {
				$result->row = array('code' => $this->languages[$language_id]);
				$result->rows = array($result->row);
				$result->num_rows = 1;
			}

			return $result;
		}

		if (strpos($sql, 'FROM `oc_setting`') !== false) {
			$code = $this->extractString($sql, 'code');
			$key = $this->extractString($sql, 'key');
			$store_ids = $this->extractStoreIds($sql);

			foreach ($store_ids as $store_id) {
				foreach ($this->settings as $setting) {
					if (
						(int)$setting['store_id'] === $store_id
						&& (string)$setting['code'] === $code
						&& (string)$setting['key'] === $key
					) {
						$result->row = array(
							'value' => $setting['value'],
							'serialized' => isset($setting['serialized']) ? (int)$setting['serialized'] : 0
						);
						$result->rows = array($result->row);
						$result->num_rows = 1;
						return $result;
					}
				}
			}

			return $result;
		}

		$entity_map = array(
			'product' => 'product_id',
			'category' => 'category_id',
			'manufacturer' => 'manufacturer_id',
			'information' => 'information_id',
			'article' => 'article_id',
			'blog_category' => 'blog_category_id'
		);

		foreach ($entity_map as $table => $key) {
			if (strpos($sql, 'FROM `oc_' . $table . '`') === false) {
				continue;
			}

			foreach ($this->entities[$table] as $id) {
				$result->rows[] = array($key => (int)$id);
			}

			$result->num_rows = count($result->rows);
			$result->row = $result->num_rows ? $result->rows[0] : array();

			return $result;
		}

		return $result;
	}

	private function extractStoreIds($sql) {
		if (preg_match("/`store_id` IN \(0, '([0-9]+)'\)/", $sql, $match)) {
			$store_id = (int)$match[1];

			return $store_id === 0 ? array(0) : array($store_id, 0);
		}

		return array($this->extractInt($sql, 'store_id'));
	}

	private function extractInt($sql, $field) {
		if (preg_match("/`" . preg_quote($field, '/') . "` = '([0-9]+)'/", $sql, $match)) {
			return (int)$match[1];
		}

		return 0;
	}

	private function extractString($sql, $field) {
		if (preg_match("/`" . preg_quote($field, '/') . "` = '([^']*)'/", $sql, $match)) {
			return stripslashes($match[1]);
		}

		return '';
	}
}

function assertSameValue($expected, $actual, $message) {
	if ($expected !== $actual) {
		throw new RuntimeException(
			$message . ': expected ' . var_export($expected, true)
			. ', got ' . var_export($actual, true)
		);
	}
}

$db = new SeoUrlAuditTestDb();

$db->settings = array(
	array('store_id' => 0, 'code' => 'config', 'key' => 'config_seo_url', 'value' => '1'),
	array('store_id' => 0, 'code' => 'config', 'key' => 'config_seo_language', 'value' => '1'),
	array(
		'store_id' => 0,
		'code' => 'config',
		'key' => 'config_seo_language_prefix',
		'value' => json_encode(array('ru-ru' => 'ru', 'uk-ua' => 'uk')),
		'serialized' => 1
	),
	array('store_id' => 1, 'code' => 'config', 'key' => 'config_seo_url', 'value' => '1'),
	array('store_id' => 1, 'code' => 'config', 'key' => 'config_seo_language', 'value' => '0')
);

$db->seo_urls = array(
	array('seo_url_id' => 1, 'store_id' => 0, 'language_id' => 1, 'query' => 'product_id=10', 'keyword' => 'shared'),
	array('seo_url_id' => 2, 'store_id' => 0, 'language_id' => 2, 'query' => 'product_id=20', 'keyword' => 'shared'),

	array('seo_url_id' => 3, 'store_id' => 0, 'language_id' => 1, 'query' => 'category_id=5', 'keyword' => 'duplicate'),
	array('seo_url_id' => 4, 'store_id' => 0, 'language_id' => 1, 'query' => 'information_id=6', 'keyword' => 'duplicate'),

	array('seo_url_id' => 5, 'store_id' => 0, 'language_id' => 1, 'query' => 'product_id=30', 'keyword' => 'query-a'),
	array('seo_url_id' => 6, 'store_id' => 0, 'language_id' => 1, 'query' => 'product_id=30', 'keyword' => 'query-b'),

	array('seo_url_id' => 7, 'store_id' => 0, 'language_id' => 1, 'query' => 'product_id=99', 'keyword' => 'uk'),
	array('seo_url_id' => 8, 'store_id' => 0, 'language_id' => 2, 'query' => 'common/home', 'keyword' => 'uk'),

	array('seo_url_id' => 9, 'store_id' => 1, 'language_id' => 1, 'query' => 'product_id=40', 'keyword' => 'legacy'),
	array('seo_url_id' => 10, 'store_id' => 1, 'language_id' => 2, 'query' => 'product_id=41', 'keyword' => 'legacy'),
	array('seo_url_id' => 11, 'store_id' => 0, 'language_id' => 1, 'query' => 'product_id=999', 'keyword' => 'orphan-product')
);

$db->entities['product'] = array(10, 20, 30, 40, 41, 99);
$db->entities['category'] = array(5);
$db->entities['information'] = array(6);

$registry = new Registry();
$registry->set('db', $db);

$model = new ModelDesignSeoUrl($registry);
$audit = $model->getSeoUrlAudit();

assertSameValue(11, $audit['summary']['all_rows'], 'Unexpected SEO URL row count');

assertSameValue(false, $audit['issues'][1]['keyword'], 'Shared multilingual keyword was incorrectly marked as duplicate');
assertSameValue(false, $audit['issues'][2]['keyword'], 'Shared multilingual keyword was incorrectly marked as duplicate');

assertSameValue(true, $audit['issues'][3]['keyword'], 'Same-language duplicate keyword was not detected');
assertSameValue(true, $audit['issues'][4]['keyword'], 'Same-language duplicate keyword was not detected');

assertSameValue(true, $audit['issues'][5]['query'], 'Duplicate query was not detected');
assertSameValue(true, $audit['issues'][6]['query'], 'Duplicate query was not detected');

assertSameValue(true, $audit['issues'][7]['prefix'], 'Reserved language prefix collision was not detected');
assertSameValue(false, $audit['issues'][8]['prefix'], 'Own common/home language prefix was incorrectly reported');

assertSameValue(true, $audit['issues'][9]['keyword'], 'Legacy global keyword collision was not detected');
assertSameValue(true, $audit['issues'][10]['keyword'], 'Legacy global keyword collision was not detected');

assertSameValue(true, $audit['issues'][1]['shared_language'], 'Cross-language match was not detected');
assertSameValue(true, $audit['issues'][2]['shared_language'], 'Cross-language match was not detected');
assertSameValue(true, $audit['issues'][9]['shared_language'], 'Legacy cross-language match was not detected');
assertSameValue(true, $audit['issues'][10]['shared_language'], 'Legacy cross-language match was not detected');

assertSameValue(false, $audit['issues'][10]['orphan'], 'Existing product was incorrectly marked orphaned');
assertSameValue(true, $audit['issues'][11]['orphan'], 'Missing product SEO URL was not marked orphaned');

assertSameValue(array(3, 4, 9, 10), $model->getSeoUrlIssueIds('keyword'), 'Keyword issue filter returned wrong rows');
assertSameValue(array(5, 6), $model->getSeoUrlIssueIds('query'), 'Query issue filter returned wrong rows');
assertSameValue(array(7), $model->getSeoUrlIssueIds('prefix'), 'Prefix issue filter returned wrong rows');
assertSameValue(array(1, 2, 9, 10), $model->getSeoUrlIssueIds('shared_language'), 'Cross-language issue filter returned wrong rows');
assertSameValue(array(11), $model->getSeoUrlIssueIds('orphan'), 'Orphan issue filter returned wrong rows');

echo "SEO URL audit regression checks passed\n";
