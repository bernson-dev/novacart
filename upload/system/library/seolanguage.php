<?php
/**
 * Language prefix resolver for SEO URLs.
 *
 * This service deliberately keeps language routing separate from the SEO
 * engine. It resolves only the first URL segment, then leaves the remaining
 * route to the standard seo_url controller or SeoPro.
 */
class SeoLanguage {
	private $registry;
	private $config;
	private $request;
	private $response;
	private $session;
	private $db;
	private $url;
	private $languages;
	private $prefixes;

	public function __construct($registry) {
		$this->registry = $registry;
		$this->config = $registry->get('config');
		$this->request = $registry->get('request');
		$this->response = $registry->get('response');
		$this->session = $registry->get('session');
		$this->db = $registry->get('db');
		$this->url = $registry->get('url');
	}

	public function isEnabled() {
		$status = $this->config->has('module_seo_language_status')
			? $this->config->get('module_seo_language_status')
			: $this->config->get('seo_language_status');

		return (bool)$this->config->get('config_seo_url') && (bool)$status;
	}

	/**
	 * Resolve the language from the first SEO path segment.
	 *
	 * Prefixed URLs explicitly select a language. Unprefixed public SEO URLs
	 * belong to the configured catalog default language. Internal index.php
	 * routes keep the language already selected by the normal startup logic.
	 */
	public function resolve() {
		$languages = $this->getLanguages();

		if (!$languages) {
			return;
		}

		$default = $this->getDefaultLanguage($languages);

		if (!$default) {
			return;
		}

		if (!$this->isEnabled()) {
			return;
		}

		if (isset($this->request->get['_route_']) && is_scalar($this->request->get['_route_'])) {
			$route = trim((string)$this->request->get['_route_'], '/');
			$target = $default;
			$matched_prefix = '';
			$parts = $route === '' ? array() : explode('/', $route);

			if ($parts) {
				$first = strtolower(rawurldecode((string)$parts[0]));
				$prefix_map = $this->getPrefixMap($languages);

				if (isset($prefix_map[$first])) {
					$target = $prefix_map[$first];
					$matched_prefix = $first;
					array_shift($parts);

					if ($parts) {
						$this->request->get['_route_'] = implode('/', $parts);
					} else {
						unset($this->request->get['_route_']);
					}
				}
			}

			$this->applyLanguage($target);

			/*
			 * The default language always owns the store root. Keep an old or
			 * manually entered default-language prefix working only as a
			 * canonical alias, then redirect it to the unprefixed URL.
			 */
			if (
				$matched_prefix !== ''
				&& $target['code'] === $default['code']
				&& $this->isSafeRedirectMethod()
			) {
				$this->redirectWithoutDefaultPrefix($matched_prefix);
			}

			return;
		}

		/*
		 * A plain storefront root has no _route_. It is still a public SEO URL,
		 * therefore it must always represent the catalog default language
		 * rather than the language left in a previous session/cookie.
		 */
		if (
			!isset($this->request->get['route'])
			&& $this->isSafeRedirectMethod()
			&& $this->isStoreRootRequest()
		) {
			$preferred = $this->getPreferredLanguage($languages);

			if ($preferred && $preferred['code'] !== $default['code']) {
				$prefix = $this->getPrefixByCode($preferred['code']);

				if ($prefix !== '') {
					$this->applyLanguage($preferred);
					$this->redirectToLanguageRoot($prefix);
				}
			}

			$this->applyLanguage($default);
		}
	}

