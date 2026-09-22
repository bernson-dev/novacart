<?php
/**
 * Language-aware URL generator.
 *
 * The catalog default language (config_language) always owns the store root.
 * Other languages use their common/home SEO keyword. No language code or
 * language_id is hard-coded here.
 */
class LanguageUrl {
	private $registry;
	private $config;
	private $request;
	private $url;
	private $db;

	public function __construct($registry) {
		$this->registry = $registry;
		$this->config = $registry->get('config');
		$this->request = $registry->get('request');
		$this->url = $registry->get('url');
		$this->db = $registry->get('db');
	}

	/**
	 * Generate a URL using the requested language context without changing
	 * session, cookies or the active Language object.
	 */
	public function link($route, $args = '', $language_id = 0, $secure = false) {
		$route = (string)$route;
		$language_id = (int)$language_id;
		$current_language_id = (int)$this->config->get('config_language_id');

		if ($language_id < 1 || $language_id === $current_language_id) {
			return $this->url->link($route, $args, $secure);
		}

		$this->config->set('config_language_id', $language_id);

		try {
			return $this->url->link($route, $args, $secure);
		} finally {
			$this->config->set('config_language_id', $current_language_id);
		}
	}

	public function current($language_id, $secure = false) {
		$target = $this->getCurrentTarget();

		return $this->link(
			$target['route'],
			$target['args'],
			$language_id,
			$secure
		);
	}

	public function getCurrentTarget() {
		$route = 'common/home';
		$args = array();

		if (isset($this->request->get['route']) && is_scalar($this->request->get['route'])) {
			$candidate = (string)$this->request->get['route'];

			if (preg_match('/^[a-zA-Z0-9_\/]+$/', $candidate)) {
				$route = $candidate;
			}
		}

		foreach ($this->request->get as $key => $value) {
			if ($key === '_route_' || $key === 'route') {
				continue;
			}

			$args[$key] = $value;
		}

		return array(
			'route' => $route,
			'args'  => $args
		);
	}

	/**
	 * Return the enabled catalog default language.
	 */
	public function getDefaultLanguage() {
		$code = (string)$this->config->get('config_language');

		if ($code === '') {
			return false;
		}

		$query = $this->db->query("SELECT language_id, code FROM " . DB_PREFIX . "language
			WHERE code = '" . $this->db->escape($code) . "'
			AND status = '1'
			LIMIT 1");

		return $query->num_rows ? $query->row : false;
	}

	public function getDefaultLanguageId() {
		$language = $this->getDefaultLanguage();

		return $language ? (int)$language['language_id'] : 0;
	}

	public function isDefaultLanguage($language_id) {
		return (int)$language_id > 0
			&& (int)$language_id === $this->getDefaultLanguageId();
	}

	/**
	 * Effective SEO keyword for a language home page.
	 *
	 * The default catalog language always maps to an empty keyword (store root)
	 * even if a legacy common/home keyword remains in the database.
	 */
	public function getHomeKeyword($language_id) {
		$language_id = (int)$language_id;

		if ($language_id < 1) {
			return null;
		}

		if ($this->isDefaultLanguage($language_id)) {
			return '';
		}

		$query = $this->db->query("SELECT keyword FROM " . DB_PREFIX . "seo_url
			WHERE `query` = 'common/home'
			AND store_id = '" . (int)$this->config->get('config_store_id') . "'
			AND language_id = '" . $language_id . "'
			LIMIT 1");

		if ($query->num_rows && trim((string)$query->row['keyword']) !== '') {
			return trim((string)$query->row['keyword'], '/');
		}

		// A former default language may have an empty common/home keyword.
		// Keep it reachable after the default language changes without requiring
		// a database migration. Configured SEO keywords always take precedence.
		return $this->getFallbackHomeKeyword($language_id);
	}

	/**
	 * Resolve an implicit home alias only when no explicit seo_url keyword
	 * matched it. This is primarily for a former default language whose stored
	 * common/home keyword is empty.
	 */
	public function getLanguageByFallbackHomeKeyword($keyword) {
		$keyword = strtolower(trim((string)$keyword, '/'));

		if ($keyword === '') {
			return false;
		}

		$query = $this->db->query("SELECT language_id, code FROM " . DB_PREFIX . "language
			WHERE status = '1'
			ORDER BY sort_order, name");

		$default_language_id = $this->getDefaultLanguageId();

		foreach ($query->rows as $language) {
			$language_id = (int)$language['language_id'];

			if ($language_id === $default_language_id) {
				continue;
			}

			$home = $this->db->query("SELECT keyword FROM " . DB_PREFIX . "seo_url
				WHERE `query` = 'common/home'
				AND store_id = '" . (int)$this->config->get('config_store_id') . "'
				AND language_id = '" . $language_id . "'
				LIMIT 1");

			if ($home->num_rows && trim((string)$home->row['keyword']) !== '') {
				continue;
			}

			if ($this->getFallbackHomeKeyword($language_id) === $keyword) {
				return $language;
			}
		}

		return false;
	}

	private function getFallbackHomeKeyword($language_id) {
		$query = $this->db->query("SELECT code FROM " . DB_PREFIX . "language
			WHERE language_id = '" . (int)$language_id . "'
			AND status = '1'
			LIMIT 1");

		if (!$query->num_rows) {
			return null;
		}

		$code = strtolower(str_replace('_', '-', (string)$query->row['code']));
		$parts = explode('-', $code);
		$candidates = array();

		if (!empty($parts[0])) {
			$candidates[] = $parts[0];
		}

		if ($code !== '' && !in_array($code, $candidates, true)) {
			$candidates[] = $code;
		}

		$candidates[] = 'lang-' . (int)$language_id;

		foreach ($candidates as $candidate) {
			$collision = $this->db->query("SELECT seo_url_id FROM " . DB_PREFIX . "seo_url
				WHERE keyword = '" . $this->db->escape($candidate) . "'
				AND store_id = '" . (int)$this->config->get('config_store_id') . "'
				LIMIT 1");

			if (!$collision->num_rows) {
				return $candidate;
			}
		}

		return 'lang-' . (int)$language_id;
	}
}
