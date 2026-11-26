<?php
require dirname(__DIR__) . '/upload/system/library/novacart/sitemap.php';
use Novacart\Sitemap;
function same($a,$b,$m) { if ($a !== $b) throw new RuntimeException($m . ': ' . var_export($b,true)); }
same(500,Sitemap::pageSize(null),'Default size');
same(1000,Sitemap::pageSize('1000'),'Maximum size');
same(500,Sitemap::pageSize(1001),'Invalid size');
same(0,Sitemap::cacheMinutes('0'),'Disable cache');
same(60,Sitemap::cacheMinutes(null),'Default TTL');
same('a&amp;b',Sitemap::xml('a&amp;b'),'No double encoding');
same('a&amp;b',Sitemap::xml('a&b'),'XML escapes ampersand');
same('&lt;tag&gt;',Sitemap::xml('<tag>'),'XML escapes markup');

define('DB_PREFIX', 'oc_');
$dir = sys_get_temp_dir() . '/novacart-sitemap-test-' . bin2hex(random_bytes(8));
if (!mkdir($dir)) throw new RuntimeException('Cannot create test directory');
define('DIR_CACHE', $dir . '/');
define('DIR_IMAGE', $dir . '/');
register_shutdown_function(function () use ($dir) {
    foreach (glob($dir . '/*') ?: array() as $file) unlink($file);
    rmdir($dir);
});

