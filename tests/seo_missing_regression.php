<?php
define('DB_PREFIX', 'oc_');
class Model {
	protected $db;
	protected $cache;
	protected $log;
	public function __construct($db, $cache, $log) { $this->db = $db; $this->cache = $cache; $this->log = $log; }
}
require dirname(__DIR__) . '/upload/admin/model/design/seo_missing.php';
function same($expected, $actual, $msg) {
	if ($expected !== $actual) { throw new RuntimeException($msg . ': ' . var_export($actual, true)); }
}
class FakeDb {
	public $queries = array();
	public $row = array();
	public $rows = array();
	public function escape($s) { return addslashes($s); }
	public function countAffected() { return 1; }
	public function query($sql) {
		$this->queries[] = $sql;
		return (object)array('row' => $this->row, 'rows' => $this->rows);
	}
}
class FakeCache {
	public $deleted = array();
	public function delete($key) { $this->deleted[] = $key; }
}
class FakeSeo {
	public $existing = array();
	public $taken = array('taken', 'ua');
	public $base = 'taken';
	public function getSeoUrlGeneratorValue($row, $name) { return $this->base; }
	public function isReservedLanguagePrefix($key, $store) { return $key === 'ua'; }
	public function getSeoUrlsByKeyword($key, $store, $lang) { return in_array($key, $this->taken, true) ? array(1) : array(); }
	public function getSeoUrlsByQuery($query) { return $this->existing; }
}
$db = new FakeDb();
$cache = new FakeCache();
$log = new class { public function write($msg) {} };
$model = new ModelDesignSeoMissing($db, $cache, $log);
$seo = new FakeSeo();
$row = array('entity_id'=>5,'name'=>'Name','query'=>'product_id=5','store_id'=>2,'language_id'=>3);
same('taken-3', $model->suggest($row, $seo, array('taken-2'=>true)), 'Suffix avoids existing and batch keywords');
$seo->base = 'ua';
same('ua-2', $model->suggest($row, $seo, array()), 'Reserved prefix');
$seo->base = '';
same('', $model->suggest($row, $seo, array()), 'Empty source');
$db->row = $row;
same(false, $model->saveMissing('product',2,3,5,'taken',$seo), 'Existing keyword rejected');
same(false, $model->saveMissing('product',2,3,5,'bad/path',$seo), 'Invalid keyword rejected');
$seo->existing = array(array('store_id'=>2,'language_id'=>3,'seo_url_id'=>7,'keyword'=>'existing'));
same(false, $model->saveMissing('product',2,3,5,'fresh',$seo), 'Nonempty never overwritten');
$seo->existing[0]['keyword'] = '';
same(true, $model->saveMissing('product',2,3,5,'fresh',$seo), 'Empty row filled');
same(true, strpos(end($db->queries), "TRIM(u.keyword) = ''") !== false, 'Guard empty update');
$seo->existing[] = $seo->existing[0];
same(false, $model->saveMissing('product',2,3,5,'fresh',$seo), 'Ambiguous rows skipped');
$seo->existing = array(array('store_id'=>1,'language_id'=>3,'seo_url_id'=>7,'keyword'=>'existing'));
same(true, $model->saveMissing('product',2,3,5,'fresh',$seo), 'Other store does not block insert');
same(true, strpos(end($db->queries), 'NOT EXISTS') !== false, 'Insert rechecks scope');
$db->row = array();
same(false, $model->saveMissing('product',2,3,5,'fresh',$seo), 'No longer missing or not assigned');
$model->getMissing('product',2,3,0,50,42);
same(true, strpos(end($db->queries), ' > 42 ORDER BY entity_id LIMIT 0,50') !== false, 'Keyset cursor advances');
$db->rows = array($row);
$seo->base = '';
$batch = $model->generateBatch('product',2,3,0,$seo);
same(1,$batch['skipped'],'Unusable names skipped');
same(5,$batch['cursor'],'Skipped rows advance cursor');
same(true,$batch['done'],'Short final batch');
echo "SEO missing keyword regression: OK\n";
