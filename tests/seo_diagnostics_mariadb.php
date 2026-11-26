<?php
error_reporting(E_ALL);
require dirname(__DIR__).'/upload/system/engine/registry.php';
require dirname(__DIR__).'/upload/system/engine/model.php';
require dirname(__DIR__).'/upload/admin/model/design/seo_diagnostics.php';
define('DB_PREFIX','diag_');
function check($expected,$actual,$message) {
    if ($expected !== $actual) throw new RuntimeException($message.': '.var_export($actual,true));
}
class DiagnosticTestDb {
    public $pdo;
    private $affected=0;
    public function escape($value) { return substr($this->pdo->quote($value),1,-1); }
    public function countAffected() { return $this->affected; }
    public function __construct() {
        $this->pdo=new PDO('mysql:host=127.0.0.1;port=3306;dbname=seo_diagnostics;charset=utf8mb4','seo_test','seo_test',array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
    }
    public function query($sql) {
        $statement=$this->pdo->query($sql);
        $this->affected=$statement->rowCount();
        $rows=$statement->columnCount()?$statement->fetchAll(PDO::FETCH_ASSOC):array();
        return (object)array('rows'=>$rows,'row'=>$rows[0]??array(),'num_rows'=>count($rows));
    }
}
$db=new DiagnosticTestDb();
$registry=new Registry();
$registry->set('db',$db);
$model=new ModelDesignSeoDiagnostics($registry);
foreach ($model->getTypes() as $type) {
    $id=$type.'_id';
    $table=DB_PREFIX.$type;
    $name=$type==='information'?'title':'name';
    $db->pdo->exec("CREATE TABLE ".$table." (".$id." INT PRIMARY KEY, name VARCHAR(255), status INT, date_available DATE) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $db->pdo->exec("CREATE TABLE ".$table."_description (".$id." INT, language_id INT, ".$name." VARCHAR(255), meta_title VARCHAR(255), meta_description VARCHAR(255), PRIMARY KEY (".$id.",language_id)) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $db->pdo->exec("CREATE TABLE ".$table."_to_store (".$id." INT, store_id INT, PRIMARY KEY (".$id.",store_id))");
    try {
        $db->pdo->exec("INSERT INTO ".$table." VALUES (1,'One',1,'2000-01-01'),(2,'Two',1,'2000-01-01'),(3,'Three',1,'2000-01-01'),(4,'Other store',1,'2000-01-01'),(5,'Disabled',0,'2000-01-01'),(6,'Future',1,'2999-01-01'),(7,'Other language',1,'2000-01-01')");
        $db->pdo->exec("INSERT INTO ".$table."_to_store VALUES (1,0),(2,0),(3,0),(4,1),(5,0),(6,0),(7,0)");
        $db->pdo->exec("INSERT INTO ".$table."_description VALUES (1,1,'One','  ','Description'),(2,1,'Two','Same',''),(3,1,'Three','same','Description'),(4,1,'Other store','Same',''),(5,1,'Disabled','Disabled',''),(6,1,'Future','Future',''),(7,2,'Other language','Same',''),(2,2,'Two','Unique','Description')");
        check(1,$model->countMeta($type,0,1,'missing_title'),$type.' missing title');
        // Manufacturer has no status/date constraint; other types exclude disabled.
        $missingDescription=$type==='manufacturer'?3:($type==='product'||$type==='article'?1:2);
        check($missingDescription,$model->countMeta($type,0,1,'missing_description'),$type.' missing description');
        check(2,$model->countMeta($type,0,1,'duplicate_title'),$type.' case-insensitive duplicate title');
        check(array(3),array_map('intval',array_column($model->getMeta($type,0,1,'duplicate_title',1,1),'entity_id')),$type.' pagination');
        check(0,$model->countMeta($type,1,1,'duplicate_title'),$type.' store isolation');
        check(0,$model->countMeta($type,0,2,'duplicate_title'),$type.' language isolation');
        check(1,$model->countMeta($type,0,2,'missing_description'),$type.' second language');
        $db->pdo->exec("ALTER TABLE ".$table."_description ADD description TEXT");
        $db->pdo->exec("UPDATE ".$table."_description SET description='&lt;p&gt;Useful text&lt;/p&gt;' WHERE ".$id."=2 AND language_id=1");
        $row=$model->getMeta($type,0,1,'missing_description',0,1)[0];
        check('Useful text',$model->previewMeta($type,0,1,'missing_description',$row),'Description preview');
        check(true,$model->applyMeta($type,0,1,'missing_description',2,'','Useful text'),'Fill description');
        check(false,$model->applyMeta($type,0,1,'missing_description',2,'','Overwrite'),'Existing description protected');
        check(false,$model->applyMeta($type,1,1,'missing_title',1,'  ','Wrong store'),'Wrong store blocked');
        check(true,$model->applyMeta($type,0,1,'missing_title',1,'  ','New title'),'Fill title');
        check(false,$model->applyMeta($type,0,1,'missing_title',1,'  ','Stale'),'Stale value blocked');
        check(false,$model->applyMeta($type,0,1,'duplicate_title',2,'Same','same'),'Conflicting title blocked');
        check(true,$model->applyMeta($type,0,1,'duplicate_title',2,'Same','Unique title'),'Edit duplicate');
        check(0,$model->countMeta($type,0,1,'duplicate_title'),'Duplicate resolved');
    } finally {
        foreach (array($table.'_description',$table.'_to_store',$table) as $fixture) $db->pdo->exec("DROP TABLE ".$fixture);
    }
}
$db->pdo->exec("CREATE TABLE diag_seo_url (seo_url_id INT PRIMARY KEY,store_id INT,language_id INT,`query` VARCHAR(255),keyword VARCHAR(255)) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
try {
    $db->pdo->exec("INSERT INTO diag_seo_url VALUES (1,0,1,'common/home',''),(2,0,1,'product_id=1',''),(3,0,1,'product_id=2','  '),(4,0,1,'product_id=3','good-slug'),(5,0,1,'product_id=4','bad slug'),(6,0,1,'product_id=5','bad/path'),(7,0,1,'product_id=6','товар'),(8,0,1,'product_id=7','..'),(9,0,1,'product_id=8','bad?slug'),(10,0,1,'product_id=9','bad#slug')");
    $db->pdo->exec("CREATE TABLE diag_product (product_id INT PRIMARY KEY)");
    $db->pdo->exec("INSERT INTO diag_product VALUES (1)");
    try {
        check(false,$model->deleteOrphan(2,'product_id=1',''),'Existing entity protected');
        check(false,$model->deleteOrphan(4,'product_id=3','wrong'),'Changed keyword protected');
        check(true,$model->deleteOrphan(4,'product_id=3','good-slug'),'Delete orphan');
    } finally { $db->pdo->exec("DROP TABLE diag_product"); }
    check(2,$model->countKeywords('empty'),'Empty keywords except homepage');
    check(5,$model->countKeywords('invalid'),'Invalid characters, Unicode allowed');
    check(array(5,6,8,9,10),array_map('intval',array_column($model->getKeywords('invalid',0,50),'seo_url_id')),'Invalid keyword IDs');
} finally {
    $db->pdo->exec("DROP TABLE diag_seo_url");
}
foreach (array(array('unknown',0,1,'missing_title'),array('product',0,0,'missing_title'),array('product',0,1,'unknown')) as $args) {
    try { $model->countMeta(...$args); throw new RuntimeException('Invalid scope accepted'); }
    catch (InvalidArgumentException $e) {}
}
echo "SEO diagnostics MariaDB integration: OK\n";
