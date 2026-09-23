<?php
class ModelDesignSeoUrl extends Model {
	private $language_scope = array();
	private $language_prefixes = array();
	private $language_prefix_map = array();
	public function addSeoUrl($data) {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "seo_url` SET `store_id` = '" . (int)$data['store_id'] . "', `language_id` = '" . (int)$data['language_id'] . "', `query` = '" . $this->db->escape(html_entity_decode($data['query'], ENT_QUOTES, 'UTF-8')) . "', `keyword` = '" . $this->db->escape($data['keyword']) . "'");
	}

	public function editSeoUrl($seo_url_id, $data) {
		$this->db->query("UPDATE `" . DB_PREFIX . "seo_url` SET `store_id` = '" . (int)$data['store_id'] . "', `language_id` = '" . (int)$data['language_id'] . "', `query` = '" . $this->db->escape(html_entity_decode($data['query'], ENT_QUOTES, 'UTF-8')) . "', `keyword` = '" . $this->db->escape($data['keyword']) . "' WHERE `seo_url_id` = '" . (int)$seo_url_id . "'");
	}

	public function deleteSeoUrl($seo_url_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE `seo_url_id` = '" . (int)$seo_url_id . "'");
	}

	public function getSeoUrl($seo_url_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_url` WHERE `seo_url_id` = '" . (int)$seo_url_id . "'");

		return $query->row;
	}

	public function getSeoUrls($data = array()) {
		// Заменяем подзапросы на JOIN для корректной сортировки
		$sql = "SELECT su.*, s.`name` AS `store`, l.`name` AS `language`
            FROM `" . DB_PREFIX . "seo_url` su
            LEFT JOIN `" . DB_PREFIX . "store` s ON s.`store_id` = su.`store_id`
            LEFT JOIN `" . DB_PREFIX . "language` l ON l.`language_id` = su.`language_id`";

		$implode = array();

		if (!empty($data['filter_query'])) {
			$implode[] = "`query` LIKE '" . $this->db->escape($data['filter_query']) . "'";
		}

		if (!empty($data['filter_keyword'])) {
			$implode[] = "`keyword` LIKE '%" . $this->db->escape($data['filter_keyword']) . "%'";
		}

		if (isset($data['filter_store_id']) && $data['filter_store_id'] !== '') {
			$implode[] = "su.`store_id` = '" . (int)$data['filter_store_id'] . "'";
		}

		if (!empty($data['filter_language_id']) && $data['filter_language_id'] !== '') {
			$implode[] = "su.`language_id` = '" . (int)$data['filter_language_id'] . "'";
		}

		if ($implode) {
			$sql .= " WHERE " . implode(" AND ", $implode);
		}

		// Разрешённые поля для сортировки (включая алиасы)
		$sort_data = array(
		'query',
		'keyword',
		'store_id',
		'language_id',
		'store',
		'language'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY `" . $this->db->escape($data['sort']) . "`";
		} else {
			$sql .= " ORDER BY `query`";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			$data['start'] = max(0, (int)$data['start']);
			$data['limit'] = (int)($data['limit'] < 1 ? 20 : $data['limit']);
			$sql .= " LIMIT " . $data['start'] . "," . $data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getTotalSeoUrls($data = array()) {
		$sql = "SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "seo_url`";

		$implode = array();

		if (!empty($data['filter_query'])) {
			$implode[] = "`query` LIKE '" . $this->db->escape($data['filter_query']) . "'";
		}

		if (!empty($data['filter_keyword'])) {
			$implode[] = "`keyword` LIKE '" . $this->db->escape($data['filter_keyword']) . "'";
		}

		if (!empty($data['filter_store_id']) && $data['filter_store_id'] !== '') {
			$implode[] = "`store_id` = '" . (int)$data['filter_store_id'] . "'";
		}

		if (!empty($data['filter_language_id']) && $data['filter_language_id'] !== '') {
			$implode[] = "`language_id` = '" . (int)$data['filter_language_id'] . "'";
		}

		if ($implode) {
			$sql .= " WHERE " . implode(" AND ", $implode);
		}

		$query = $this->db->query($sql);

		return (int)$query->row['total'];
	}

	public function getSeoUrlsByKeyword($keyword, $store_id = null, $language_id = null) {
		$sql = "SELECT * FROM `" . DB_PREFIX . "seo_url` WHERE `keyword` = '" . $this->db->escape($keyword) . "'";

		if ($store_id !== null) {
			$sql .= " AND `store_id` = '" . (int)$store_id . "'";
		}

		if ($language_id !== null) {
			$sql .= " AND `language_id` = '" . (int)$language_id . "'";
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}

	/**
	 * Language-prefixed SEO URLs make identical keywords safe across languages.
	 * Without the SEO Language module, keep OpenCart's legacy global-per-store
	 * uniqueness rule so disabling the module cannot introduce ambiguous URLs.
	 */
	public function usesLanguageScopedKeywords($store_id) {
		$store_id = (int)$store_id;

		if (array_key_exists($store_id, $this->language_scope)) {
			return $this->language_scope[$store_id];
		}

		$seo_query = $this->db->query("SELECT `value` FROM `" . DB_PREFIX . "setting`
			WHERE `store_id` = '" . $store_id . "'
			AND `code` = 'config'
			AND `key` = 'config_seo_url'
			LIMIT 1");

		if (!$seo_query->num_rows || empty($seo_query->row['value'])) {
			$this->language_scope[$store_id] = false;
			return false;
		}

		$query = $this->db->query("SELECT `value` FROM `" . DB_PREFIX . "setting`
			WHERE `store_id` = '" . $store_id . "'
			AND `code` = 'module_seo_language'
			AND `key` = 'module_seo_language_status'
			LIMIT 1");

		if ($query->num_rows) {
			$this->language_scope[$store_id] = !empty($query->row['value']);
			return $this->language_scope[$store_id];
		}

		// Compatibility with the first test build before one-time migration.
		$query = $this->db->query("SELECT `value` FROM `" . DB_PREFIX . "setting`
			WHERE `store_id` = '" . $store_id . "'
			AND `code` = 'seo_language'
			AND `key` = 'seo_language_status'
			LIMIT 1");

		$this->language_scope[$store_id] = $query->num_rows && !empty($query->row['value']);

		return $this->language_scope[$store_id];
	}

	public function isReservedLanguagePrefix($keyword, $store_id) {
		if (!$this->usesLanguageScopedKeywords($store_id)) {
			return false;
		}

		$keyword = strtolower(trim((string)$keyword, " /\\"));

		if ($keyword === '') {
			return false;
		}

		$prefixes = $this->getReservedLanguagePrefixes((int)$store_id);

		return isset($prefixes[$keyword]);
	}

	public function isOwnLanguagePrefix($keyword, $store_id, $language_id) {
		if (!$this->usesLanguageScopedKeywords($store_id)) {
			return false;
		}

		$keyword = strtolower(trim((string)$keyword, " /\\"));

		if ($keyword === '') {
			return false;
		}

		$query = $this->db->query("SELECT code FROM `" . DB_PREFIX . "language`
			WHERE language_id = '" . (int)$language_id . "'
			LIMIT 1");

		if (!$query->num_rows || empty($query->row['code'])) {
			return false;
		}

		$prefixes = $this->getLanguagePrefixMap((int)$store_id);
		$code = (string)$query->row['code'];

		return isset($prefixes[$code]) && $prefixes[$code] === $keyword;
	}

	private function getReservedLanguagePrefixes($store_id) {
		if (isset($this->language_prefixes[$store_id])) {
			return $this->language_prefixes[$store_id];
		}

		$this->language_prefixes[$store_id] = array();

		foreach ($this->getLanguagePrefixMap((int)$store_id) as $prefix) {
			$this->language_prefixes[$store_id][$prefix] = true;
		}

		return $this->language_prefixes[$store_id];
	}

	private function getLanguagePrefixMap($store_id) {
		if (isset($this->language_prefix_map[$store_id])) {
			return $this->language_prefix_map[$store_id];
		}

		$this->language_prefix_map[$store_id] = array();

		$query = $this->db->query("SELECT `value`, `serialized` FROM `" . DB_PREFIX . "setting`
			WHERE `store_id` = '" . (int)$store_id . "'
			AND `code` = 'module_seo_language'
			AND `key` = 'module_seo_language_prefix'
			LIMIT 1");

		if (!$query->num_rows) {
			$query = $this->db->query("SELECT `value`, `serialized` FROM `" . DB_PREFIX . "setting`
				WHERE `store_id` = '" . (int)$store_id . "'
				AND `code` = 'seo_language'
				AND `key` = 'seo_language_prefix'
				LIMIT 1");
		}

		if (!$query->num_rows) {
			return $this->language_prefix_map[$store_id];
		}

		$value = $query->row['value'];

		if (!empty($query->row['serialized'])) {
			$value = json_decode((string)$value, true);
		}

		if (!is_array($value)) {
			return $this->language_prefix_map[$store_id];
		}

		foreach ($value as $code => $prefix) {
			if (!is_scalar($prefix)) {
				continue;
			}

			$prefix = strtolower(trim((string)$prefix, " /\\"));

			if ($prefix !== '') {
				$this->language_prefix_map[$store_id][(string)$code] = $prefix;
			}
		}

		return $this->language_prefix_map[$store_id];
	}

	public function getSeoUrlsByQuery($query) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_url` WHERE `query` = '" . $this->db->escape($query) . "'");

		return $query->rows;
	}

}
