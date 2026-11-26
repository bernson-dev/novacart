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

	public function getMissingById($type, $store, $language, $entity_id) {
		list($sql, $name, $id) = $this->buildQuery($type, $store, $language);
		return $this->db->query("SELECT e.`" . $id . "` AS entity_id, " . $name . " AS name, CONCAT('" . $id . "=', e.`" . $id . "`) AS `query`, " . (int)$store . " AS store_id, " . (int)$language . " AS language_id" . $sql . " AND e.`" . $id . "` = " . (int)$entity_id . " LIMIT 1")->row;
	}

	public function suggest($row, $seo, $used) {
		$base = substr($seo->getSeoUrlGeneratorValue($row, html_entity_decode($row['name'], ENT_QUOTES, 'UTF-8')), 0, 240);
		if ($base === '') {
			return '';
		}
		for ($i = 0; $i < 100; $i++) {
			$candidate = $base . ($i ? '-' . ($i + 1) : '');
			if (!isset($used[$candidate]) && !$seo->isReservedLanguagePrefix($candidate, (int)$row['store_id']) && !$seo->getSeoUrlsByKeyword($candidate, (int)$row['store_id'], null)) {
				$used[$candidate] = true;
				return $candidate;
			}
		}
		return '';
	}

	public function saveMissing($type, $store, $language, $entity_id, $keyword, $seo) {
		if (!is_string($keyword) || !preg_match('/^[a-z0-9_-]{1,255}$/D', $keyword)) {
			return false;
		}
		$row = $this->getMissingById($type, $store, $language, $entity_id);
		if (!$row || $seo->isReservedLanguagePrefix($keyword, $store) || $seo->getSeoUrlsByKeyword($keyword, $store, null)) {
			return false;
		}
		$existing = array();
		foreach ($seo->getSeoUrlsByQuery($row['query']) as $item) {
			if ((int)$item['store_id'] === (int)$store && (int)$item['language_id'] === (int)$language) {
				$existing[] = $item;
			}
		}
		// Ambiguous duplicate rows must be resolved in the normal SEO audit.
		if (count($existing) > 1 || ($existing && trim($existing[0]['keyword']) !== '')) {
			return false;
		}
		if ($existing) {
			// Atomic predicate prevents overwriting a keyword entered since preview.
			$this->db->query("UPDATE `" . DB_PREFIX . "seo_url` SET keyword = '" . $this->db->escape($keyword) . "' WHERE seo_url_id = " . (int)$existing[0]['seo_url_id'] . " AND TRIM(keyword) = ''");
			$saved = $this->db->countAffected() === 1;
		} else {
			// One statement rechecks both the query scope and store-wide keyword.
			$this->db->query("INSERT INTO `" . DB_PREFIX . "seo_url` (store_id, language_id, `query`, keyword) SELECT " . (int)$store . ", " . (int)$language . ", '" . $this->db->escape($row['query']) . "', '" . $this->db->escape($keyword) . "' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `" . DB_PREFIX . "seo_url` WHERE store_id = " . (int)$store . " AND (keyword = '" . $this->db->escape($keyword) . "' OR (language_id = " . (int)$language . " AND `query` = '" . $this->db->escape($row['query']) . "')))");
			$saved = $this->db->countAffected() === 1;
		}
		if ($saved) {
			$this->cache->delete('seo_url');
			$this->cache->delete('seopro');
		}
		return $saved;
	}
}
