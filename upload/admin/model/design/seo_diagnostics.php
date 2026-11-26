<?php
class ModelDesignSeoDiagnostics extends Model {
    private $types = array('product'=>'name','category'=>'name','manufacturer'=>'name','information'=>'title','article'=>'name','blog_category'=>'name');
    public function getTypes() { return array_keys($this->types); }
    public function getMetaIssues() { return array('missing_title','missing_description','duplicate_title'); }

    // Scope to active languages, visible entities and the selected store.
    // Duplicate titles are compared within each entity type and language.
    private function metaSource($type, $store, $language) {
        if (!isset($this->types[$type]) || (int)$language < 1) throw new InvalidArgumentException('Invalid metadata scope');
        $id = $type . '_id';
        $name = $type === 'manufacturer' ? 'e.name' : 'd.' . $this->types[$type];
        $sql = " FROM " . DB_PREFIX . $type . " e INNER JOIN " . DB_PREFIX . $type . "_description d ON d." . $id . "=e." . $id . " AND d.language_id=" . (int)$language .
            " WHERE EXISTS (SELECT 1 FROM " . DB_PREFIX . $type . "_to_store s WHERE s." . $id . "=e." . $id . " AND s.store_id=" . (int)$store . ")";
        if ($type !== 'manufacturer') $sql .= " AND e.status=1";
        if ($type === 'product' || $type === 'article') $sql .= " AND e.date_available<=NOW()";
        return array($sql, $id, $name);
    }
    private function metaQuery($type, $store, $language, $issue) {
        list($source,$id,$name) = $this->metaSource($type,$store,$language);
        $fields = "SELECT DISTINCT e." . $id . " AS entity_id, " . $name . " AS name, d.meta_title, d.meta_description";
        if ($issue === 'missing_title') return $fields . $source . " AND TRIM(d.meta_title)=''";
        if ($issue === 'missing_description') return $fields . $source . " AND TRIM(d.meta_description)=''";
        if ($issue !== 'duplicate_title') throw new InvalidArgumentException('Invalid metadata issue');
        return "SELECT m.* FROM (" . $fields . $source . " AND TRIM(d.meta_title)<>'') m INNER JOIN (SELECT TRIM(d.meta_title) AS title" . $source . " AND TRIM(d.meta_title)<>'' GROUP BY TRIM(d.meta_title) HAVING COUNT(DISTINCT e." . $id . ")>1) dup ON TRIM(m.meta_title)=dup.title";
    }
    public function countMeta($type,$store,$language,$issue) {
        return (int)$this->db->query("SELECT COUNT(*) AS total FROM (" . $this->metaQuery($type,$store,$language,$issue) . ") issues")->row['total'];
    }
    public function getMeta($type,$store,$language,$issue,$start,$limit) {
        return $this->db->query($this->metaQuery($type,$store,$language,$issue) . " ORDER BY entity_id LIMIT " . max(0,(int)$start) . "," . max(1,min(100,(int)$limit)))->rows;
    }
    public static function cleanMeta($value, $limit) {
        $value = html_entity_decode((string)$value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = preg_replace('~<(script|style)\b[^>]*>.*?</\1\s*>~is', ' ', $value);
        $value = preg_replace('~<[^>]+>~u', ' ', $value);
        $value = trim(preg_replace('/[\s\p{Z}]+/u', ' ', $value) ?? '');
        return rtrim(mb_substr($value, 0, $limit, 'UTF-8'));
    }

    public function previewMeta($type, $store, $language, $issue, $row) {
        if ($issue === 'duplicate_title') return (string)$row['meta_title'];
        if ($issue === 'missing_title') return self::cleanMeta($row['name'], 255);
        if ($issue !== 'missing_description') return '';
        list($source,$id) = $this->metaSource($type,$store,$language);
        $result = $this->db->query("SELECT d.description" . $source . " AND e." . $id . "=" . (int)$row['entity_id'] . " LIMIT 2");
        if ($result->num_rows !== 1) return '';
        return self::cleanMeta($result->row['description'], 160);
    }

    // Compare the exact previewed value and recheck the selected scope in SQL.
    // Descriptions are shared by stores; the UI explicitly explains this.
    public function applyMeta($type,$store,$language,$issue,$entity,$old,$new) {
        if (!in_array($issue,$this->getMetaIssues(),true) || !is_string($old) || !is_string($new)) return false;
        $field = $issue === 'missing_description' ? 'meta_description' : 'meta_title';
        $new = self::cleanMeta($new,255);
        if ($new === '' || $new === $old || ($issue !== 'duplicate_title' && trim($old) !== '')) return false;
        list($source,$id) = $this->metaSource($type,$store,$language);
        $current = $this->db->query("SELECT d." . $field . " AS current_value" . $source . " AND e." . $id . "=" . (int)$entity . " LIMIT 2");
        if ($current->num_rows !== 1 || $current->row['current_value'] !== $old) return false;
        if ($issue === 'duplicate_title') {
            $duplicate = $this->db->query("SELECT e." . $id . $source . " AND e." . $id . "<>" . (int)$entity . " AND TRIM(d.meta_title)='" . $this->db->escape(trim($old)) . "' LIMIT 1");
            if (!$duplicate->num_rows) return false;
            $conflict = $this->db->query("SELECT e." . $id . $source . " AND e." . $id . "<>" . (int)$entity . " AND TRIM(d.meta_title)='" . $this->db->escape($new) . "' LIMIT 1");
            if ($conflict->num_rows) return false;
        }
        // Metadata belongs to the description, not the store mapping.
        $join = substr($source, strlen(" FROM "));
        $where = strpos($join, " WHERE ");
        $sql = "UPDATE " . substr($join,0,$where) . " SET d." . $field . "='" . $this->db->escape($new) . "'" . substr($join,$where);
        $sql .= " AND e." . $id . "=" . (int)$entity . " AND BINARY d." . $field . "=BINARY '" . $this->db->escape($old) . "'";
        if ($issue !== 'duplicate_title') $sql .= " AND TRIM(d." . $field . ")=''";
        $this->db->query($sql);
        return (int)$this->db->countAffected() === 1;
    }

    public function deleteOrphan($id,$query,$keyword) {
        if (!is_string($query) || !is_string($keyword) || !preg_match('/^(product|category|manufacturer|information|article|blog_category)_id=([0-9]+)$/D',$query,$match)) return false;
        $table=$match[1];
        $this->db->query("DELETE u FROM " . DB_PREFIX . "seo_url u WHERE u.seo_url_id=" . (int)$id .
            " AND BINARY u.`query`=BINARY '" . $this->db->escape($query) . "' AND BINARY u.keyword=BINARY '" . $this->db->escape($keyword) .
            "' AND NOT EXISTS (SELECT 1 FROM " . DB_PREFIX . $table . " e WHERE e." . $table . "_id=" . (int)$match[2] . ")");
        return (int)$this->db->countAffected() === 1;
    }

    public static function redirectPreview(array $rows) {
        $sources=array();
        foreach ($rows as $row) {
            if (!(int)$row['status']) continue;
            $source=\Novacart\SeoRedirect::normalizePath($row['source_path']);
            if ($source !== '') $sources[(int)$row['store_id']][$source][]=$row;
        }
        $out=array();
        foreach (self::redirectIssues($rows) as $row) {
            $target=\Novacart\SeoRedirect::normalizePath($row['target_path']);
            $seen=array(\Novacart\SeoRedirect::normalizePath($row['source_path'])=>true);
            $store=(int)$row['store_id'];
            $final=$row['target_path'];
            while (isset($sources[$store][$target])) {
                if (isset($seen[$target]) || count($seen)>=100 || count($sources[$store][$target])!==1) { $final=''; break; }
                $seen[$target]=true;
                $next=$sources[$store][$target][0];
                if ((int)$next['http_code']!==(int)$row['http_code'] || (int)$next['language_id']!==(int)$row['language_id']) { $final=''; break; }
                $final=$next['target_path'];
                $target=\Novacart\SeoRedirect::normalizePath($final);
                if ($target==='') { $final=''; break; }
            }
            $row['proposal']=$final;
            $out[]=$row;
        }
        return $out;
    }

    private function keywordQuery($issue) {
        $sql = "SELECT seo_url_id, store_id, language_id, `query`, keyword FROM " . DB_PREFIX . "seo_url WHERE ";
        if ($issue === 'empty') return $sql . "TRIM(keyword)='' AND `query`<>'common/home'";
        if ($issue === 'invalid') return $sql . "TRIM(keyword)<>'' AND (keyword<>TRIM(keyword) OR keyword REGEXP '[[:space:][:cntrl:]]' OR LOCATE('/',keyword)>0 OR LOCATE('?',keyword)>0 OR LOCATE('#',keyword)>0 OR LOCATE(CHAR(92),keyword)>0 OR keyword IN ('.','..'))";
        throw new InvalidArgumentException('Invalid keyword issue');
    }
    public function countKeywords($issue) {
        return (int)$this->db->query("SELECT COUNT(*) AS total FROM (" . $this->keywordQuery($issue) . ") issues")->row['total'];
    }
    public function getKeywords($issue,$start,$limit) {
        return $this->db->query($this->keywordQuery($issue) . " ORDER BY seo_url_id LIMIT " . max(0,(int)$start) . "," . max(1,min(100,(int)$limit)))->rows;
    }

    // Match runtime policy: any enabled source in the same store blocks a
    // redirect to that path, even when the language scopes differ.
    public static function redirectIssues(array $rows) {
        $sources = array();
        $issues = array();
        foreach ($rows as $row) {
            if (!(int)$row['status']) continue;
            $source = \Novacart\SeoRedirect::normalizePath($row['source_path']);
            if ($source !== '') $sources[(int)$row['store_id']][$source] = true;
        }
        foreach ($rows as $row) {
            if (!(int)$row['status'] || !in_array((int)$row['http_code'],array(301,302),true)) continue;
            $source = \Novacart\SeoRedirect::normalizePath($row['source_path']);
            $target = \Novacart\SeoRedirect::normalizePath($row['target_path']);
            if ($source === '' || $target === '') continue;
            if ($source === $target || isset($sources[(int)$row['store_id']][$target])) $issues[] = $row;
        }
        return $issues;
    }
    public function getRedirectIssues() {
        require_once DIR_SYSTEM . 'library/novacart/seo_redirect.php';
        $service = new \Novacart\SeoRedirect($this->db);
        if (!$service->isAvailable()) return null;
        return self::redirectPreview($this->db->query("SELECT seo_redirect_id, store_id, language_id, source_path, target_path, http_code, status FROM " . DB_PREFIX . "seo_redirect WHERE status=1 ORDER BY seo_redirect_id")->rows);
    }
}