class SitemapTestConfig {
    public $values = array(
        'config_store_id'=>0, 'config_url'=>'https://store.test/', 'config_ssl'=>'https://store.test/',
        'config_language'=>'ru-ru', 'config_language_id'=>2,
        'feed_google_sitemap_limit'=>2, 'feed_google_sitemap_cache'=>0,
        'config_seo_url'=>1, 'config_seo_pro'=>1
    );
    public function get($key) { return $this->values[$key] ?? null; }
    public function set($key,$value) { $this->values[$key]=$value; }
}
class SitemapTestDb {
    public $queries = array();
    public $products = array(
        array('product_id'=>1,'status'=>1,'store'=>0,'date_available'=>'2000-01-01','date_modified'=>'2020-01-01','image'=>'test image.jpg'),
        array('product_id'=>2,'status'=>1,'store'=>0,'date_available'=>'2000-01-01'),
        array('product_id'=>3,'status'=>1,'store'=>0,'date_available'=>'2000-01-01'),
        array('product_id'=>4,'status'=>0,'store'=>0,'date_available'=>'2000-01-01'),
        array('product_id'=>5,'status'=>1,'store'=>1,'date_available'=>'2000-01-01'),
        array('product_id'=>6,'status'=>1,'store'=>0,'date_available'=>'2999-01-01')
    );
    public function query($sql) {
        $this->queries[]=$sql;
        $rows=array();
        if (strpos($sql,'FROM oc_language') !== false) {
            $rows=array(array('language_id'=>1,'code'=>'ru-ru'),array('language_id'=>2,'code'=>'uk-ua'));
        } elseif (strpos($sql,'FROM oc_product e') !== false) {
            // Require actual store/status/date predicates, then emulate the result.
            if (!preg_match('/s\.store_id = (\d+)/',$sql,$m) || strpos($sql,'e.status = 1')===false || strpos($sql,'e.date_available <= NOW()')===false) throw new RuntimeException('Missing SQL scope');
            $store=(int)$m[1];
            $rows=array_values(array_filter($this->products,function($p) use ($store) {
                return $p['store']===$store && $p['status']===1 && $p['date_available'] <= date('Y-m-d');
            }));
            if (strpos($sql,'COUNT(*)')!==false) $rows=array(array('total'=>count($rows)));
            elseif (preg_match('/LIMIT (\d+),(\d+)$/',$sql,$m)) $rows=array_slice($rows,(int)$m[1],(int)$m[2]);
            else throw new RuntimeException('Missing SQL pagination');
        } elseif (strpos($sql,'COUNT(*)')!==false) {
            $rows=array(array('total'=>0));
        }
        return (object)array('rows'=>$rows,'row'=>$rows[0]??array(),'num_rows'=>count($rows));
    }
}
class SitemapTestUrl {
    private $config;
    public function __construct($config) { $this->config=$config; }
    public function link($route,$args='',$ssl=false) {
        parse_str($args,$params);
        return 'https://store.test/' . ($this->config->get('config_language_id')===2?'ua/':'') . ($route==='common/home'?'':'product-' . $params['product_id']);
    }
}
// Stub only the existing language service; XML generation itself is real.
class SeoLanguage {
    public $enabled=true;
    public function isEnabled() { return $this->enabled; }
    public function isRouteAvailableForLanguage($route,$params,$id) { return true; }
    public function getAvailableAlternateLinks($route,$params,$ssl) {
        $path=$route==='common/home'?'':'product-' . $params['product_id'];
        return array('ru'=>'https://store.test/'.$path,'uk'=>'https://store.test/ua/'.$path,'x-default'=>'https://store.test/'.$path);
    }
}
class SitemapTestRegistry {
    public $values=array();
    public function get($key) { return $this->values[$key]??null; }
}
function document($xml) {
    $doc=new DOMDocument();
    same(true,$doc->loadXML($xml, LIBXML_NONET),'Valid XML');
    return $doc;
}
function locs($doc) {
    $xp=new DOMXPath($doc);
    $xp->registerNamespace('s','http://www.sitemaps.org/schemas/sitemap/0.9');
    $out=array();
    foreach($xp->query('//s:loc') as $node) $out[]=$node->textContent;
    return $out;
}
$config=new SitemapTestConfig();
$db=new SitemapTestDb();
$seo=new SeoLanguage();
$registry=new SitemapTestRegistry();
$registry->values=array('config'=>$config,'db'=>$db,'seo_language'=>$seo,'url'=>new SitemapTestUrl($config),'request'=>(object)array('server'=>array('HTTPS'=>'on')));
file_put_contents(DIR_IMAGE.'test image.jpg','fixture');
$service=new Sitemap($registry);
$index=document($service->serve(array()));
same('sitemapindex',$index->documentElement->localName,'Index root');
$children=locs($index);
same(3,count($children),'Home and two product parts');
same(true,strpos($children[2],'sitemap_part=2')!==false,'Second part in index');
$first=document($service->serve(array('sitemap_type'=>'products','sitemap_part'=>1)));
$second=document($service->serve(array('sitemap_type'=>'products','sitemap_part'=>2)));
same('urlset',$first->documentElement->localName,'Child root');
$all=array_merge(locs($first),locs($second));
same(array('https://store.test/product-1','https://store.test/ua/product-1','https://store.test/product-2','https://store.test/ua/product-2','https://store.test/product-3','https://store.test/ua/product-3'),$all,'Store/status/date and partition coverage');
same($all,array_values(array_unique($all)),'No duplicate locations');
same(12,$first->getElementsByTagNameNS('http://www.w3.org/1999/xhtml','link')->length,'Reciprocal alternate links plus x-default');
same(2,$first->getElementsByTagNameNS('http://www.google.com/schemas/sitemap-image/1.1','loc')->length,'Original image per translation');
same('https://store.test/image/test%20image.jpg',$first->getElementsByTagNameNS('http://www.google.com/schemas/sitemap-image/1.1','loc')->item(0)->textContent,'Image URL escaped');
same(null,$service->serve(array('sitemap_type'=>'products','sitemap_part'=>3)),'Out of range');
same(null,$service->serve(array('sitemap_type'=>'../products','sitemap_part'=>1)),'Invalid type');
same(null,$service->serve(array('sitemap_type'=>'home','sitemap_part'=>2)),'Invalid home part');
same(null,$service->serve(array('sitemap_type'=>'products','sitemap_part'=>array(1))),'Invalid part array');
same(0,count(glob(DIR_CACHE.'novacart-sitemap-*.xml')),'Cache disabled');
$seo->enabled=false;
$service=new Sitemap($registry);
same(array('https://store.test/product-1','https://store.test/product-2'),locs(document($service->serve(array('sitemap_type'=>'products','sitemap_part'=>1)))),'Default language only');
same(2,$config->get('config_language_id'),'Active language restored');
$config->set('config_store_id',1);
$service=new Sitemap($registry);
same(array('https://store.test/product-5'),locs(document($service->serve(array('sitemap_type'=>'products','sitemap_part'=>1)))),'Other store isolated');
$config->set('config_store_id',0);
$config->set('feed_google_sitemap_cache',60);
$service=new Sitemap($registry);
$xml=$service->serve(array());
$queries=count($db->queries);
same($xml,$service->serve(array()),'Cache hit');
same($queries,count($db->queries),'Cache hit avoids database generation');
same(1,count(glob(DIR_CACHE.'novacart-sitemap-*.xml')),'Cache file exists');
file_put_contents(DIR_CACHE.'novacart-sitemap-expired-'.(time()-60).'.xml','expired');
file_put_contents(DIR_CACHE.'unrelated.xml','untouched');
same(1,Sitemap::clearCache(true),'Expired cache removed');
same(1,count(glob(DIR_CACHE.'novacart-sitemap-*.xml')),'Fresh cache retained');
same(1,Sitemap::clearCache(),'Manual cache clear');
same(true,is_file(DIR_CACHE.'unrelated.xml'),'Other cache untouched');
echo "Sitemap PRO regression: OK\n";

