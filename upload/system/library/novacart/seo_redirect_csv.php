<?php
namespace Novacart;

class SeoRedirectCsv {
    const MAX_BYTES = 2097152;
    const MAX_ROWS = 1000;
    public static function columns() {
        return array('source_path','target_path','store_id','language_id','http_code','status');
    }
    public static function parse($content) {
        if (!is_string($content) || strlen($content)>self::MAX_BYTES || !preg_match('//u',$content) || strpos($content,"\0")!==false) throw new \InvalidArgumentException('error_csv_format');
        if (substr($content,0,3)==="\xEF\xBB\xBF") $content=substr($content,3);
        $stream=fopen('php://temp','w+');
        try {
            fwrite($stream,$content); rewind($stream);
            $header=fgetcsv($stream,0,',','"','');
            if ($header!==self::columns()) {
                rewind($stream);
                $header=fgetcsv($stream,0,';','"','');
                if ($header!==self::columns()) throw new \InvalidArgumentException('error_csv_header');
                $separator=';';
            } else $separator=',';
            $rows=array(); $number=1;
            while (($values=fgetcsv($stream,0,$separator,'"',''))!==false) {
                $number++;
                if ($values===array(null)) continue;
                if (count($rows)>=self::MAX_ROWS) throw new \InvalidArgumentException('error_csv_limit');
                $row=array('number'=>$number,'error'=>'','data'=>array());
                if (count($values)!==6) $row['error']='error_csv_format';
                else {
                    $row['data']=array_combine(self::columns(),array_map('trim',$values));
                    $row['data']['seo_redirect_id']=0;
                    if (!ctype_digit($row['data']['store_id']) || !ctype_digit($row['data']['language_id']) || !in_array($row['data']['http_code'],array('301','302','410'),true) || !in_array($row['data']['status'],array('0','1'),true)) $row['error']='error_csv_format';
                }
                $rows[]=$row;
            }
            if (!$rows) throw new \InvalidArgumentException('error_csv_empty');
            return $rows;
        } finally { fclose($stream); }
    }
    public static function encode(array $rows) {
        $stream=fopen('php://temp','w+');
        try {
            fwrite($stream,"\xEF\xBB\xBF");
            fputcsv($stream,self::columns(),';','"','');
            foreach ($rows as $row) {
                $values=array();
                foreach (self::columns() as $field) {
                    $value=(string)$row[$field];
                    // Refuse spreadsheet-formula cells, rather than modifying a path silently.
                    if (preg_match('/^[\s\x00-\x1f]*[=+\-@]/',$value)) throw new \InvalidArgumentException('error_csv_format');
                    $values[]=$value;
                }
                fputcsv($stream,$values,';','"','');
            }
            rewind($stream);
            return stream_get_contents($stream);
        } finally { fclose($stream); }
    }
}
