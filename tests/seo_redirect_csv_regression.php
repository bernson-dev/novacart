<?php
require dirname(__DIR__).'/upload/system/library/novacart/seo_redirect_csv.php';
use Novacart\SeoRedirectCsv;
function check($a,$b) { if ($a!==$b) throw new RuntimeException(var_export($b,true)); }
$row=array('source_path'=>'/старый','target_path'=>'/новый','store_id'=>'0','language_id'=>'2','http_code'=>'301','status'=>'1');
$csv=SeoRedirectCsv::encode(array($row));
$parsed=SeoRedirectCsv::parse($csv);
check('', $parsed[0]['error']);
check($row+array('seo_redirect_id'=>0),$parsed[0]['data']);
$header=implode(',',SeoRedirectCsv::columns());
check('error_csv_format',SeoRedirectCsv::parse($header."\n/a,/b,0,0,999,1")[0]['error']);
check('error_csv_format',SeoRedirectCsv::parse($header."\n/a,/b,0")[0]['error']);
foreach (array('wrong'=>"error_csv_header",$header=>"error_csv_empty",$header."\n".str_repeat("/a,/b,0,0,301,1\n",1001)=>"error_csv_limit") as $input=>$error) {
    try { SeoRedirectCsv::parse($input); throw new RuntimeException('Expected error'); } catch (InvalidArgumentException $e) { check($error,$e->getMessage()); }
}
echo "SEO redirect CSV regression: OK\n";
