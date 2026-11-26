<?php
namespace Novacart;

/** Store-specific entity exclusions; all languages of an entity share the rule. */
class SeoNoindex {
    public static function routes() {
        return array(
            'product/product'=>array('product','product_id'),
            'product/category'=>array('category','path'),
            'product/manufacturer/info'=>array('manufacturer','manufacturer_id'),
            'information/information'=>array('information','information_id'),
            'blog/article'=>array('article','article_id'),
            'blog/category'=>array('blog_category','blog_category_id')
        );
    }
    public static function parse($text) {
        if (!is_string($text) || strlen($text)>60000) throw new \InvalidArgumentException('error_noindex_format');
        $map=array(); $count=0;
        $types=array_column(self::routes(),0);
        foreach (preg_split('/\r\n|\r|\n/',$text) as $line) {
            $line=trim($line);
            if ($line==='') continue;
            if (!preg_match('/^([a-z_]+)=([1-9][0-9]{0,9})$/D',$line,$m) || !in_array($m[1],$types,true) || (float)$m[2]>2147483647) throw new \InvalidArgumentException('error_noindex_format');
            if (!isset($map[$m[1]][(int)$m[2]])) $count++;
            $map[$m[1]][(int)$m[2]]=true;
            if ($count>2000) throw new \InvalidArgumentException('error_noindex_limit');
        }
        ksort($map);
        foreach ($map as &$ids) { $ids=array_keys($ids); sort($ids,SORT_NUMERIC); }
        unset($ids);
        return $map;
    }
    public static function ids($config,$store,$type) {
        $all=$config->get('seo_noindex_rules');
        $ids=$all[(int)$store][$type] ?? array();
        return is_array($ids)?array_values(array_unique(array_filter(array_map('intval',array_filter($ids,'is_scalar')),function($id){return $id>0;}))):array();
    }
    public static function matches($config,$store,$route,$params) {
        $routes=self::routes();
        if (!is_string($route) || !isset($routes[$route])) return false;
        list($type,$param)=$routes[$route];
        $value=$params[$param] ?? '';
        if (!is_scalar($value)) return false;
        $value=(string)$value;
        if ($type==='category' || $type==='blog_category') {
            if (!preg_match('/^[0-9]+(?:_[0-9]+)*$/D',$value)) return false;
            $parts=explode('_',$value); $value=end($parts);
        }
        return ctype_digit($value) && in_array((int)$value,self::ids($config,$store,$type),true);
    }
    public static function robots($existing) {
        $parts=preg_split('/\s*,\s*/',strtolower(trim((string)$existing)),-1,PREG_SPLIT_NO_EMPTY);
        $parts=array_values(array_diff($parts,array('index','all','noindex')));
        if (in_array('none',$parts,true)) return implode(', ',$parts);
        array_unshift($parts,'noindex');
        if (!in_array('nofollow',$parts,true) && !in_array('follow',$parts,true)) $parts[]='follow';
        return implode(', ',array_unique($parts));
    }
}
