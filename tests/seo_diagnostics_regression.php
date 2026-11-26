<?php
require dirname(__DIR__).'/upload/system/engine/model.php';
require dirname(__DIR__).'/upload/system/library/novacart/seo_redirect.php';
require dirname(__DIR__).'/upload/admin/model/design/seo_diagnostics.php';
function expect($a,$b,$m) { if ($a!==$b) throw new RuntimeException($m); }
function rule($id,$store,$source,$target,$status=1,$code=301) { return array('seo_redirect_id'=>$id,'store_id'=>$store,'source_path'=>$source,'target_path'=>$target,'status'=>$status,'http_code'=>$code); }
$rows=array(rule(1,0,'/a','/b/'),rule(2,0,'/b','/c'),rule(3,0,'/self','/self/'),rule(4,1,'/other','/b'),rule(5,0,'/off','/a',0),rule(6,0,'/gone','',1,410),rule(7,0,'/to-gone','/gone'),rule(8,0,'/to-off','/off'));
expect(array(1,3,7),array_column(ModelDesignSeoDiagnostics::redirectIssues($rows),'seo_redirect_id'),'Redirect chains, loops, disabled rules and store scope');
expect('Hello world',ModelDesignSeoDiagnostics::cleanMeta('&lt;p&gt;Hello&lt;/p&gt; <script>bad()</script> world',255),'HTML cleaning');
expect('абв',ModelDesignSeoDiagnostics::cleanMeta('абвг',3),'Unicode truncation');
foreach ($rows as &$row) $row['language_id']=0;
unset($row);
$preview=ModelDesignSeoDiagnostics::redirectPreview($rows);
expect(array('/c','',''),array_column($preview,'proposal'),'Safe chain preview, loop and 410 require manual review');
echo "SEO diagnostics regression: OK\n";
