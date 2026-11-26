<?php
define('DB_PREFIX', 'oc_');
require dirname(__DIR__) . '/upload/system/library/novacart/seo_not_found.php';
use Novacart\SeoNotFound;

function check404($expected, $actual, $message) {
	if ($expected !== $actual) {
		throw new RuntimeException($message . ': ' . var_export($actual, true));
	}
}

check404('/ua/missing', SeoNotFound::requestPath('/ua/missing/?utm_source=test&token=secret'), 'Ignore query and trailing slash');
check404('/shop/old', SeoNotFound::requestPath('/shop/old', '/shop/'), 'Store subdirectory');
foreach (array('/index.php?route=product/product', '/image/missing.png', '/a.js', '/.env', '/system/test', '//external.test/x', '/%2f', '/', '/other/old') as $path) {
	check404('', SeoNotFound::requestPath($path, $path === '/other/old' ? '/shop/' : '/'), 'Excluded path ' . $path);
}

class JournalTestDb {
	public $available = true;
	public $writes = array();
	public function countAffected() { return 7; }
	public function escape($value) { return addslashes($value); }
	public function query($sql) {
		if (strpos($sql, 'information_schema.TABLES') !== false) {
			return (object)array('num_rows' => (int)$this->available);
		}
		$this->writes[] = $sql;
		return true;
	}
}

$db = new JournalTestDb();
$service = new SeoNotFound($db);
$service->record('/ua/missing', 2, 3);
$service->record('/ua/missing', 2, 3);
check404(2, count($db->writes), 'Two requests');
check404($db->writes[0], $db->writes[1], 'Same path and scope use identical upsert');
check404(true, strpos($db->writes[0], 'ON DUPLICATE KEY UPDATE hits = hits + 1, date_last = NOW()') !== false, 'Atomic increment');
$db->available = false;
(new SeoNotFound($db))->record('/missing', 0, 1);
check404(2, count($db->writes), 'Missing table no-op');
check404(90, SeoNotFound::retentionDays(null), 'Default retention');
check404(30, SeoNotFound::retentionDays('30'), 'Configured retention');
foreach (array(0, -1, 3651, 'abc', array(), '1.5') as $invalid) {
	check404(90, SeoNotFound::retentionDays($invalid), 'Invalid retention fallback');
}
check404(true, SeoNotFound::loggingEnabled(null), 'Default enabled');
check404(false, SeoNotFound::loggingEnabled('0'), 'Disabled logging');
check404(true, SeoNotFound::loggingEnabled('1'), 'Enabled logging');
$db->available = true;
check404(7, (new SeoNotFound($db))->prune(30), 'Deleted count');
check404(true, strpos(end($db->writes), 'INTERVAL 30 DAY') !== false, 'Configured cleanup period');
$db->available = false;
check404(0, (new SeoNotFound($db))->prune(30), 'Missing journal cleanup no-op');
echo "SEO 404 journal regression: OK\n";
