<?php
require dirname(__DIR__).'/upload/system/library/novacart/seo_noindex.php';
use Novacart\SeoNoindex;
function check($a,$b){if($a!==$b)throw new RuntimeException(var_export($b,true));}
$rules=SeoNoindex::parse("product=12\r\ncategory=3\nproduct=12");
check(array('category'=>array(3),'product'=>array(12)),$rules);
$config=new class($rules) {
    private $rules;
    public function __construct($rules){$this->rules=$rules;}
    public function get($key){return array(0=>$this->rules);}
};
check(true,SeoNoindex::matches($config,0,'product/product',array('product_id'=>12,'path'=>'3')));
check(false,SeoNoindex::matches($config,0,'product/product',array('product_id'=>13,'path'=>'3')));
check(true,SeoNoindex::matches($config,0,'product/category',array('path'=>'1_3')));
check(false,SeoNoindex::matches($config,0,'product/category',array('path'=>'3_4')));
check(false,SeoNoindex::matches($config,1,'product/product',array('product_id'=>12)));
check(false,SeoNoindex::matches($config,0,'common/home',array('product_id'=>12)));
check('noindex, follow',SeoNoindex::robots('index, follow'));
check('noindex, nofollow',SeoNoindex::robots('nofollow'));
check('none',SeoNoindex::robots('none'));
foreach(array('product=0','unknown=1','product=1 OR 1=1','product=2147483648') as $bad){
    try{SeoNoindex::parse($bad);throw new RuntimeException('Accepted invalid rule');}catch(InvalidArgumentException $e){}
}
echo "SEO noindex regression: OK\n";