	/**
	 * Add the active non-default language prefix to a generated SEO URL.
	 */
	public function rewrite($url) {
		if (!$this->isEnabled() || !is_string($url) || $url === '') {
			return $url;
		}

		$languages = $this->getLanguages();

		if (!$languages) {
			return $url;
		}

		$default = $this->getDefaultLanguage($languages);
		$active = $this->getLanguageById((int)$this->config->get('config_language_id'), $languages);

		if (!$default || !$active || $active['code'] === $default['code']) {
			return $url;
		}

		$prefix = $this->getPrefixByCode($active['code']);

		if ($prefix === '') {
			return $url;
		}

		$url_info = parse_url(str_replace('&amp;', '&', $url));

		if (
			!is_array($url_info)
			|| empty($url_info['scheme'])
			|| empty($url_info['host'])
		) {
			return $url;
		}

		$base = $this->getBaseUrlForScheme($url_info['scheme']);
		$base_info = parse_url($base);

		if (
			!is_array($base_info)
			|| empty($base_info['host'])
			|| strcasecmp((string)$base_info['host'], (string)$url_info['host']) !== 0
		) {
			return $url;
		}

		$base_path = $this->normalizeBasePath(isset($base_info['path']) ? $base_info['path'] : '/');
		$path = isset($url_info['path']) ? (string)$url_info['path'] : '/';
		$base_without_slash = rtrim($base_path, '/');

		if ($base_without_slash === '') {
			$relative = ltrim($path, '/');
		} elseif ($path === $base_without_slash) {
			$relative = '';
		} elseif (strpos($path, $base_path) === 0) {
			$relative = ltrim(substr($path, strlen($base_path)), '/');
		} else {
			return $url;
		}

		$relative_parts = $relative === '' ? array() : explode('/', $relative);

		if (
			$relative_parts
			&& strtolower(rawurldecode((string)$relative_parts[0])) === $prefix
		) {
			return $url;
		}

		$new_path = $base_path . rawurlencode($prefix) . '/';

		if ($relative !== '') {
			$new_path .= $relative;
		}

		$result = $url_info['scheme'] . '://' . $url_info['host'];

		if (isset($url_info['port'])) {
			$result .= ':' . (int)$url_info['port'];
		}

		$result .= $new_path;

		if (isset($url_info['query']) && $url_info['query'] !== '') {
			$result .= '?' . $url_info['query'];
		}

		if (isset($url_info['fragment']) && $url_info['fragment'] !== '') {
			$result .= '#' . $url_info['fragment'];
		}

		return str_replace('&', '&amp;', $result);
	}

	public function getPrefixByLanguageId($language_id) {
		$languages = $this->getLanguages();
		$language = $this->getLanguageById((int)$language_id, $languages);

		if (!$language) {
			return '';
		}

		$default = $this->getDefaultLanguage($languages);

		if ($default && $language['code'] === $default['code']) {
			return '';
		}

		return $this->getPrefixByCode($language['code']);
	}

	public function getConfiguredPrefixByLanguageId($language_id) {
		$languages = $this->getLanguages();
		$language = $this->getLanguageById((int)$language_id, $languages);

		return $language ? $this->getPrefixByCode($language['code']) : '';
	}


	/**
	 * Return enabled storefront languages without exposing the internal cache.
	 */
	public function getEnabledLanguages() {
		return array_values($this->getLanguages());
	}

	/**
	 * Build alternate URLs for the same logical route in every enabled language.
	 *
	 * Only config_language_id is changed temporarily. Session, cookies and the
	 * active Language object remain untouched, so hreflang/sitemap generation
	 * cannot change the visitor's language.
	 *
	 * @param string $route
	 * @param array  $params
	 * @param bool   $ssl
	 *
	 * @return array hreflang => URL, including x-default
	 */
	public function getAlternateLinks($route, $params = array(), $ssl = false) {
		if (!$this->isEnabled() || !$this->url || !is_string($route) || $route === '') {
			return array();
		}

		if (!is_array($params)) {
			$params = array();
		}

		$languages = $this->getLanguages();
		$default = $this->getDefaultLanguage($languages);
		$links = array();
		$default_hreflang = '';

		foreach ($languages as $language) {
			$url = $this->getUrlForLanguage(
				$route,
				$params,
				(int)$language['language_id'],
				(bool)$ssl
			);

			if ($url === '') {
				continue;
			}

			$links[$this->normalizeHreflangCode($language['code'])] = $url;
		}

		if ($default) {
			$default_url = $this->getUrlForLanguage(
				$route,
				$params,
				(int)$default['language_id'],
				(bool)$ssl
			);

			if ($default_url !== '') {
				$links['x-default'] = $default_url;
			}
		}

		return $links;
	}

