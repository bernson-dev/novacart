<?php
/**
 * @package     SeoPro
 * @author      Oclabs
 * @copyright   Copyright (c) 2017, Oclabs (https://www.oclabs.pro/)
 * @copyright   Copyright (c) 2021, ocStore (https://ocstore.com/)
 * @license     https://opensource.org/licenses/GPL-3.0
 */

// ALTER TABLE `oc_product_to_category` ADD `main_category_id` TINYINT(1) NOT NULL DEFAULT '0' AFTER `category_id`;

class SeoPro {
	private $config;
	private $ajax = false;
	private $request;
	private $registry;
	private $response;
	private $url;
	private $session;
	private $db;
	private $cache;
	private $cat_tree = [];
	private $keywords = [];
	private $queries = [];
	private $product_categories = [];
	private $valide_get_param = [];
	private $language_home_alias = false;

	public function __construct($registry) {
		$this->registry = $registry;
		$this->config = $registry->get('config');
		$this->request = $registry->get('request');
		$this->session = $registry->get('session');
		$this->response = $registry->get('response');
		$this->url = $registry->get('url');
		$this->db = $registry->get('db');
		$this->cache = $registry->get('cache');

		$this->detectAjax();

		if (!$this->config->get('config_seo_pro')) {
			return;
		}

		$this->detectPostfix();
		$this->detectLanguage();
		$this->initHelpers();

		$params = explode("\r\n", (string)$this->config->get('config_valide_params'));
		$this->valide_get_param = array_filter($params);
	}

	public function prepareRoute($parts) {
		if (!$this->config->get('config_seo_pro')) {
			return $parts;
		}

		/*
		 * Synthetic language-home aliases are used only when a non-default
		 * language has no stored common/home keyword (usually because it used
		 * to be the default language). detectLanguage() has already selected the
		 * language, so this segment represents common/home rather than a normal
		 * SEO entity.
		 */
		if ($this->language_home_alias) {
			$this->request->get['route'] = 'common/home';
			return array();
		}

		$query = null;

		if (!empty($parts) && is_array($parts)) {
			foreach ($parts as $id => $part) {
				if ($this->config->get('config_seopro_lowercase')) {
					$parts[$id] = utf8_strtolower($part);
				}

				$keyword = trim((string)$parts[$id]);

				if ($keyword !== '') {
					$query = $this->getQueryByKeyword($keyword);
					$url = explode('=', (string)$query, 2);

					if (!empty($url[0])) {
					if(!in_array($url[0], ['category_id', 'product_id', 'manufacturer_id', 'information_id', 'article_id', 'blog_category_id'])) {
							return $parts;
						}

						if ($url[0] == 'category_id') {
							if (!isset($this->request->get['path'])) {
								$this->request->get['path'] = $url[1];
							} else {
								$this->request->get['path'] .= '_' . $url[1];
							}
						} elseif ($url[0] == 'blog_category_id') {
							if (!isset($this->request->get['blog_category_id'])) {
								$this->request->get['blog_category_id'] = $url[1];
							} else {
								$this->request->get['blog_category_id'] .= '_' . $url[1];
							}
						} elseif (count($url) > 1) {
							$this->request->get[$url[0]] = $url[1];
						}
					}
				}

				unset($parts[$id]);
			}

			if (!$query) {
				$this->request->get['route'] = 'error/not_found';
				return [];
			}
		}

		if (isset($this->request->get['product_id'])) {
			if (isset($this->request->get['path'])) {
				unset($this->request->get['path']);
			}

			$path = $this->getCategoryByProduct($this->request->get['product_id']);

			if ($path) {
				$this->request->get['path'] = $path;
			}

			$this->request->get['route'] = 'product/product';
		} elseif (isset($this->request->get['path'])) {
			$this->request->get['route'] = 'product/category';
		} elseif (isset($this->request->get['manufacturer_id'])) {
			$this->request->get['route'] = 'product/manufacturer/info';
		} elseif (isset($this->request->get['information_id'])) {
			$this->request->get['route'] = 'information/information';
		}

		// Blog
		if (isset($this->request->get['article_id'])) {
			if (isset($this->request->get['blog_category_id'])) {
				unset($this->request->get['blog_category_id']);
			}

			$blog_category_path = $this->getBlogPathByArticle($this->request->get['article_id']);

			if ($blog_category_path) {
				$this->request->get['blog_category_id'] = $blog_category_path;
			}

			$this->request->get['route'] = 'blog/article';
		} elseif (isset($this->request->get['blog_category_id'])) {
			$this->request->get['route'] = 'blog/category';
		}
		// End blog

		return $parts;
	}

