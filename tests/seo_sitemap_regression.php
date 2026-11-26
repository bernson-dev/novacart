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
echo "Sitemap PRO regression: OK\n";
