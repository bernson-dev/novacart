<?php
class ModelDesignSeoMissing extends Model {
	private $definitions = array(
		'product' => 'name',
		'category' => 'name',
		'manufacturer' => 'name',
		'information' => 'title',
		'article' => 'name',
		'blog_category' => 'name'
	);

	public function getTypes() {
		return array_keys($this->definitions);
	}

	private function buildQuery($type, $store, $language) {
		if (!isset($this->definitions[$type]) || (int)$language < 1) {
			throw new InvalidArgumentException('Invalid SEO generator scope');
		}
		$id = $type . '_id';
		$name = $this->definitions[$type];
		$manufacturer = $type === 'manufacturer';
		$name_sql = ($manufacturer ? 'e' : 'd') . '.`' . $name . '`';
		$sql = " FROM `" . DB_PREFIX . $type . "` e INNER JOIN `" . DB_PREFIX . $type . "_to_store` s ON s.`" . $id . "` = e.`" . $id . "` AND s.store_id = " . (int)$store;
		if (!$manufacturer) {
			$sql .= " INNER JOIN `" . DB_PREFIX . $type . "_description` d ON d.`" . $id . "` = e.`" . $id . "` AND d.language_id = " . (int)$language;
		}
		$sql .= " WHERE NOT EXISTS (SELECT 1 FROM `" . DB_PREFIX . "seo_url` u WHERE u.store_id = " . (int)$store . " AND u.language_id = " . (int)$language . " AND u.`query` = CONCAT('" . $id . "=', e.`" . $id . "`) AND TRIM(u.keyword) <> '')";
		return array($sql, $name_sql, $id);
	}

	public function countMissing($type, $store, $language) {
		list($sql, $name, $id) = $this->buildQuery($type, $store, $language);
		return (int)$this->db->query("SELECT COUNT(DISTINCT e.`" . $id . "`) AS total" . $sql)->row['total'];
	}

	public function getMissing($type, $store, $language, $start, $limit) {
		list($sql, $name, $id) = $this->buildQuery($type, $store, $language);
		return $this->db->query("SELECT DISTINCT e.`" . $id . "` AS entity_id, " . $name . " AS name, CONCAT('" . $id . "=', e.`" . $id . "`) AS `query`, " . (int)$store . " AS store_id, " . (int)$language . " AS language_id" . $sql . " ORDER BY entity_id LIMIT " . max(0, (int)$start) . "," . max(1, min(100, (int)$limit)))->rows;
	}
}