	public function baseRewrite($data, $language_id) {
		if (!$this->config->get('config_seo_pro')) {
			return array(null, $data, false);
		}

		$url = null;
		$postfix = false;
		$language_id = (int)$this->config->get('config_language_id');

		if (empty($data['route'])) {
			return array($url, $data, $postfix);
		}

		/*
		 * In language-prefix mode common/home is a structural route, not a SEO
		 * keyword. This also prevents legacy common/home rows from producing a
		 * second language segment.
		 */
		if (
			$data['route'] === 'common/home'
			&& $this->registry->has('seo_language')
		) {
			$seo_language = $this->registry->get('seo_language');

			if ($seo_language instanceof SeoLanguage && $seo_language->isEnabled()) {
				$data = array('route' => 'common/home');
				return array('', $data, $postfix);
			}
		}


		/*
		 * The current catalog default always owns the bare store root. Do not let
		 * an old common/home row keep a prefix after config_language changes.
		 * Conversely, if a former default language has an empty home keyword,
		 * synthesize a deterministic alias without modifying the database.
		 */
		if ($data['route'] === 'common/home') {
			$default_language_id = $this->getDefaultLanguageId();

			if ($default_language_id && $language_id === $default_language_id) {
				return array('', array('route' => 'common/home'), $postfix);
			}

			$keyword = $this->getKeywordByQuery('common/home', $language_id);

			if ($keyword === null || $keyword === '') {
				$language_code = $this->getLanguageCodeById($language_id);
				$keyword = $this->getLanguageHomeAlias($language_code);
			}

			if ($keyword !== '') {
				return array('/' . rawurlencode($keyword), array('route' => 'common/home'), $postfix);
			}

			return array('', array('route' => 'common/home'), $postfix);
		}
		switch ($data['route']) {
			case 'product/product':
				if (isset($data['product_id'])) {
					$route = 'product/product';
					$path = '';
					$product_id = $data['product_id'];
					$valide_get_param_data = array();

					if (isset($data['path']) || $this->config->get('config_seo_url_include_path')) {
						$path = $this->getCategoryByProduct($product_id);
					}

					if ($this->valide_get_param) {
						foreach ($this->valide_get_param as $get_param) {
							if (isset($data[$get_param])) {
								$valide_get_param_data[$get_param] = $data[$get_param];
								$this->response->addHeader('X-Robots-Tag: noindex');
							}
						}
					}

					$data = array();
					$data['route'] = $route;

					if ($path && $this->config->get('config_seo_url_include_path')) {
						$data['path'] = $path;
					}

					$data['product_id'] = $product_id;

					if ($this->valide_get_param && $valide_get_param_data) {
						$data = array_merge($data, $valide_get_param_data);
					}
				}
				break;

			case 'blog/article':
				if (isset($data['article_id'])) {
					$route = 'blog/article';
					$blog_path = '';
					$article_id = $data['article_id'];
					$valide_get_param_data = array();

					if (isset($data['blog_category_id'])) {
						$blog_path = $this->getBlogPathByArticle($article_id);
					}

					if ($this->valide_get_param) {
						foreach ($this->valide_get_param as $get_param) {
							if (isset($data[$get_param])) {
								$valide_get_param_data[$get_param] = $data[$get_param];
								$this->response->addHeader('X-Robots-Tag: noindex');
							}
						}
					}

					$data = array();
					$data['route'] = $route;

					if ($blog_path && $this->config->get('config_seo_url_include_path')) {
						$data['blog_category_id'] = $blog_path;
					}

					$data['article_id'] = $article_id;

					if ($this->valide_get_param && $valide_get_param_data) {
						$data = array_merge($data, $valide_get_param_data);
					}
				}
				break;

			case 'product/category':
				if (isset($data['path'])) {
					$category = explode('_', $data['path']);
					$category = end($category);
					unset($data['information_id']);
					$data['path'] = $this->getPathByCategory($category);
				}
				break;

			case 'blog/article/review':
			case 'product/product/review':
			case 'information/information/agree':
				return array($url, $data, $postfix);
		}

		$queries = [];
		$route = '';

		if (isset($data['route'])) {
			$route = $data['route'];
			unset($data['route']);
		}

		foreach ($data as $key => $value) {
			switch ($key) {
				case 'product_id':
					$product_id = (int)$value;
					$queries[] = 'product_id=' . $product_id;
					$postfix = true;
					unset($data[$key]);
					break;

				case 'manufacturer_id':
					$manufacturer_id = (int)$value;
					$queries[] = 'manufacturer_id=' . $manufacturer_id;
					$postfix = true;
					unset($data[$key]);
					break;
				case 'category_id':
				case 'information_id':
					$information_id = (int)$value;
					$queries[] = 'information_id=' . $information_id;
					$postfix = true;
					unset($data[$key]);
					break;

				case 'blog_category_id':
					$blog_categories = explode('_', $value);
					foreach ($blog_categories as $blog_category_id) {
						$queries[] = 'blog_category_id=' . (int)$blog_category_id;
					}
					unset($data[$key]);
					break;

				case 'article_id':
					$article_id = (int)$value;
					$queries[] = 'article_id=' . $article_id;
					$postfix = true;
					unset($data[$key]);
					break;

				case 'path':
					$categories = explode('_', $value);
					foreach ($categories as $category_id) {
						$queries[] = 'category_id=' . (int)$category_id;
					}
					unset($data[$key]);
					break;
			}
		}

		if (empty($queries) && $route) {
			$keyword = $this->getKeywordByQuery($route, $language_id);

			if ($keyword !== null) {
				$url = '';

				if ($keyword !== '') {
					$url = '/' . rawurlencode($keyword);
				}
			}

			$data['route'] = $route;
		} else {
			$rows = [];

			foreach ($queries as $query) {
				$keyword = $this->getKeywordByQuery($query, $language_id);

				if ($keyword !== null && $keyword !== '') {
					$rows[] = $keyword;
				}
			}

			if (!empty($rows) && count($rows) == count($queries)) {
				$url = '';

				foreach ($rows as $row) {
					$url .= '/' . rawurlencode($row);
				}
			}
		}

		return [$url, $data, $postfix];
	}

