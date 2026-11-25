<?php
namespace Novacart;

/**
 * Manual redirects for unresolved public SEO paths.
 * Paths are origin-relative (including any store subdirectory/language prefix).
 * Query strings are not part of a rule's identity.
 */
class SeoRedirect {
	private $db;
	private $available;

	public function __construct($db) {
		$this->db = $db;
	}

	public function isAvailable() {
		if ($this->available === null) {
			$result = $this->db->query("SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '" . $this->db->escape(DB_PREFIX . 'seo_redirect') . "' LIMIT 1");
			$this->available = (bool)$result->num_rows;
		}
		return $this->available;
	}

	/**
	 * Canonicalize percent encoding and trailing slash for matching only.
	 * Reject ambiguous paths, external URLs, header injection and traversal.
	 */
	public static function normalizePath($path) {
		if (!is_string($path) || $path === '' || strlen($path) > 2048 || $path[0] !== '/' || strpos($path, '//') !== false || preg_match('/[\x00-\x20\x7f\\\\?#]/', $path) || preg_match('/%(?![0-9a-fA-F]{2})/', $path)) {
			return '';
		}

		$parts = explode('/', trim($path, '/'));
		$normalized = array();
		foreach ($parts as $part) {
			if ($part === '') {
				continue;
			}
			$decoded = rawurldecode($part);
			if ($decoded === '.' || $decoded === '..' || preg_match('/[\x00-\x20\x7f\\\\\/?#%]/', $decoded) || !preg_match('//u', $decoded)) {
				return '';
			}
			$normalized[] = rawurlencode($decoded);
		}
		$result = '/' . implode('/', $normalized);
		return strlen($result) <= 2048 ? $result : '';
	}

	public static function inStore($path, $base_path) {
		$base = self::normalizePath($base_path);
		return $base !== '' && ($base === '/' || $path === $base || strpos($path, $base . '/') === 0);
	}

	/**
	 * A target must not itself be a manual source. Check all language
	 * scopes in this store because the target may change the active language.
	 * This deliberately refuses chains rather than altering 301/302 semantics.
	 */
	public function hasEnabledSource($path, $store_id) {
		$path = self::normalizePath($path);
		if ($path === '' || !$this->isAvailable()) {
			return false;
		}
		$result = $this->db->query("SELECT seo_redirect_id FROM " . DB_PREFIX . "seo_redirect WHERE store_id = '" . (int)$store_id . "' AND source_hash = '" . hash('sha256', $path) . "' AND status = '1' LIMIT 1");
		return (bool)$result->num_rows;
	}

	public function resolve($source, $store_id, $language_id, $base_path = '/') {
		$source = self::normalizePath($source);
		if ($source === '' || $source === '/' || !self::inStore($source, $base_path) || !$this->isAvailable()) {
			return array();
		}

		// Exact language wins over the optional all-languages rule (id 0).
		$result = $this->db->query("SELECT source_path, target_path, http_code FROM " . DB_PREFIX . "seo_redirect WHERE store_id = '" . (int)$store_id . "' AND language_id IN (0," . (int)$language_id . ") AND source_hash = '" . hash('sha256', $source) . "' AND status = '1' ORDER BY language_id DESC LIMIT 1");
		if (!$result->num_rows || self::normalizePath($result->row['source_path']) !== $source) {
			return array();
		}

		$code = (int)$result->row['http_code'];
		if ($code === 410) {
			return array('code' => 410, 'target' => '');
		}
		$target = self::normalizePath($result->row['target_path']);
		if (!in_array($code, array(301, 302), true) || $target === '' || $source === $target || !self::inStore($target, $base_path) || $this->hasEnabledSource($target, $store_id)) {
			return array();
		}

		// Preserve the configured trailing slash, which may be canonical.
		return array('code' => $code, 'target' => $target . ($target !== '/' && substr($result->row['target_path'], -1) === '/' ? '/' : ''));
	}
}
