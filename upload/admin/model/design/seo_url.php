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

	/**
	 * Keep standard per-language SEO rows intact.
	 *
	 * Prefix mode changes only storefront lookup: the catalog default language
	 * keyword is public for every language. Other language rows remain stored
	 * as fallbacks for switching prefix mode off without a data migration.
	 */
	public function normalizeLanguageScopedSeoUrls($seo_urls) {
		return is_array($seo_urls) ? $seo_urls : array();
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


	/**
	 * Build a direct storefront URL without SEO rewriting.
	 *
	 * Opening this URL exercises the real catalog startup and therefore shows
	 * whether the configured canonical redirect actually works.
	 */
	public function getSeoUrlPreview($row) {
		if (!is_array($row) || empty($row['query'])) {
			return '';
		}

		return $this->buildDirectFrontendUrl(
			(string)$row['query'],
			(int)$row['store_id']
		);
	}

	private function buildDirectFrontendUrl($route_query, $store_id) {
		$base = $this->getStoreBaseUrl($store_id);

		if ($base === '') {
			return '';
		}

		$route_query = trim((string)$route_query);
		$route = '';
		$params = array();

		if ($route_query === 'common/home') {
			$route = 'common/home';
		} elseif (preg_match('/^(product_id|category_id|manufacturer_id|information_id|article_id|blog_category_id)=([0-9]+)$/', $route_query, $match)) {
			$id = (int)$match[2];

			switch ($match[1]) {
				case 'product_id':
					$route = 'product/product';
					$params['product_id'] = $id;
					break;
				case 'category_id':
					$route = 'product/category';
					$params['path'] = $id;
					break;
				case 'manufacturer_id':
					$route = 'product/manufacturer/info';
					$params['manufacturer_id'] = $id;
					break;
				case 'information_id':
					$route = 'information/information';
					$params['information_id'] = $id;
					break;
				case 'article_id':
					$route = 'blog/article';
					$params['article_id'] = $id;
					break;
				case 'blog_category_id':
					$route = 'blog/category';
					$params['blog_category_id'] = $id;
					break;
			}
		} elseif (strpos($route_query, '=') === false) {
			$route = $route_query;
		} else {
			parse_str($route_query, $params);

			if (!empty($params['route'])) {
				$route = (string)$params['route'];
				unset($params['route']);
			}
		}

		$url = rtrim($base, '/') . '/index.php';

		if ($route !== '') {
			// Route names are application paths, not arbitrary query data. Keep the
			// familiar OpenCart form: route=product/product instead of product%2Fproduct.
			$url .= '?route=' . $route;

			foreach ($params as $key => $value) {
				if (is_scalar($value)) {
					$url .= '&' . rawurlencode((string)$key) . '=' . rawurlencode((string)$value);
				}
			}

			return $url;
		}

		return $route_query !== ''
			? $url . '?' . ltrim($route_query, '?&')
			: $url;
	}

	private function getStoreBaseUrl($store_id) {
		$store_id = (int)$store_id;
		$secure = !empty($this->request->server['HTTPS'])
			&& strtolower((string)$this->request->server['HTTPS']) !== 'off';

		if ($store_id > 0) {
			$query = $this->db->query("SELECT `url`, `ssl` FROM `" . DB_PREFIX . "store`
				WHERE `store_id` = '" . $store_id . "'
				LIMIT 1");

			if ($query->num_rows) {
				$url = trim((string)$query->row['url']);
				$ssl = trim((string)$query->row['ssl']);

				if ($secure && $ssl !== '') {
					return $ssl;
				}

				return $url !== '' ? $url : $ssl;
			}
		}

		/*
		 * The default storefront URL is normally defined in admin/config.php as
		 * HTTP_CATALOG / HTTPS_CATALOG. It is not required to exist as config_url
		 * in oc_setting, so relying on the settings table makes previews disappear.
		 */
		if ($secure && defined('HTTPS_CATALOG') && trim((string)HTTPS_CATALOG) !== '') {
			return trim((string)HTTPS_CATALOG);
		}

		if (defined('HTTP_CATALOG') && trim((string)HTTP_CATALOG) !== '') {
			return trim((string)HTTP_CATALOG);
		}

		if (defined('HTTPS_CATALOG') && trim((string)HTTPS_CATALOG) !== '') {
			return trim((string)HTTPS_CATALOG);
		}

		// Compatibility fallback for non-standard installations.
		$url = trim((string)$this->getStoreConfigValue(0, 'config_url', ''));
		$ssl = trim((string)$this->getStoreConfigValue(0, 'config_ssl', ''));

		return $secure && $ssl !== '' ? $ssl : ($url !== '' ? $url : $ssl);
	}

	private function getStoreConfigValue($store_id, $key, $default = null) {
		$setting = $this->getEffectiveLanguageSetting(
			(int)$store_id,
			array(
				array('config', (string)$key)
			)
		);

		return $setting !== null && array_key_exists('value', $setting)
			? $setting['value']
			: $default;
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



	/**
	 * Build the keyword used by the inline generator in design/seo_url.
	 *
	 * Everything is resolved on the server so disabled languages and non-entity
	 * routes behave exactly like normal rows and the browser does not need to
	 * reconstruct store/language rules.
	 */
	public function getSeoUrlGeneratorValue($row, $source_name = '') {
		$source = $this->getSeoUrlGeneratorSource($row, $source_name);

		if ($source === '') {
			return '';
		}

		$keyword = $this->transliterateSeoKeyword($source);

		if ($keyword === '') {
			return '';
		}

		$prefix = $this->getSeoUrlGeneratorPrefix($row);

		return $prefix !== '' ? $prefix . '_' . $keyword : $keyword;
	}

	/**
	 * Return a human-readable source for keyword generation.
	 *
	 * Entity rows use the actual localized title/name. Plain OpenCart routes use
	 * their final route segment (extension/feed/ocfilter_sitemap -> ocfilter sitemap).
	 */
	private function getSeoUrlGeneratorSource($row, $source_name = '') {
		$source_name = trim((string)$source_name);

		if ($source_name !== '') {
			return $source_name;
		}

		if (!is_array($row) || empty($row['query'])) {
			return '';
		}

		$route_query = trim((string)$row['query']);

		// Homepage is structural and never receives a generated keyword.
		if ($route_query === 'common/home') {
			return '';
		}

		/*
		 * Closed protected set: these are core/entity query formats we understand.
		 * If their source entity is missing, do not manufacture an ID-based slug.
		 * Every other query belongs to the generic extension path below.
		 */
		if (preg_match('/^(product_id|category_id|manufacturer_id|information_id|article_id|blog_category_id)=[0-9]+$/', $route_query)) {
			return '';
		}

		// Plain route: extension/feed/ocfilter_sitemap -> ocfilter sitemap.
		if (strpos($route_query, '=') === false) {
			$parts = preg_split('#/+#', trim($route_query, '/'));
			$last = $parts ? end($parts) : '';

			return $last !== false
				? trim(preg_replace('/[_\\-.]+/', ' ', (string)$last))
				: '';
		}

		/*
		 * Unknown module/legacy query: use only its semantic key and ignore the
		 * numeric ID. No module-specific guessing is done here.
		 * blogcategory_id=1 -> blogcategory
		 */
		$first = explode('&', $route_query, 2);
		$pair = explode('=', $first[0], 2);
		$key = isset($pair[0]) ? trim((string)$pair[0]) : '';

		if ($key === '') {
			return '';
		}

		$key = preg_replace('/_id$/', '', $key);
		$key = preg_replace('/[_\\-.]+/', ' ', $key);

		return trim((string)$key);
	}

	private function getSeoUrlGeneratorPrefix($row) {
		if (!is_array($row) || empty($row['language_id'])) {
			return '';
		}

		$language_id = (int)$row['language_id'];
		$store_id = isset($row['store_id']) ? (int)$row['store_id'] : 0;
		$default_language_id = $this->getStoreDefaultLanguageId($store_id);

		if ($default_language_id > 0 && $language_id === $default_language_id) {
			return '';
		}

		$query = $this->db->query("SELECT `code` FROM `" . DB_PREFIX . "language`
			WHERE `language_id` = '" . $language_id . "'
			LIMIT 1");

		if (!$query->num_rows || empty($query->row['code'])) {
			return '';
		}

		$code = strtolower(str_replace('_', '-', (string)$query->row['code']));
		$parts = explode('-', $code);
		$prefix = isset($parts[0]) ? preg_replace('/[^a-z0-9]/', '', $parts[0]) : '';

		return (string)$prefix;
	}

	private function transliterateSeoKeyword($text) {
		$text = trim((string)$text);

		if ($text === '') {
			return '';
		}

		if (function_exists('utf8_strtolower')) {
			$text = utf8_strtolower($text);
		} elseif (function_exists('mb_strtolower')) {
			$text = mb_strtolower($text, 'UTF-8');
		} else {
			$text = strtr($text, array(
				'А'=>'а','Б'=>'б','В'=>'в','Г'=>'г','Д'=>'д','Е'=>'е','Ё'=>'ё','Ж'=>'ж','З'=>'з','И'=>'и','Й'=>'й',
				'К'=>'к','Л'=>'л','М'=>'м','Н'=>'н','О'=>'о','П'=>'п','Р'=>'р','С'=>'с','Т'=>'т','У'=>'у','Ф'=>'ф',
				'Х'=>'х','Ц'=>'ц','Ч'=>'ч','Ш'=>'ш','Щ'=>'щ','Ы'=>'ы','Э'=>'э','Ю'=>'ю','Я'=>'я','Ъ'=>'ъ','Ь'=>'ь',
				'Ґ'=>'ґ','І'=>'і','Є'=>'є','Ї'=>'ї'
			));
			$text = strtolower($text);
		}

		$text = strtr($text, array(
			'а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'yo',
			'ж'=>'zh','з'=>'z','и'=>'i','й'=>'y','к'=>'k','л'=>'l','м'=>'m',
			'н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u',
			'ф'=>'f','х'=>'kh','ц'=>'ts','ч'=>'ch','ш'=>'sh','щ'=>'shch',
			'ы'=>'y','э'=>'e','ю'=>'yu','я'=>'ya','ъ'=>'','ь'=>'',
			'ґ'=>'g','і'=>'i','є'=>'ye','ї'=>'yi','&'=>'and'
		));

		$text = preg_replace('/\\s+/u', '-', $text);
		$text = str_replace('.', '-', $text);
		$text = preg_replace('/[^a-z0-9\\-_]/', '', $text);
		$text = preg_replace('/-+/', '-', $text);
		$text = preg_replace('/_+/', '_', $text);

		return trim($text, '-');
	}


	private function buildSeoUrlSearchCondition($value) {
		$value = trim((string)$value);
		$escaped = $this->db->escape($value);
		$like = "'%" . $escaped . "%'";
		$conditions = array(
			"su.`query` LIKE " . $like,
			"su.`keyword` LIKE " . $like
		);

		$entity_queries = $this->getSeoUrlSearchEntityQueries($value);

		if ($entity_queries) {
			$quoted = array();

			foreach ($entity_queries as $entity_query) {
				$quoted[] = "'" . $this->db->escape($entity_query) . "'";
			}

			$conditions[] = "su.`query` IN (" . implode(',', $quoted) . ")";
		}

		return '(' . implode(' OR ', $conditions) . ')';
	}

	/**
	 * Resolve matching entity names once per request, then filter seo_url by its
	 * compact query column. This avoids six correlated EXISTS subqueries for
	 * every seo_url row and avoids repeating the source scans for COUNT + list.
	 */
	private function getSeoUrlSearchEntityQueries($value) {
		$value = trim((string)$value);
		$cache_key = md5($value);

		if (isset($this->search_condition_cache[$cache_key])) {
			return $this->search_condition_cache[$cache_key];
		}

		$result = array();

		// One-character source-name scans are unbounded on large description
		// tables. Query/keyword search above still works for any input length.
		$length = function_exists('utf8_strlen') ? utf8_strlen($value) : strlen($value);

		if ($value === '' || $length < 2) {
			$this->search_condition_cache[$cache_key] = array();
			return array();
		}

		$definitions = array(
			array('table' => 'product_description', 'id' => 'product_id', 'name' => 'name', 'query' => 'product_id'),
			array('table' => 'category_description', 'id' => 'category_id', 'name' => 'name', 'query' => 'category_id'),
			array('table' => 'manufacturer', 'id' => 'manufacturer_id', 'name' => 'name', 'query' => 'manufacturer_id'),
			array('table' => 'information_description', 'id' => 'information_id', 'name' => 'title', 'query' => 'information_id'),
			array('table' => 'article_description', 'id' => 'article_id', 'name' => 'name', 'query' => 'article_id'),
			array('table' => 'blog_category_description', 'id' => 'blog_category_id', 'name' => 'name', 'query' => 'blog_category_id')
		);

		$like = "'%" . $this->db->escape($value) . "%'";

		foreach ($definitions as $definition) {
			$query = $this->db->query(
				"SELECT DISTINCT `" . $definition['id'] . "`"
				. " FROM `" . DB_PREFIX . $definition['table'] . "`"
				. " WHERE `" . $definition['name'] . "` LIKE " . $like
			);

			foreach ($query->rows as $row) {
				$id = isset($row[$definition['id']]) ? (int)$row[$definition['id']] : 0;

				if ($id > 0) {
					$result[$definition['query'] . '=' . $id] = $definition['query'] . '=' . $id;
				}
			}
		}

		$this->search_condition_cache[$cache_key] = array_values($result);

		return $this->search_condition_cache[$cache_key];
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
				/*
				 * common/home may contain a legacy language-prefix alias. That row is
				 * structural routing metadata, not an entity slug, so do not let it
				 * create a normal keyword-duplicate group.
				 */
				$structural_home_prefix = $route_query === 'common/home'
					&& $this->isOwnLanguagePrefix($keyword, $store_id, $language_id);

				if (!$structural_home_prefix) {
					// Keep entity keywords unique across the whole store so fallback
					// language rows are safe when prefix mode is disabled.
					$keyword_key = $store_id . '|' . $keyword;

					if (!isset($keyword_groups[$keyword_key])) {
						$keyword_groups[$keyword_key] = array();
					}

					$keyword_groups[$keyword_key][] = $seo_url_id;
					$keyword_group_labels[$keyword_key] = $keyword;
				}

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
