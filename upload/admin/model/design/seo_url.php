<?php
class ModelDesignSeoUrl extends Model {
	private $language_scope = array();
	private $language_prefixes = array();
	private $language_prefix_map = array();
	private $audit_cache = null;
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

		if (isset($data['filter_language_id']) && $data['filter_language_id'] !== '') {
			$implode[] = "su.`language_id` = '" . (int)$data['filter_language_id'] . "'";
		}

		if (!empty($data['filter_issue'])) {
			$issue_ids = $this->getSeoUrlIssueIds($data['filter_issue']);

			if ($issue_ids) {
				$implode[] = "su.`seo_url_id` IN (" . implode(',', array_map('intval', $issue_ids)) . ")";
			} else {
				$implode[] = "1 = 0";
			}
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
		$sql = "SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "seo_url` su";

		$implode = array();

		if (!empty($data['filter_query'])) {
			$implode[] = "su.`query` LIKE '" . $this->db->escape($data['filter_query']) . "'";
		}

		if (!empty($data['filter_keyword'])) {
			$implode[] = "su.`keyword` LIKE '%" . $this->db->escape($data['filter_keyword']) . "%'";
		}

		if (isset($data['filter_store_id']) && $data['filter_store_id'] !== '') {
			$implode[] = "su.`store_id` = '" . (int)$data['filter_store_id'] . "'";
		}

		if (isset($data['filter_language_id']) && $data['filter_language_id'] !== '') {
			$implode[] = "su.`language_id` = '" . (int)$data['filter_language_id'] . "'";
		}

		if (!empty($data['filter_issue'])) {
			$issue_ids = $this->getSeoUrlIssueIds($data['filter_issue']);

			if ($issue_ids) {
				$implode[] = "su.`seo_url_id` IN (" . implode(',', array_map('intval', $issue_ids)) . ")";
			} else {
				$implode[] = "1 = 0";
			}
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
			WHERE `store_id` IN (0, '" . $store_id . "')
			AND `code` = 'config'
			AND `key` = 'config_seo_url'
			ORDER BY `store_id` DESC
			LIMIT 1");

		if (!$seo_query->num_rows || empty($seo_query->row['value'])) {
			$this->language_scope[$store_id] = false;
			return false;
		}

		$setting = $this->getEffectiveLanguageSetting(
			$store_id,
			array(
				array('config', 'config_seo_language'),
				array('module_seo_language', 'module_seo_language_status'),
				array('seo_language', 'seo_language_status')
			)
		);

		$this->language_scope[$store_id] = $setting !== null && !empty($setting['value']);

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

		$query = $this->db->query("SELECT `code` FROM `" . DB_PREFIX . "language`
			WHERE `language_id` = '" . (int)$language_id . "'
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

		$setting = $this->getEffectiveLanguageSetting(
			(int)$store_id,
			array(
				array('config', 'config_seo_language_prefix'),
				array('module_seo_language', 'module_seo_language_prefix'),
				array('seo_language', 'seo_language_prefix')
			),
			true
		);

		if ($setting === null) {
			return $this->language_prefix_map[$store_id];
		}

		$value = $setting['value'];

		if (!empty($setting['serialized'])) {
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

	/**
	 * Resolve a setting with migration-safe precedence:
	 * local native -> local extension legacy -> local earliest legacy ->
	 * store 0 native -> store 0 extension legacy -> store 0 earliest legacy.
	 */
	private function getEffectiveLanguageSetting($store_id, $candidates, $serialized = false) {
		$store_ids = array((int)$store_id);

		if ((int)$store_id !== 0) {
			$store_ids[] = 0;
		}

		foreach ($store_ids as $candidate_store_id) {
			foreach ($candidates as $candidate) {
				$columns = $serialized ? "`value`, `serialized`" : "`value`";

				$query = $this->db->query("SELECT " . $columns . " FROM `" . DB_PREFIX . "setting`
					WHERE `store_id` = '" . (int)$candidate_store_id . "'
					AND `code` = '" . $this->db->escape($candidate[0]) . "'
					AND `key` = '" . $this->db->escape($candidate[1]) . "'
					LIMIT 1");

				if ($query->num_rows) {
					return $query->row;
				}
			}
		}

		return null;
	}

	public function getSeoUrlAudit() {
		if ($this->audit_cache !== null) {
			return $this->audit_cache;
		}

		$audit = array(
			'issues' => array(),
			'summary' => array(
				'all_rows' => 0,
				'issue_rows' => 0,
				'keyword_groups' => 0,
				'keyword_rows' => 0,
				'query_groups' => 0,
				'query_rows' => 0,
				'prefix_rows' => 0,
				'shared_language_groups' => 0,
				'shared_language_rows' => 0,
				'orphan_rows' => 0
			)
		);

		$query = $this->db->query("SELECT `seo_url_id`, `store_id`, `language_id`, `query`, `keyword`
			FROM `" . DB_PREFIX . "seo_url`
			ORDER BY `seo_url_id` ASC");

		$audit['summary']['all_rows'] = count($query->rows);

		$query_groups = array();
		$keyword_groups = array();

		foreach ($query->rows as $row) {
			$seo_url_id = (int)$row['seo_url_id'];
			$store_id = (int)$row['store_id'];
			$language_id = (int)$row['language_id'];
			$route_query = (string)$row['query'];
			$keyword = trim((string)$row['keyword']);

			$audit['issues'][$seo_url_id] = array(
				'query' => false,
				'keyword' => false,
				'prefix' => false,
				'shared_language' => false,
				'orphan' => false
			);

			if ($route_query !== '') {
				$query_key = $store_id . '|' . $language_id . '|' . $route_query;

				if (!isset($query_groups[$query_key])) {
					$query_groups[$query_key] = array();
				}

				$query_groups[$query_key][] = $seo_url_id;
			}

			if ($keyword !== '') {
				$keyword_key = $store_id . '|';

				if ($this->usesLanguageScopedKeywords($store_id)) {
					$keyword_key .= $language_id . '|';
				}

				$keyword_key .= $keyword;

				if (!isset($keyword_groups[$keyword_key])) {
					$keyword_groups[$keyword_key] = array();
				}

				$keyword_groups[$keyword_key][] = $seo_url_id;

				if (!isset($shared_language_groups[$store_id . '|' . $keyword])) {
					$shared_language_groups[$store_id . '|' . $keyword] = array();
				}

				$shared_language_groups[$store_id . '|' . $keyword][$language_id][] = $seo_url_id;

				if (
					$this->isReservedLanguagePrefix($keyword, $store_id)
					&& !(
						$route_query === 'common/home'
						&& $this->isOwnLanguagePrefix($keyword, $store_id, $language_id)
					)
				) {
					$audit['issues'][$seo_url_id]['prefix'] = true;
					$audit['summary']['prefix_rows']++;
				}
			}

			if (preg_match('/^(product_id|category_id|manufacturer_id|information_id|article_id|blog_category_id)=([0-9]+)$/', $route_query, $match)) {
				$type_map = array(
					'product_id' => 'product',
					'category_id' => 'category',
					'manufacturer_id' => 'manufacturer',
					'information_id' => 'information',
					'article_id' => 'article',
					'blog_category_id' => 'blog_category'
				);

				$type = $type_map[$match[1]];
				$entity_id = (int)$match[2];

				$entity_ids[$type][$entity_id] = $entity_id;

				if (!isset($entity_rows[$type][$entity_id])) {
					$entity_rows[$type][$entity_id] = array();
				}

				$entity_rows[$type][$entity_id][] = $seo_url_id;
			}
		}

		foreach ($query_groups as $ids) {
			if (count($ids) < 2) {
				continue;
			}

			$audit['summary']['query_groups']++;
			$audit['summary']['query_rows'] += count($ids);

			foreach ($ids as $seo_url_id) {
				$audit['issues'][$seo_url_id]['query'] = true;
			}
		}

		foreach ($keyword_groups as $ids) {
			if (count($ids) < 2) {
				continue;
			}

			$audit['summary']['keyword_groups']++;
			$audit['summary']['keyword_rows'] += count($ids);

			foreach ($ids as $seo_url_id) {
				$audit['issues'][$seo_url_id]['keyword'] = true;
			}
		}

		foreach ($shared_language_groups as $languages) {
			if (count($languages) < 2) {
				continue;
			}

			$ids = array();

			foreach ($languages as $language_ids) {
				$ids = array_merge($ids, $language_ids);
			}

			$audit['summary']['shared_language_groups']++;
			$audit['summary']['shared_language_rows'] += count($ids);

			foreach ($ids as $seo_url_id) {
				$audit['issues'][$seo_url_id]['shared_language'] = true;
			}
		}

		$entity_tables = array(
			'product' => 'product',
			'category' => 'category',
			'manufacturer' => 'manufacturer',
			'information' => 'information',
			'article' => 'article',
			'blog_category' => 'blog_category'
		);

		$entity_keys = array(
			'product' => 'product_id',
			'category' => 'category_id',
			'manufacturer' => 'manufacturer_id',
			'information' => 'information_id',
			'article' => 'article_id',
			'blog_category' => 'blog_category_id'
		);

		foreach ($entity_ids as $type => $ids) {
			if (!$ids) {
				continue;
			}

			$key = $entity_keys[$type];
			$exists = array();

			$entity_query = $this->db->query("SELECT `" . $key . "` FROM `" . DB_PREFIX . $entity_tables[$type] . "`
				WHERE `" . $key . "` IN (" . implode(',', array_map('intval', $ids)) . ")");

			foreach ($entity_query->rows as $entity) {
				$exists[(int)$entity[$key]] = true;
			}

			foreach ($entity_rows[$type] as $entity_id => $seo_url_ids) {
				if (isset($exists[(int)$entity_id])) {
					continue;
				}

				foreach ($seo_url_ids as $seo_url_id) {
					$audit['issues'][$seo_url_id]['orphan'] = true;
					$audit['summary']['orphan_rows']++;
				}
			}
		}

		foreach ($audit['issues'] as $seo_url_id => $issues) {
			if ($issues['query'] || $issues['keyword'] || $issues['prefix'] || $issues['orphan']) {
				$audit['summary']['issue_rows']++;
			}
		}

		$this->audit_cache = $audit;

		return $this->audit_cache;
	}

	public function getSeoUrlIssueIds($type = 'all') {
		$audit = $this->getSeoUrlAudit();
		$ids = array();

		foreach ($audit['issues'] as $seo_url_id => $issues) {
			if ($type === 'all') {
				$matched = $issues['query'] || $issues['keyword'] || $issues['prefix'] || $issues['orphan'];
			} else {
				$matched = isset($issues[$type]) && $issues[$type];
			}

			if ($matched) {
				$ids[] = (int)$seo_url_id;
			}
		}

		return $ids;
	}


	public function getSeoUrlsByQuery($query) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_url` WHERE `query` = '" . $this->db->escape($query) . "'");

		return $query->rows;
	}

}