	/**
	 * Generate one URL in a requested language without modifying visitor state.
	 */
	public function getUrlForLanguage($route, $params, $language_id, $ssl = false) {
		$languages = $this->getLanguages();
		$language = $this->getLanguageById((int)$language_id, $languages);

		if (
			!$this->isEnabled()
			|| !$this->url
			|| !$language
			|| !is_string($route)
			|| $route === ''
		) {
			return '';
		}

		if (!is_array($params)) {
			$params = array();
		}

		$original_language_id = $this->config->get('config_language_id');

		try {
			$this->config->set('config_language_id', (int)$language['language_id']);

			$query = $params
				? http_build_query($params, '', '&', PHP_QUERY_RFC3986)
				: '';

			return $this->url->link($route, $query, (bool)$ssl);
		} finally {
			$this->config->set('config_language_id', $original_language_id);
		}
	}

	/**
	 * Check that the logical page exists in the requested language.
	 *
	 * Routes without a language-bound entity are considered available. Entity
	 * routes are verified against their description table so hreflang never
	 * points to a localized 404 page.
	 */
	public function isRouteAvailableForLanguage($route, $params, $language_id) {
		$route = (string)$route;
		$language_id = (int)$language_id;
		$params = is_array($params) ? $params : array();

		if ($language_id < 1) {
			return false;
		}

		switch ($route) {
			case 'product/product':
				if (empty($params['product_id'])) {
					return false;
				}

				$query = $this->db->query("SELECT p.product_id FROM " . DB_PREFIX . "product p
					INNER JOIN " . DB_PREFIX . "product_description pd ON (pd.product_id = p.product_id)
					INNER JOIN " . DB_PREFIX . "product_to_store p2s ON (p2s.product_id = p.product_id)
					WHERE p.product_id = '" . (int)$params['product_id'] . "'
					AND pd.language_id = '" . $language_id . "'
					AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'
					AND p.status = '1'
					AND p.date_available <= NOW()
					LIMIT 1");

				return (bool)$query->num_rows;

			case 'product/category':
				if (empty($params['path'])) {
					return false;
				}

				$path = explode('_', (string)$params['path']);
				$category_id = (int)end($path);

				$query = $this->db->query("SELECT c.category_id FROM " . DB_PREFIX . "category c
					INNER JOIN " . DB_PREFIX . "category_description cd ON (cd.category_id = c.category_id)
					INNER JOIN " . DB_PREFIX . "category_to_store c2s ON (c2s.category_id = c.category_id)
					WHERE c.category_id = '" . $category_id . "'
					AND cd.language_id = '" . $language_id . "'
					AND c2s.store_id = '" . (int)$this->config->get('config_store_id') . "'
					AND c.status = '1'
					LIMIT 1");

				return (bool)$query->num_rows;

			case 'product/manufacturer/info':
				if (empty($params['manufacturer_id'])) {
					return false;
				}

				$query = $this->db->query("SELECT m.manufacturer_id FROM " . DB_PREFIX . "manufacturer m
					INNER JOIN " . DB_PREFIX . "manufacturer_description md ON (md.manufacturer_id = m.manufacturer_id)
					INNER JOIN " . DB_PREFIX . "manufacturer_to_store m2s ON (m2s.manufacturer_id = m.manufacturer_id)
					WHERE m.manufacturer_id = '" . (int)$params['manufacturer_id'] . "'
					AND md.language_id = '" . $language_id . "'
					AND m2s.store_id = '" . (int)$this->config->get('config_store_id') . "'
					LIMIT 1");

				return (bool)$query->num_rows;

			case 'information/information':
				if (empty($params['information_id'])) {
					return false;
				}

				$query = $this->db->query("SELECT i.information_id FROM " . DB_PREFIX . "information i
					INNER JOIN " . DB_PREFIX . "information_description id ON (id.information_id = i.information_id)
					INNER JOIN " . DB_PREFIX . "information_to_store i2s ON (i2s.information_id = i.information_id)
					WHERE i.information_id = '" . (int)$params['information_id'] . "'
					AND id.language_id = '" . $language_id . "'
					AND i2s.store_id = '" . (int)$this->config->get('config_store_id') . "'
					AND i.status = '1'
					LIMIT 1");

				return (bool)$query->num_rows;

			case 'blog/article':
				if (empty($params['article_id'])) {
					return false;
				}

				$query = $this->db->query("SELECT a.article_id FROM " . DB_PREFIX . "article a
					INNER JOIN " . DB_PREFIX . "article_description ad ON (ad.article_id = a.article_id)
					INNER JOIN " . DB_PREFIX . "article_to_store a2s ON (a2s.article_id = a.article_id)
					WHERE a.article_id = '" . (int)$params['article_id'] . "'
					AND ad.language_id = '" . $language_id . "'
					AND a2s.store_id = '" . (int)$this->config->get('config_store_id') . "'
					AND a.status = '1'
					AND a.date_available <= NOW()
					LIMIT 1");

				return (bool)$query->num_rows;

			case 'blog/category':
				if (empty($params['blog_category_id'])) {
					return false;
				}

				$path = explode('_', (string)$params['blog_category_id']);
				$blog_category_id = (int)end($path);

				$query = $this->db->query("SELECT c.blog_category_id FROM " . DB_PREFIX . "blog_category c
					INNER JOIN " . DB_PREFIX . "blog_category_description cd ON (cd.blog_category_id = c.blog_category_id)
					INNER JOIN " . DB_PREFIX . "blog_category_to_store c2s ON (c2s.blog_category_id = c.blog_category_id)
					WHERE c.blog_category_id = '" . $blog_category_id . "'
					AND cd.language_id = '" . $language_id . "'
					AND c2s.store_id = '" . (int)$this->config->get('config_store_id') . "'
					AND c.status = '1'
					LIMIT 1");

				return (bool)$query->num_rows;
		}

		return true;
	}

	/**
	 * Same as getAlternateLinks(), but excludes languages where the entity does
	 * not exist.
	 */
	public function getAvailableAlternateLinks($route, $params = array(), $ssl = false) {
		if (!$this->isEnabled()) {
			return array();
		}

		$languages = $this->getLanguages();
		$default = $this->getDefaultLanguage($languages);
		$links = array();

		foreach ($languages as $language) {
			if (!$this->isRouteAvailableForLanguage($route, $params, (int)$language['language_id'])) {
				continue;
			}

			$url = $this->getUrlForLanguage($route, $params, (int)$language['language_id'], $ssl);

			if ($url !== '') {
				$hreflang = $this->normalizeHreflangCode($language['code']);
				$links[$hreflang] = $url;

				if ($default && $language['code'] === $default['code']) {
					$default_hreflang = $hreflang;
				}
			}
		}

		if ($default_hreflang !== '' && isset($links[$default_hreflang])) {
			$links['x-default'] = $links[$default_hreflang];
		}

		return $links;
	}

	private function normalizeHreflangCode($code) {
		$parts = preg_split('/[-_]+/', trim((string)$code));
		$normalized = array();

		foreach ($parts as $index => $part) {
			if ($part === '') {
				continue;
			}

			if ($index === 0) {
				$normalized[] = strtolower($part);
			} elseif (strlen($part) === 2 || strlen($part) === 3) {
				$normalized[] = strtoupper($part);
			} elseif (strlen($part) === 4) {
				$normalized[] = ucfirst(strtolower($part));
			} else {
				$normalized[] = strtolower($part);
			}
		}

		return implode('-', $normalized);
	}

	private function getLanguages() {
		if ($this->languages !== null) {
			return $this->languages;
		}

		$this->languages = array();

		if (!$this->db) {
			return $this->languages;
		}

		$query = $this->db->query("SELECT language_id, name, code, status FROM " . DB_PREFIX . "language WHERE status = '1' ORDER BY sort_order, name");

		foreach ($query->rows as $language) {
			$code = (string)$language['code'];

			$this->languages[$code] = array(
				'language_id' => (int)$language['language_id'],
				'name'        => (string)$language['name'],
				'code'        => $code
			);
		}

		return $this->languages;
	}

	private function getDefaultLanguage($languages) {
		$code = (string)$this->config->get('config_language');

		if ($code !== '' && isset($languages[$code])) {
			return $languages[$code];
		}

		return $languages ? reset($languages) : false;
	}

	private function getLanguageById($language_id, $languages) {
		foreach ($languages as $language) {
			if ((int)$language['language_id'] === (int)$language_id) {
				return $language;
			}
		}

		return false;
	}

	private function getPrefixMap($languages) {
		$map = array();

		foreach ($languages as $language) {
			$prefix = $this->getPrefixByCode($language['code']);

			if ($prefix !== '' && !isset($map[$prefix])) {
				$map[$prefix] = $language;
			}
		}

		return $map;
	}

	private function getPrefixByCode($code) {
		if ($this->prefixes === null) {
			$this->prefixes = array();

			if ($this->config->has('module_seo_language_prefix')) {
				$configured = $this->config->get('module_seo_language_prefix');
			} else {
				// Compatibility with settings saved by the first test build.
				$configured = $this->config->get('seo_language_prefix');
			}

			if (is_array($configured)) {
				foreach ($configured as $language_code => $prefix) {
					if (!is_scalar($prefix)) {
						continue;
					}

					$prefix = strtolower(trim((string)$prefix));
					$prefix = trim($prefix, '/');

					if ($prefix !== '') {
						$this->prefixes[(string)$language_code] = $prefix;
					}
				}
			}
		}

		if (isset($this->prefixes[$code])) {
			return $this->prefixes[$code];
		}

		/*
		 * Older saved settings allowed the current default language to have an
		 * empty prefix. If that language later becomes non-default, generate a
		 * deterministic collision-free reserve prefix automatically.
		 */
		$languages = $this->getLanguages();

		if (!isset($languages[$code])) {
			return '';
		}

		$language = $languages[$code];
		$candidates = array();

		$legacy = $this->db->query("SELECT keyword FROM " . DB_PREFIX . "seo_url
			WHERE query = 'common/home'
			AND store_id = '" . (int)$this->config->get('config_store_id') . "'
			AND language_id = '" . (int)$language['language_id'] . "'
			LIMIT 1");

		if ($legacy->num_rows) {
			$legacy_prefix = strtolower(trim((string)$legacy->row['keyword'], " /\\"));

			if ($legacy_prefix !== '') {
				$candidates[] = $legacy_prefix;
			}
		}

		$normalized_code = strtolower(str_replace('_', '-', (string)$language['code']));
		$parts = explode('-', $normalized_code);

		if (!empty($parts[0])) {
			$candidates[] = $parts[0];
		}

		if ($normalized_code !== '') {
			$candidates[] = $normalized_code;
		}

		$candidates[] = 'lang-' . (int)$language['language_id'];

		foreach (array_unique($candidates) as $candidate) {
			if (
				$candidate === ''
				|| in_array($candidate, $this->prefixes, true)
				|| !preg_match('/^[a-z0-9][a-z0-9_-]{0,31}$/', $candidate)
			) {
				continue;
			}

			$collision = $this->db->query("SELECT seo_url_id FROM " . DB_PREFIX . "seo_url
				WHERE store_id = '" . (int)$this->config->get('config_store_id') . "'
				AND keyword = '" . $this->db->escape($candidate) . "'
				AND query <> 'common/home'
				LIMIT 1");

			if (!$collision->num_rows) {
				$this->prefixes[$code] = $candidate;
				return $candidate;
			}
		}

		return '';
	}

	private function applyLanguage($language) {
		if (!$language || empty($language['code']) || empty($language['language_id'])) {
			return;
		}

		$code = (string)$language['code'];
		$language_id = (int)$language['language_id'];

		$this->session->data['language'] = $code;
		$this->config->set('config_language_id', $language_id);

		$language_object = new Language($code);
		$language_object->load($code);
		$this->registry->set('language', $language_object);

		if (PHP_SAPI !== 'cli' && !headers_sent()) {
			setcookie('language', $code, time() + 60 * 60 * 24 * 30, '/');
		}
	}

	private function getPreferredLanguage($languages) {
		$code = '';

		if (isset($this->session->data['language']) && is_scalar($this->session->data['language'])) {
			$code = (string)$this->session->data['language'];
		}

		if (
			($code === '' || !isset($languages[$code]))
			&& isset($this->request->cookie['language'])
			&& is_scalar($this->request->cookie['language'])
		) {
			$code = (string)$this->request->cookie['language'];
		}

		return isset($languages[$code]) ? $languages[$code] : false;
	}

	private function redirectToLanguageRoot($prefix) {
		$base = $this->getBaseUrlForScheme($this->isSecureRequest() ? 'https' : 'http');
		$base_info = parse_url($base);

		if (!is_array($base_info) || empty($base_info['scheme']) || empty($base_info['host'])) {
			return;
		}

		$target = $base_info['scheme'] . '://' . $base_info['host'];

		if (isset($base_info['port'])) {
			$target .= ':' . (int)$base_info['port'];
		}

		$target .= $this->normalizeBasePath(isset($base_info['path']) ? $base_info['path'] : '/');
		$target .= rawurlencode((string)$prefix) . '/';

		$this->response->redirect($target, 302);
	}

	private function isSafeRedirectMethod() {
		$method = isset($this->request->server['REQUEST_METHOD'])
			? strtoupper((string)$this->request->server['REQUEST_METHOD'])
			: 'GET';

		return $method === 'GET' || $method === 'HEAD';
	}

	private function isStoreRootRequest() {
		if (empty($this->request->server['REQUEST_URI'])) {
			return false;
		}

		$request = parse_url((string)$this->request->server['REQUEST_URI']);
		$path = is_array($request) && isset($request['path']) ? (string)$request['path'] : '/';
		$base = $this->getBaseUrlForScheme($this->isSecureRequest() ? 'https' : 'http');
		$base_info = parse_url($base);
		$base_path = $this->normalizeBasePath(is_array($base_info) && isset($base_info['path']) ? $base_info['path'] : '/');

		return $path === $base_path
			|| $path === rtrim($base_path, '/')
			|| $path === $base_path . 'index.php';
	}

	private function redirectWithoutDefaultPrefix($prefix) {
		if (empty($this->request->server['REQUEST_URI'])) {
			return;
		}

		$request_uri = (string)$this->request->server['REQUEST_URI'];
		$request = parse_url($request_uri);

		if (!is_array($request)) {
			return;
		}

		$path = isset($request['path']) ? (string)$request['path'] : '/';
		$base = $this->getBaseUrlForScheme($this->isSecureRequest() ? 'https' : 'http');
		$base_info = parse_url($base);

		if (!is_array($base_info) || empty($base_info['host'])) {
			return;
		}

		$base_path = $this->normalizeBasePath(isset($base_info['path']) ? $base_info['path'] : '/');
		$relative = '';

		if ($base_path === '/') {
			$relative = ltrim($path, '/');
		} elseif (strpos($path, $base_path) === 0) {
			$relative = substr($path, strlen($base_path));
		} else {
			return;
		}

		$had_trailing_slash = $path !== '/' && substr($path, -1) === '/';
		$parts = $relative === '' ? array() : explode('/', trim($relative, '/'));

		if (
			!$parts
			|| strtolower(rawurldecode((string)$parts[0])) !== strtolower((string)$prefix)
		) {
			return;
		}

		array_shift($parts);

		$new_path = $base_path;

		if ($parts) {
			$new_path .= implode('/', $parts);

			if ($had_trailing_slash) {
				$new_path .= '/';
			}
		}

		$target = $base_info['scheme'] . '://' . $base_info['host'];

		if (isset($base_info['port'])) {
			$target .= ':' . (int)$base_info['port'];
		}

		$target .= $new_path;

		if (isset($request['query']) && $request['query'] !== '') {
			$target .= '?' . $request['query'];
		}

		$this->response->redirect($target, 301);
	}

	private function getBaseUrlForScheme($scheme) {
		if (strtolower((string)$scheme) === 'https' && $this->config->get('config_ssl')) {
			return (string)$this->config->get('config_ssl');
		}

		return (string)$this->config->get('config_url');
	}

	private function normalizeBasePath($path) {
		$path = '/' . trim((string)$path, '/');

		if ($path === '/') {
			return '/';
		}

		return $path . '/';
	}

	private function isSecureRequest() {
		if (!empty($this->request->server['HTTPS'])) {
			$https = strtolower((string)$this->request->server['HTTPS']);

			if ($https === 'on' || $https === '1') {
				return true;
			}
		}

		if (!empty($this->request->server['HTTP_X_FORWARDED_PROTO'])) {
			$parts = explode(',', (string)$this->request->server['HTTP_X_FORWARDED_PROTO']);

			return strtolower(trim($parts[0])) === 'https';
		}

		return false;
	}
}
