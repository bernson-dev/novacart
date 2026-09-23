<?php
class ModelDesignSeoUrl extends Model {
	private $language_scope = array();
	private $language_prefixes = array();
	private $language_prefix_map = array();
	private $audit_cache = null;
	public function addSeoUrl($data) {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "seo_url` SET `store_id` = '" . (int)$data['store_id'] . "', `language_id` = '" . (int)$data['language_id'] . "', `query` = '" . $this->db->escape(html_entity_decode($data['query'], ENT_QUOTES, 'UTF-8')) . "', `keyword` = '" . $this->db->escape($data['keyword']) . "'");
		$this->clearSeoCache();
	}

	public function editSeoUrl($seo_url_id, $data) {
		$this->db->query("UPDATE `" . DB_PREFIX . "seo_url` SET `store_id` = '" . (int)$data['store_id'] . "', `language_id` = '" . (int)$data['language_id'] . "', `query` = '" . $this->db->escape(html_entity_decode($data['query'], ENT_QUOTES, 'UTF-8')) . "', `keyword` = '" . $this->db->escape($data['keyword']) . "' WHERE `seo_url_id` = '" . (int)$seo_url_id . "'");
		$this->clearSeoCache();
	}

	public function editSeoUrlKeyword($seo_url_id, $keyword) {
		$this->db->query("UPDATE `" . DB_PREFIX . "seo_url` SET `keyword` = '" . $this->db->escape((string)$keyword) . "' WHERE `seo_url_id` = '" . (int)$seo_url_id . "'");
		$this->clearSeoCache();
	}

	public function deleteSeoUrl($seo_url_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE `seo_url_id` = '" . (int)$seo_url_id . "'");
		$this->clearSeoCache();
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
			$implode[] = "su.`query` LIKE '%" . $this->db->escape($data['filter_query']) . "%'";
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

		if (!empty($data['filter_search'])) {
			$implode[] = $this->buildSeoUrlSearchCondition($data['filter_search']);
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

		if (!empty($data['filter_issue']) && in_array($data['filter_issue'], array('keyword', 'shared_language', 'prefix'))) {
			$sql .= " ORDER BY su.`store_id`, su.`keyword`, su.`language_id`, su.`seo_url_id`";
		} elseif (!empty($data['filter_issue']) && $data['filter_issue'] === 'query') {
			$sql .= " ORDER BY su.`store_id`, su.`language_id`, su.`query`, su.`seo_url_id`";
		} elseif (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
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
			$implode[] = "su.`query` LIKE '%" . $this->db->escape($data['filter_query']) . "%'";
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

		if (!empty($data['filter_search'])) {
			$implode[] = $this->buildSeoUrlSearchCondition($data['filter_search']);
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

	public function normalizeLanguageScopedSeoUrls($seo_urls) {
		if (!is_array($seo_urls)) {
			return array();
		}

		$language_query = $this->db->query("SELECT `language_id` FROM `" . DB_PREFIX . "language`
			WHERE `status` = '1'
			ORDER BY `sort_order`, `name`");

		$active_language_ids = array();

		foreach ($language_query->rows as $language) {
			$active_language_ids[] = (int)$language['language_id'];
		}

		foreach ($seo_urls as $store_id => $language_values) {
			if (!is_array($language_values) || !$this->usesLanguageScopedKeywords((int)$store_id)) {
				continue;
			}

			$default_language_id = $this->getStoreDefaultLanguageId((int)$store_id);
			$keyword = '';

			if (
				$default_language_id > 0
				&& isset($language_values[$default_language_id])
			) {
				$keyword = trim((string)$language_values[$default_language_id]);
			}

			/*
			 * Upgrade-safe fallback: old installations may have a blank default
			 * language keyword but a populated secondary-language keyword.
			 * Preserve an existing slug instead of deleting all localized rows.
			 */
			if ($keyword === '') {
				foreach ($language_values as $value) {
					$value = trim((string)$value);

					if ($value !== '') {
						$keyword = $value;
						break;
					}
				}
			}

			foreach ($active_language_ids as $language_id) {
				$seo_urls[$store_id][$language_id] = $keyword;
			}
		}

		return $seo_urls;
	}

	public function getStoreDefaultLanguageId($store_id) {
		$setting = $this->getEffectiveLanguageSetting(
			(int)$store_id,
			array(
				array('config', 'config_language')
			)
		);

		if ($setting === null || empty($setting['value'])) {
			return 0;
		}

		$query = $this->db->query("SELECT `language_id` FROM `" . DB_PREFIX . "language`
			WHERE `code` = '" . $this->db->escape((string)$setting['value']) . "'
			LIMIT 1");

		return $query->num_rows ? (int)$query->row['language_id'] : 0;
	}


	public function getSeoUrlSources($rows) {
		$definitions = array(
			'product_id' => array(
				'table' => 'product_description',
				'id' => 'product_id',
				'name' => 'name',
				'language' => true,
				'route' => 'catalog/product/edit',
				'parameter' => 'product_id'
			),
			'category_id' => array(
				'table' => 'category_description',
				'id' => 'category_id',
				'name' => 'name',
				'language' => true,
				'route' => 'catalog/category/edit',
				'parameter' => 'category_id'
			),
			'manufacturer_id' => array(
				'table' => 'manufacturer',
				'id' => 'manufacturer_id',
				'name' => 'name',
				'language' => false,
				'route' => 'catalog/manufacturer/edit',
				'parameter' => 'manufacturer_id'
			),
			'information_id' => array(
				'table' => 'information_description',
				'id' => 'information_id',
				'name' => 'title',
				'language' => true,
				'route' => 'catalog/information/edit',
				'parameter' => 'information_id'
			),
			'article_id' => array(
				'table' => 'article_description',
				'id' => 'article_id',
				'name' => 'name',
				'language' => true,
				'route' => 'blog/article/edit',
				'parameter' => 'article_id'
			),
			'blog_category_id' => array(
				'table' => 'blog_category_description',
				'id' => 'blog_category_id',
				'name' => 'name',
				'language' => true,
				'route' => 'blog/category/edit',
				'parameter' => 'blog_category_id'
			)
		);

		$groups = array();
		$parsed = array();

		foreach ($rows as $row) {
			if (!preg_match('/^([a-z_]+)=([0-9]+)$/', (string)$row['query'], $match)) {
				continue;
			}

			$type = $match[1];

			if (!isset($definitions[$type])) {
				continue;
			}

			$id = (int)$match[2];
			$language_id = (int)$row['language_id'];
			$parsed[(int)$row['seo_url_id']] = array(
				'type' => $type,
				'id' => $id,
				'language_id' => $language_id
			);

			if (!isset($groups[$type])) {
				$groups[$type] = array(
					'ids' => array(),
					'languages' => array()
				);
			}

			$groups[$type]['ids'][$id] = $id;

			if ($definitions[$type]['language']) {
				$groups[$type]['languages'][$language_id] = $language_id;
			}
		}

		$names = array();

		foreach ($groups as $type => $group) {
			$definition = $definitions[$type];

			if (!$group['ids']) {
				continue;
			}

			$sql = "SELECT `" . $definition['id'] . "`, `" . $definition['name'] . "`";

			if ($definition['language']) {
				$sql .= ", `language_id`";
			}

			$sql .= " FROM `" . DB_PREFIX . $definition['table'] . "`
				WHERE `" . $definition['id'] . "` IN (" . implode(',', array_map('intval', $group['ids'])) . ")";

			if ($definition['language'] && $group['languages']) {
				$sql .= " AND `language_id` IN (" . implode(',', array_map('intval', $group['languages'])) . ")";
			}

			$query = $this->db->query($sql);

			foreach ($query->rows as $item) {
				$key = $type . '|' . (int)$item[$definition['id']] . '|';

				if ($definition['language']) {
					$key .= (int)$item['language_id'];
				} else {
					$key .= '0';
				}

				$names[$key] = (string)$item[$definition['name']];
			}
		}

		$result = array();

		foreach ($parsed as $seo_url_id => $source) {
			$definition = $definitions[$source['type']];
			$key = $source['type'] . '|' . $source['id'] . '|'
				. ($definition['language'] ? $source['language_id'] : 0);

			$result[$seo_url_id] = array(
				'name' => isset($names[$key]) ? $names[$key] : '',
				'route' => $definition['route'],
				'parameter' => $definition['parameter'],
				'id' => $source['id']
			);
		}

		return $result;
	}

	private function buildSeoUrlSearchCondition($value) {
		$value = $this->db->escape(trim((string)$value));
		$like = "'%" . $value . "%'";

		return "("
			. "su.`query` LIKE " . $like
			. " OR su.`keyword` LIKE " . $like
			. " OR EXISTS (SELECT 1 FROM `" . DB_PREFIX . "product_description` pd"
				. " WHERE su.`query` = CONCAT('product_id=', pd.`product_id`)"
				. " AND pd.`name` LIKE " . $like . ")"
			. " OR EXISTS (SELECT 1 FROM `" . DB_PREFIX . "category_description` cd"
				. " WHERE su.`query` = CONCAT('category_id=', cd.`category_id`)"
				. " AND cd.`name` LIKE " . $like . ")"
			. " OR EXISTS (SELECT 1 FROM `" . DB_PREFIX . "manufacturer` m"
				. " WHERE su.`query` = CONCAT('manufacturer_id=', m.`manufacturer_id`)"
				. " AND m.`name` LIKE " . $like . ")"
			. " OR EXISTS (SELECT 1 FROM `" . DB_PREFIX . "information_description` id"
				. " WHERE su.`query` = CONCAT('information_id=', id.`information_id`)"
				. " AND id.`title` LIKE " . $like . ")"
			. " OR EXISTS (SELECT 1 FROM `" . DB_PREFIX . "article_description` ad"
				. " WHERE su.`query` = CONCAT('article_id=', ad.`article_id`)"
				. " AND ad.`name` LIKE " . $like . ")"
			. " OR EXISTS (SELECT 1 FROM `" . DB_PREFIX . "blog_category_description` bcd"
				. " WHERE su.`query` = CONCAT('blog_category_id=', bcd.`blog_category_id`)"
				. " AND bcd.`name` LIKE " . $like . ")"
			. ")";
	}


	private function clearSeoCache() {
		if ($this->config->get('config_seo_pro')) {
			$this->cache->delete('seopro');
		}
	}


	public function getSeoUrlAudit() {
		if ($this->audit_cache !== null) {
			return $this->audit_cache;
		}

		$audit = array(
			'issues' => array(),
			'groups' => array(
				'query' => array(),
				'keyword' => array(),
				'shared_language' => array()
			),
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
		$query_group_labels = array();
		$keyword_groups = array();
		$keyword_group_labels = array();
		$shared_language_groups = array();
		$shared_language_group_labels = array();
		$entity_ids = array(
			'product' => array(),
			'category' => array(),
			'manufacturer' => array(),
			'information' => array(),
			'article' => array(),
			'blog_category' => array()
		);
		$entity_rows = array(
			'product' => array(),
			'category' => array(),
			'manufacturer' => array(),
			'information' => array(),
			'article' => array(),
			'blog_category' => array()
		);

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
				$query_group_labels[$query_key] = $route_query;
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
				$keyword_group_labels[$keyword_key] = $keyword;

				if (!isset($shared_language_groups[$store_id . '|' . $keyword])) {
					$shared_language_groups[$store_id . '|' . $keyword] = array();
				}

				$shared_key = $store_id . '|' . $keyword;
				$shared_language_groups[$shared_key][$language_id][] = $seo_url_id;
				$shared_language_group_labels[$shared_key] = $keyword;

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

		foreach ($query_groups as $group_key => $ids) {
			if (count($ids) < 2) {
				continue;
			}

			$count = count($ids);
			$audit['summary']['query_groups']++;
			$audit['summary']['query_rows'] += $count;

			foreach ($ids as $seo_url_id) {
				$audit['issues'][$seo_url_id]['query'] = true;
				$audit['groups']['query'][$seo_url_id] = array(
					'key' => $group_key,
					'label' => isset($query_group_labels[$group_key]) ? $query_group_labels[$group_key] : '',
					'count' => $count
				);
			}
		}

		foreach ($keyword_groups as $group_key => $ids) {
			if (count($ids) < 2) {
				continue;
			}

			$count = count($ids);
			$audit['summary']['keyword_groups']++;
			$audit['summary']['keyword_rows'] += $count;

			foreach ($ids as $seo_url_id) {
				$audit['issues'][$seo_url_id]['keyword'] = true;
				$audit['groups']['keyword'][$seo_url_id] = array(
					'key' => $group_key,
					'label' => isset($keyword_group_labels[$group_key]) ? $keyword_group_labels[$group_key] : '',
					'count' => $count
				);
			}
		}

		foreach ($shared_language_groups as $group_key => $languages) {
			if (count($languages) < 2) {
				continue;
			}

			$ids = array();

			foreach ($languages as $language_ids) {
				$ids = array_merge($ids, $language_ids);
			}

			$count = count($ids);
			$audit['summary']['shared_language_groups']++;
			$audit['summary']['shared_language_rows'] += $count;

			foreach ($ids as $seo_url_id) {
				$audit['issues'][$seo_url_id]['shared_language'] = true;
				$audit['groups']['shared_language'][$seo_url_id] = array(
					'key' => $group_key,
					'label' => isset($shared_language_group_labels[$group_key])
						? $shared_language_group_labels[$group_key]
						: '',
					'count' => $count
				);
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
