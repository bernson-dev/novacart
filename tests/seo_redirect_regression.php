<?php
define('DB_PREFIX', 'oc_');
require dirname(__DIR__) . '/upload/system/library/novacart/seo_redirect.php';

use Novacart\SeoRedirect;

function check($expected, $actual, $message) {
	if ($expected !== $actual) {
		throw new RuntimeException($message . ': ' . var_export($actual, true));
	}
}

class RedirectTestDb {
	public $available = true;
	public $rules = array();

	public function escape($value) { return addslashes((string)$value); }
	public function query($sql) {
		if (strpos($sql, 'information_schema.TABLES') !== false) {
			return (object)array('num_rows' => (int)$this->available);
		}
		if (!preg_match("/store_id = '([0-9]+)'/", $sql, $store) || !preg_match("/source_hash = '([a-f0-9]+)'/", $sql, $hash)) {
			throw new RuntimeException('Unexpected SQL: ' . $sql);
		}
		preg_match('/language_id IN \(0,([0-9]+)\)/', $sql, $language);
		$rows = array_filter($this->rules, function ($row) use ($store, $hash, $language) {
			return $row['store_id'] === (int)$store[1] && hash('sha256', SeoRedirect::normalizePath($row['source_path'])) === $hash[1] && $row['status'] === 1 && (!$language || in_array($row['language_id'], array(0, (int)$language[1]), true));
		});
		usort($rows, function ($a, $b) { return $b['language_id'] <=> $a['language_id']; });
		return (object)array('num_rows' => count($rows), 'row' => $rows ? reset($rows) : array());
	}
}

function rule($source, $target, $code = 301, $language = 0, $store = 0, $status = 1) {
	return array('source_path' => $source, 'target_path' => $target, 'http_code' => $code, 'language_id' => $language, 'store_id' => $store, 'status' => $status);
}

check('/ua/old', SeoRedirect::normalizePath('/ua/old/'), 'Trailing slash');
check('/%D1%82%D0%B5%D1%81%D1%82', SeoRedirect::normalizePath('/тест'), 'Unicode path');
foreach (array('//evil.test/x', '/a//b', '/%2f%2fevil.test', '/%5cevil.test', '/a/../b', '/%2e%2e/a', '/%0d%0aLocation:', '/x?y=1', '/x#y', '/%252f', 'https://evil.test', '/a%ZZ', '/a\\b') as $bad) {
	check('', SeoRedirect::normalizePath($bad), 'Unsafe path ' . $bad);
}
$db = new RedirectTestDb();
$db->rules = array(rule('/old', '/new/'), rule('/old', '/ua/new', 302, 2), rule('/gone', '', 410), rule('/disabled', '/new', 301, 0, 0, 0), rule('/foreign', '/new', 301, 0, 9));
$service = new SeoRedirect($db);
check(array('code' => 301, 'target' => '/new/'), $service->resolve('/old/', 0, 1), 'Global rule');
check(array('code' => 302, 'target' => '/ua/new'), $service->resolve('/old', 0, 2), 'Language override');
check(array('code' => 410, 'target' => ''), $service->resolve('/gone', 0, 1), 'Gone');
check(array(), $service->resolve('/disabled', 0, 1), 'Disabled rule');
check(array(), $service->resolve('/foreign', 0, 1), 'Store isolation');
$db->rules[] = rule('/self', '/self/');
$db->rules[] = rule('/cycle-a', '/cycle-b');
$db->rules[] = rule('/cycle-b', '/cycle-a');
$db->rules[] = rule('/chain', '/old');
$db->rules[] = rule('/external', '//evil.test');
$db->rules[] = rule('/shop/old', '/other/new');
$db->rules[] = rule('/shop/valid', '/shop/new');
foreach (array('/self', '/cycle-a', '/cycle-b', '/chain', '/external') as $path) {
	check(array(), $service->resolve($path, 0, 1), 'Rejected rule ' . $path);
}
check(array(), $service->resolve('/shop/old', 0, 1, '/shop/'), 'Target outside store');
check(array('code' => 301, 'target' => '/shop/new'), $service->resolve('/shop/valid', 0, 1, '/shop/'), 'Store subdirectory');
$db->available = false;
check(array(), (new SeoRedirect($db))->resolve('/old', 0, 1), 'Missing schema fallback');
echo "SEO manual redirect regression: OK\n";
