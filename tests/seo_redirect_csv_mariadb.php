<?php
define('DIR_SYSTEM',dirname(__DIR__).'/upload/system/');
define('DB_PREFIX','csvtest_');
require DIR_SYSTEM.'engine/registry.php';
require DIR_SYSTEM.'engine/model.php';
require DIR_SYSTEM.'library/novacart/seo_redirect_csv.php';
require dirname(__DIR__).'/upload/admin/model/design/seo_redirect.php';
use Novacart\SeoRedirectCsv;
function check($a,$b,$m){if($a!==$b)throw new RuntimeException($m.': '.var_export($b,true));}
class CsvTestDb {
    public $pdo;
    public function __construct(){ $this->pdo=new PDO('mysql:host=127.0.0.1;port=3306;dbname=seo_diagnostics;charset=utf8mb4','seo_test','seo_test',array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION)); }
    public function escape($v){return substr($this->pdo->quote($v),1,-1);}
    public function query($sql){$s=$this->pdo->query($sql);$rows=$s->columnCount()?$s->fetchAll(PDO::FETCH_ASSOC):array();return (object)array('rows'=>$rows,'row'=>$rows[0]??array(),'num_rows'=>count($rows));}
}
$db=new CsvTestDb();
$registry=new Registry();$registry->set('db',$db);
$model=new ModelDesignSeoRedirect($registry);
$schema=file_get_contents(dirname(__DIR__).'/docs/migrations/seo-redirect.sql');
$db->pdo->exec(str_replace('oc_seo_redirect','csvtest_seo_redirect',$schema));
$db->pdo->exec("CREATE TABLE csvtest_seo_url (seo_url_id INT PRIMARY KEY,store_id INT,keyword VARCHAR(255))");
$db->pdo->exec("CREATE TABLE csvtest_store (store_id INT PRIMARY KEY,name VARCHAR(255))");
$db->pdo->exec("CREATE TABLE csvtest_language (language_id INT PRIMARY KEY,name VARCHAR(255))");
try {
    $db->pdo->exec("INSERT INTO csvtest_seo_url VALUES (1,0,'active')");
    $validate=function($row,$preview) use ($model) {
        if (!in_array((int)$row['store_id'],array(0,1),true) || !in_array((int)$row['language_id'],array(0,2),true)) return 'error_redirect_scope';
        return $model->saveRule($row,'/',array(),array(array('code'=>'uk-ua')),$preview);
    };
    $fail=function($e){throw $e;};
    $content=implode(';',SeoRedirectCsv::columns())."\n".
        "/old;/new/;0;0;301;1\n".
        "/old/;/other;0;0;302;1\n".
        "/new;/end;0;0;301;1\n".
        "/active;/end;0;0;301;1\n".
        "/gone;;0;0;410;1\n".
        "/other;https://external.test/;0;0;301;1\n".
        "/old;/different;1;0;302;1\n".
        "/bad;/end;99;0;301;1\n";
    $rows=SeoRedirectCsv::preview(SeoRedirectCsv::parse($content),$validate);
    check(array('','error_redirect_duplicate','error_redirect_chain','error_redirect_conflict','','error_redirect_path','','error_redirect_scope'),array_column($rows,'error'),'Preview validation');
    check(0,$model->getTotalRules(''),'Preview is read-only');
    $result=SeoRedirectCsv::apply($rows,$validate,$fail);
    check(array('created'=>3,'skipped'=>5,'errors'=>0),$result['report'],'Import agrees with preview');
    check(3,$model->getTotalRules(''),'Three rules inserted');
    $round=SeoRedirectCsv::parse(SeoRedirectCsv::encode($model->getRules('',0,100)));
    check(3,count($round),'Export roundtrip');
    $again=SeoRedirectCsv::apply(SeoRedirectCsv::preview(SeoRedirectCsv::parse($content),$validate),$validate,$fail);
    check(0,$again['report']['created'],'Repeat import does not overwrite');
    check(3,$model->getTotalRules(''),'Repeat import leaves row count unchanged');
    $old=$model->getRules('',0,100);
    $matched=array_values(array_filter($old,function($row){return (int)$row['store_id']===0 && $row['source_path']==='/old';}));
    check('/new/',$matched[0]['target_path'],'Existing target preserved');
    $stale=SeoRedirectCsv::preview(SeoRedirectCsv::parse(implode(';',SeoRedirectCsv::columns())."\n/race;/one;0;0;301;1"),$validate);
    check('',$validate($stale[0]['data'],false),'Concurrent insert simulation');
    check(1,SeoRedirectCsv::apply($stale,$validate,$fail)['report']['skipped'],'Stale preview revalidated');
    // More than the import limit, across three export batches.
    $values=array();
    for ($i=0;$i<1001;$i++) {
        $path='/bulk-'.$i;
        $values[]="(0,0,'".$path."','".hash('sha256',$path)."','',410,1,NOW())";
    }
    $db->pdo->exec("INSERT INTO csvtest_seo_redirect (store_id,language_id,source_path,source_hash,target_path,http_code,status,date_modified) VALUES ".implode(',',$values));
    $export=iterator_to_array($model->exportRules(),false);
    check(1005,count($export),'Export above import limit');
    check(1005,count(array_unique(array_column($export,'seo_redirect_id'))),'Export no duplicates');
    $csv=SeoRedirectCsv::encode($model->exportRules());
    check(1006,substr_count($csv,"\n"),'All rows written to CSV');
} finally {
    foreach(array('seo_redirect','seo_url','store','language') as $table)$db->pdo->exec('DROP TABLE csvtest_'.$table);
}
echo "SEO redirect CSV MariaDB integration: OK\n";
