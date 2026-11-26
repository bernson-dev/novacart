<?php
define('DIR_SYSTEM',dirname(__DIR__).'/upload/system/');
class Controller {
    protected $registry;
    public function __construct($registry){$this->registry=$registry;}
    public function __get($key){return $this->registry[$key];}
}
require dirname(__DIR__).'/upload/catalog/controller/extension/feed/google_sitemap.php';
require dirname(__DIR__).'/upload/catalog/controller/extension/feed/blog_sitemap.php';
class FixtureConfig {
    public function get($key){
        return array('config_store_id'=>0,'seo_noindex_rules'=>array(0=>array('product'=>array(1),'category'=>array(10),'manufacturer'=>array(5),'information'=>array(7),'blog_category'=>array(20),'article'=>array(30))))[$key]??null;
    }
    public function set($key,$value){}
}
class FixtureUrl {
    public function link($route,$params){return 'https://store.test/'.$route.'?'.$params;}
}
class FixtureProducts {
    public function getProducts($data=array()){return array(array('product_id'=>1),array('product_id'=>2));}
}
class FixtureCategories {
    public function getCategories($parent){return $parent===0?array(array('category_id'=>10)):($parent===10?array(array('category_id'=>11)):array());}
}
class FixtureBlogCategories {
    public function getCategoriesByParentId($parent){return $parent===0?array(20):($parent===20?array(21):array());}
}
class FixtureArticles {
    public function getArticles($data){return array(array('article_id'=>30,'date_added'=>'2020-01-01'),array('article_id'=>31,'date_added'=>'2020-01-01'));}
}
class FixtureLanguages {
    public function getLanguages(){return array();}
}
class GoogleFixture extends ControllerExtensionFeedGoogleSitemap {
    public function categories(){return $this->getCategories(0);}
}
class BlogFixture extends ControllerExtensionFeedBlogSitemap {
    public function categories(){return $this->getCategoriesAndArticles(0);}
}
function has($xml,$needle,$expected){if((strpos($xml,$needle)!==false)!==$expected)throw new RuntimeException('Unexpected URL: '.$needle);}
$registry=array('config'=>new FixtureConfig(),'url'=>new FixtureUrl(),'model_catalog_category'=>new FixtureCategories(),'model_catalog_product'=>new FixtureProducts(),'model_blog_category'=>new FixtureBlogCategories(),'model_blog_article'=>new FixtureArticles(),'model_localisation_language'=>new FixtureLanguages());
$xml=(new GoogleFixture($registry))->categories();
has($xml,'category?path=10</loc>',false);
has($xml,'category?path=10_11</loc>',true);
has($xml,'product_id=1</loc>',false);
has($xml,'product_id=2</loc>',true);
$xml=(new BlogFixture($registry))->categories();
has($xml,'category?blog_category_id=20</loc>',false);
has($xml,'category?blog_category_id=20_21</loc>',true);
has($xml,'article_id=30</loc>',false);
has($xml,'article_id=31</loc>',true);
echo "Legacy sitemap noindex regression: OK\n";