	private function getPath($categories, $category_id, $current_path = []) {
		if (!$current_path) {
			$current_path = [(int)$category_id];
		}

		$path = $current_path;
		$parent_id = 0;

		if (isset($categories[$category_id]['parent_id'])) {
			$parent_id = (int)$categories[$category_id]['parent_id'];
		}

		if ($parent_id > 0) {
			$new_path = array_merge([$parent_id], $current_path);
			$path = $this->getPath($categories, $parent_id, $new_path);
		}

		return $path;
	}

	private function initHelpers() {
		if ($this->config->get('config_seo_url_cache')) {
			$cached = $this->cache->get('seopro.cat_tree');

			if ($cached && is_array($cached)) {
				$this->cat_tree = $cached;
			}
		}

		if (!$this->cat_tree || empty($this->cat_tree)) {
			$this->cat_tree = [];
			$all_cat_query = $this->db->query("SELECT category_id, parent_id FROM " . DB_PREFIX . "category ORDER BY parent_id");
			$allcats = [];
			$categories = [];

			if ($all_cat_query->num_rows) {
				$allcats = $all_cat_query->rows;
			}

			foreach ($allcats as $category) {
				$categories[(int)$category['category_id']]['parent_id'] = (int)$category['parent_id'];
			}

			unset($allcats);

			foreach ($categories as $category_id => $category) {
				$path = $this->getPath($categories, $category_id);
				$this->cat_tree[$category_id]['path'] = $path;
			}
		}

		if ($this->config->get('config_seo_url_cache')) {
			$this->keywords = $this->cache->get('seopro.keywords');
			$this->queries = $this->cache->get('seopro.queries');

			if (!$this->keywords || !is_array($this->keywords)) {
				$this->keywords = [];
			}

			if (!$this->queries || !is_array($this->queries)) {
				$this->queries = [];
			}

			if (empty($this->keywords) || empty($this->queries)) {
				$sql_keyword = 'keyword';

				if ($this->config->get('config_seopro_lowercase')) {
					$sql_keyword = 'LCASE(keyword) as ' . $sql_keyword;
				}

				$sql = "SELECT " . $sql_keyword . ", query, store_id, language_id FROM " . DB_PREFIX . "seo_url WHERE 1";
				$query = $this->db->query($sql);

				if ($query->num_rows && is_array($query->rows)) {
					foreach ($query->rows as $row) {
						$kw = (string)$row['keyword'];
						$q = (string)$row['query'];
						$sid = (int)$row['store_id'];
						$lid = (int)$row['language_id'];

						$this->keywords[$q][$sid][$lid] = $kw;
						$this->queries[$kw][$sid][$lid] = $q;
					}
				}
			}
		}
	}

