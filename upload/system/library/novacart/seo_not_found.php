<?php
namespace Novacart;

require_once __DIR__ . '/seo_redirect.php';

/** Aggregated public SEO misses. No query strings, IPs, cookies or referrers. */
class SeoNotFound {
	private $db;
	private $available;

	public function __construct($db) {
		$this->db = $db;
	}

	public function isAvailable() {
		if ($this->available === null) {
			$result = $this->db->query("SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '" . $this->db->escape(DB_PREFIX . 'seo_not_found') . "' LIMIT 1");
			$this->available = (bool)$result->num_rows;
		}
		return $this->available;
	}

	public static function retentionDays($value) {
		return is_scalar($value) && ctype_digit((string)$value) && (int)$value >= 1 && (int)$value <= 3650 ? (int)$value : 90;
	}

	public static function loggingEnabled($value) {
		return $value === null || $value === '' || (string)$value === '1';
	}

	public function prune($days) {
		if (!$this->isAvailable()) {
			return 0;
		}
		$days = self::retentionDays($days);
		$this->db->query("DELETE FROM " . DB_PREFIX . "seo_not_found WHERE date_last < DATE_SUB(NOW(), INTERVAL " . $days . " DAY)");
		return (int)$this->db->countAffected();
	}

	public static function requestPath($uri, $base = '/') {
		if (!is_string($uri) || $uri === '' || $uri[0] !== '/' || strpos($uri, '//') === 0) {
			return '';
		}
		$path = SeoRedirect::normalizePath(parse_url(html_entity_decode($uri, ENT_QUOTES, 'UTF-8'), PHP_URL_PATH));
		if ($path === '' || $path === '/' || !SeoRedirect::inStore($path, $base)) {
			return '';
		}
		// Exclude common static assets, internal paths and direct controller URLs.
		$base = SeoRedirect::normalizePath($base);
		$relative = ltrim($base === '/' ? $path : substr($path, strlen($base)), '/');
		if ($relative === '' || preg_match('~^(?:index\.php|admin|system|image|catalog|install|api)(?:/|$)~i', $relative)
			|| preg_match('~(?:^|/)\.|[.](?:css|js|map|png|jpe?g|gif|webp|svgz?|ico|woff2?|ttf|pdf|zip|php|txt|xml)$~i', $relative)) {
			return '';
		}
		return $path;
	}

	public function record($path, $store, $language) {
		if ($path === '' || !$this->isAvailable()) {
			return;
		}
		$this->db->query("INSERT INTO " . DB_PREFIX . "seo_not_found SET store_id = '" . (int)$store . "', language_id = '" . (int)$language . "', path = '" . $this->db->escape($path) . "', path_hash = '" . hash('sha256', $path) . "', hits = 1, date_first = NOW(), date_last = NOW() ON DUPLICATE KEY UPDATE hits = hits + 1, date_last = NOW()");
	}

	public static function recordRequest($registry) {
		if ($registry->has('seo_not_found_recorded') || $registry->has('seo_redirect_gone')) {
			return;
		}
		$request = $registry->get('request');
		$config = $registry->get('config');
		if (!self::loggingEnabled($config->get('seo_not_found_status')) || !$config->get('config_seo_url') || !isset($request->get['_route_']) || !in_array(strtoupper((string)($request->server['REQUEST_METHOD'] ?? 'GET')), array('GET', 'HEAD'), true)
			|| !empty($request->post) || strtolower((string)($request->server['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest') {
			return;
		}
		$base = parse_url((string)$config->get('config_url'), PHP_URL_PATH) ?: '/';
		$path = self::requestPath($request->server['REQUEST_URI'] ?? '', $base);
		if ($path === '') {
			return;
		}
		$registry->set('seo_not_found_recorded', true);
		try {
			$service = new self($registry->get('db'));
			$service->record($path, (int)$config->get('config_store_id'), (int)$config->get('config_language_id'));
		} catch (\Throwable $e) {
			// Logging must never replace a valid 404 with a database failure.
			if ($registry->has('log')) {
				$registry->get('log')->write('SEO 404 journal write failed.');
			}
		}
	}
}