	private function detectPostfix() {
		if ($this->config->get('config_page_postfix') && isset($this->request->get['_route_'])) {
			$postfix = preg_quote($this->config->get('config_page_postfix'), '/');
			$this->request->get['_route_'] = preg_replace('/' . $postfix . '$/', '', (string)$this->request->get['_route_']);
		}
	}

	private function getQueryByKeyword($keyword, $language_id = null) {
		$query = null;
		$store_id = (int)$this->config->get('config_store_id');

		if (!$language_id) {
			$language_id = (int)$this->config->get('config_language_id');
		}

		$keyword = trim((string)$keyword);

		if ($this->config->get('config_seo_url_cache')) {
			if (isset($this->queries[$keyword][$store_id][$language_id])) {
				$query = $this->queries[$keyword][$store_id][$language_id];
			}
		} else {
			$_query = $this->db->query("SELECT query FROM " . DB_PREFIX . "seo_url
				WHERE keyword = '" . $this->db->escape($keyword) . "'
				AND store_id = '" . $store_id . "'
				AND language_id = '" . (int)$language_id . "'
				LIMIT 1");

			$query = !empty($_query->row) ? (string)$_query->row['query'] : null;
		}

		return $query;
	}

	private function getKeywordByQuery($query, $language_id = null) {
		$keyword = null;
		$store_id = (int)$this->config->get('config_store_id');

		if (!$language_id) {
			$language_id = (int)$this->config->get('config_language_id');
		}

		if ($this->config->get('config_seo_url_cache')) {
			if (isset($this->keywords[$query][$store_id][$language_id])) {
				$keyword = $this->keywords[$query][$store_id][$language_id];
			}
		} else {
			$sql_keyword = 'keyword';

			if ($this->config->get('config_seopro_lowercase')) {
				$sql_keyword = 'LCASE(keyword) as ' . $sql_keyword;
			}

			$result = $this->db->query("SELECT " . $sql_keyword . " FROM " . DB_PREFIX . "seo_url
				WHERE query = '" . $this->db->escape($query) . "'
				AND store_id = '" . $store_id . "'
				AND language_id = '" . (int)$language_id . "'
				LIMIT 1");

			$keyword = !empty($result->row) ? (string)$result->row['keyword'] : null;
		}

		return $keyword;
	}

	public function validate() {
		if (!$this->config->get('config_seo_pro')) {
			return;
		}

		if (php_sapi_name() === 'cli') {
			return;
		}

		if (isset($this->request->get['route'])) {
			$break_routes = [
				'error/not_found',
				'extension/feed/google_sitemap',
				'extension/feed/google_base',
				'extension/feed/sitemap_pro',
				'extension/feed/yandex_feed'
			];

			if (in_array($this->request->get['route'], $break_routes)) {
				return;
			}
		}

		if (!empty($this->request->post)) {
			return;
		}

		if ($this->ajax) {
			$this->response->addHeader('X-Robots-Tag: noindex');
			return;
		}

		if (empty($this->request->get['route'])) {
			$this->request->get['route'] = 'common/home';
		}

		if (empty($this->request->server['REQUEST_URI'])) {
			return;
		}

		$uri = $this->request->server['REQUEST_URI'];
		$route = $this->request->get['route'];

		if (isset($this->request->get['page'])) {
			if ((float)$this->request->get['page'] < 1) {
				unset($this->request->get['page']);
			}
		}

		if (!empty($this->request->server['HTTPS']) && $this->request->server['HTTPS'] !== 'off') {
			$host = substr($this->config->get('config_ssl'), 0, $this->strpos_offset('/', $this->config->get('config_ssl'), 3) + 1);
		} else {
			$host = substr($this->config->get('config_url'), 0, $this->strpos_offset('/', $this->config->get('config_url'), 3) + 1);
		}

		if (!$this->config->get('config_seopro_addslash')) {
			if ($uri == '/') {
				$host = rtrim($host, '/');
			}
		}

		$url = str_replace('&amp;', '&', $host . ltrim($uri, '/'));
		$seo = str_replace('&amp;', '&', $this->url->link($route, $this->getQueryString(array('_route_', 'route')), !empty($this->request->server['HTTPS']) && $this->request->server['HTTPS'] !== 'off'));

		if (rawurldecode($url) != rawurldecode($seo)) {
			$this->response->redirect($seo, 301);
		}
	}

	private function detectAjax() {
		if (isset($this->request->server['HTTP_X_REQUESTED_WITH']) && strtolower($this->request->server['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
			$this->ajax = true;
		}
	}

	private function detectLanguage() {
		if (!$this->config->get('config_seo_pro') || $this->ajax) {
			return;
		}

		/*
		 * SeoLanguage owns language resolution when the dedicated prefix router
		 * is enabled. SeoPro must not infer the language again from entity slugs.
		 */
		if ($this->registry->has('seo_language')) {
			$seo_language = $this->registry->get('seo_language');

			if ($seo_language instanceof SeoLanguage && $seo_language->isEnabled()) {
				return;
			}
		}

		// The bare store root keeps the language restored by startup/startup.
		if (!isset($this->request->get['_route_'])) {
			return;
		}

		$route_parts = array();
		$parts = explode('/', (string)$this->request->get['_route_']);

		foreach ($parts as $part) {
			$part = trim((string)$part);

			if ($part !== '') {
				$route_parts[] = $part;
			}
		}

		if (!$route_parts) {
			return;
		}

		$keyword = end($route_parts);

		/*
		 * Normal SEO keywords always have priority. This prevents a real product,
		 * category or information slug such as "ua" from being mistaken for a
		 * language alias.
		 */
		$query = $this->db->query("SELECT su.language_id, su.query, l.code
			FROM " . DB_PREFIX . "seo_url su
			INNER JOIN " . DB_PREFIX . "language l ON (l.language_id = su.language_id)
			WHERE su.keyword = '" . $this->db->escape($keyword) . "'
			AND su.store_id = '" . (int)$this->config->get('config_store_id') . "'
			AND l.status = '1'
			LIMIT 1");

		if ($query->num_rows) {
			$this->applyDetectedLanguage(
				(int)$query->row['language_id'],
				(string)$query->row['code']
			);
			return;
		}

		/*
		 * A language that used to be the default can have an empty common/home
		 * keyword. After config_language changes it still needs a stable home
		 * alias. Resolve only a single unknown path segment and only when it
		 * matches an enabled language's deterministic home alias.
		 */
		if (count($route_parts) === 1) {
			$language = $this->getLanguageByHomeAlias($keyword);

			if ($language) {
				$this->applyDetectedLanguage(
					(int)$language['language_id'],
					(string)$language['code']
				);
				$this->language_home_alias = true;
			}
		}
	}

	private function applyDetectedLanguage($language_id, $language_code) {
		$language_id = (int)$language_id;
		$language_code = (string)$language_code;

		if ($language_id < 1 || $language_code === '') {
			return;
		}

		/*
		 * Keep the complete language state synchronized in the same request.
		 * Updating only the session caused transient 404 pages and redirect loops
		 * until a later request rebuilt config_language_id and Language.
		 */
		$this->session->data['language'] = $language_code;
		$this->config->set('config_language_id', $language_id);

		$language = new Language($language_code);
		$language->load($language_code);
		$this->registry->set('language', $language);

		if (PHP_SAPI !== 'cli' && !headers_sent()) {
			setcookie('language', $language_code, time() + 60 * 60 * 24 * 30, '/');
		}
	}

	private function getLanguageByHomeAlias($alias) {
		$alias = strtolower(trim((string)$alias, '/'));

		if (!preg_match('/^[a-z0-9][a-z0-9_-]{0,31}$/', $alias)) {
			return false;
		}

		$query = $this->db->query("SELECT language_id, code FROM " . DB_PREFIX . "language
			WHERE status = '1'
			ORDER BY sort_order, name");

		foreach ($query->rows as $language) {
			if ($this->getLanguageHomeAlias($language['code']) === $alias) {
				return $language;
			}
		}

		return false;
	}

	private function getLanguageHomeAlias($code) {
		$code = strtolower(str_replace('_', '-', (string)$code));
		$parts = explode('-', $code);
		$alias = !empty($parts[0]) ? $parts[0] : '';

		// Octemplates Deals displays Ukrainian as "ua" rather than locale "uk".
		if ($this->isOctDealsTheme() && $alias === 'uk') {
			$alias = 'ua';
		}

		return $alias;
	}

	private function isOctDealsTheme() {
		$theme = strtolower((string)$this->config->get('config_theme'));

		return $theme === 'oct_deals' || strpos($theme, 'oct_deals') !== false;
	}

	private function getDefaultLanguageId() {
		$code = (string)$this->config->get('config_language');

		if ($code === '') {
			return 0;
		}

		$query = $this->db->query("SELECT language_id FROM " . DB_PREFIX . "language
			WHERE code = '" . $this->db->escape($code) . "'
			AND status = '1'
			LIMIT 1");

		return $query->num_rows ? (int)$query->row['language_id'] : 0;
	}

	private function getLanguageCodeById($language_id) {
		$query = $this->db->query("SELECT code FROM " . DB_PREFIX . "language
			WHERE language_id = '" . (int)$language_id . "'
			AND status = '1'
			LIMIT 1");

		return $query->num_rows ? (string)$query->row['code'] : '';
	}

	private function getCategoryByProduct($product_id) {
		if ((int)$product_id < 1) {
			return false;
		}

		if ($this->config->get('config_seo_url_cache')) {
			if (!is_array($this->product_categories)) {
				$this->product_categories = $this->cache->get('seopro.product_categories');

				if (!is_array($this->product_categories)) {
					$this->product_categories = [];
				}
			}

			if (isset($this->product_categories[$product_id])) {
				return $this->product_categories[$product_id];
			}
		}

		$query = $this->db->query("SELECT category_id FROM " . DB_PREFIX . "product_to_category
			WHERE product_id = '" . (int)$product_id . "'
			ORDER BY main_category DESC
			LIMIT 1");

		$category_id = $this->getPathByCategory($query->num_rows ? (int)$query->row['category_id'] : 0);

		if ($this->config->get('config_seo_url_cache')) {
			if (!is_array($this->product_categories)) {
				$this->product_categories = [];
			}

			$this->product_categories[$product_id] = $category_id;
		}

		return $category_id;
	}

	private function getPathByCategory($category_id) {
		$path = '';

		if ((int)$category_id < 1 || !isset($this->cat_tree[$category_id])) {
			return false;
		}

		if (!empty($this->cat_tree[$category_id]['path']) && is_array($this->cat_tree[$category_id]['path'])) {
			$path = implode('_', $this->cat_tree[$category_id]['path']);
		}

		return $path;
	}

	private function getBlogPathByArticle($article_id) {
		if ($article_id < 1) {
			return false;
		}

		$query = $this->db->query("SELECT blog_category_id FROM " . DB_PREFIX . "article_to_blog_category
			WHERE article_id = '" . (int)$article_id . "'
			ORDER BY main_blog_category DESC
			LIMIT 1");

		$blog_category_path = $this->getBlogPathByCategory($query->num_rows ? (int)$query->row['blog_category_id'] : 0);

		return $blog_category_path;
	}

	private function getBlogPathByCategory($blog_category_id) {
		$blog_category_id = (int)$blog_category_id;

		if ($blog_category_id < 1) {
			return false;
		}

		static $blog_path = [];
		$cache = 'seopro.blog_category.seopath';

		if (!is_array($blog_path)) {
			if ($this->config->get('config_seo_url_cache')) {
				$cached = $this->cache->get($cache);
				$blog_path = is_array($cached) ? $cached : [];
			} else {
				$blog_path = [];
			}
		}

		if (!isset($blog_path[$blog_category_id])) {
			$max_level = 10;
			$sql = "SELECT CONCAT_WS('_'";

			for ($i = $max_level - 1; $i >= 0; --$i) {
				$sql .= ",t" . $i . ".blog_category_id";
			}

			$sql .= ") AS path FROM " . DB_PREFIX . "blog_category t0";

			for ($i = 1; $i < $max_level; ++$i) {
				$sql .= " LEFT JOIN " . DB_PREFIX . "blog_category t" . $i . " ON (t" . $i . ".blog_category_id = t" . ($i - 1) . ".parent_id)";
			}

			$sql .= " WHERE t0.blog_category_id = '" . $blog_category_id . "'";

			$query = $this->db->query($sql);
			$blog_path[$blog_category_id] = $query->num_rows ? $query->row['path'] : false;

			if ($this->config->get('config_seo_url_cache')) {
				$this->cache->set($cache, $blog_path);
			}
		}

		return $blog_path[$blog_category_id];
	}

	private function strpos_offset($needle, $haystack, $occurrence) {
		$arr = explode($needle, $haystack);

		switch ($occurrence) {
			case $occurrence == 0:
				return false;
			case $occurrence > max(array_keys($arr)):
				return false;
			default:
				return strlen(implode($needle, array_slice($arr, 0, $occurrence)));
		}
	}

	private function getQueryString($exclude = []) {
		if (!is_array($exclude)) {
			$exclude = [];
		}

		return urldecode(http_build_query(array_diff_key($this->request->get, array_flip($exclude))));
	}

	public function __destruct() {
		if (!$this->config || !$this->config->get('config_seo_pro')) {
			return;
		}

		if ($this->config->get('config_seo_url_cache')) {
			$this->cache->set('seopro.keywords', $this->keywords);
			$this->cache->set('seopro.queries', $this->queries);
			$this->cache->set('seopro.cat_tree', $this->cat_tree);
			$this->cache->set('seopro.product_categories', $this->product_categories);
		}
	}
}
