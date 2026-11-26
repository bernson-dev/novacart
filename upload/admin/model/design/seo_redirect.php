<?php
class ModelDesignSeoRedirect extends Model {
	private function service() {
		require_once DIR_SYSTEM . 'library/novacart/seo_redirect.php';
		return new \Novacart\SeoRedirect($this->db);
	}

	public function isAvailable() {
		return $this->service()->isAvailable();
	}

	private function where($search) {
		if ($search === '') {
			return '';
		}
		$search = $this->db->escape($search);
		return " WHERE (r.source_path LIKE '%" . $search . "%' OR r.target_path LIKE '%" . $search . "%')";
	}

	public function getRules($search, $start, $limit) {
		return $this->db->query("SELECT r.*, s.name AS store, l.name AS language FROM " . DB_PREFIX . "seo_redirect r LEFT JOIN " . DB_PREFIX . "store s ON s.store_id = r.store_id LEFT JOIN " . DB_PREFIX . "language l ON l.language_id = r.language_id" . $this->where($search) . " ORDER BY r.seo_redirect_id DESC LIMIT " . max(0, (int)$start) . "," . max(1, (int)$limit))->rows;
	}

	// Keyset pagination avoids loading the entire redirect table at once.
	public function exportRules() {
		$last = 0;
		$max = (int)$this->db->query("SELECT COALESCE(MAX(seo_redirect_id),0) AS max_id FROM " . DB_PREFIX . "seo_redirect")->row['max_id'];
		do {
			$rows = $this->db->query("SELECT seo_redirect_id, source_path, target_path, store_id, language_id, http_code, status FROM " . DB_PREFIX . "seo_redirect WHERE seo_redirect_id > " . $last . " AND seo_redirect_id <= " . $max . " ORDER BY seo_redirect_id ASC LIMIT 500")->rows;
			foreach ($rows as $row) {
				$last = (int)$row['seo_redirect_id'];
				yield $row;
			}
		} while (count($rows) === 500);
	}

	public function getTotalRules($search) {
		return (int)$this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "seo_redirect r" . $this->where($search))->row['total'];
	}

	public function getRule($id) {
		return $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_redirect WHERE seo_redirect_id = '" . (int)$id . "'")->row;
	}

	public function deleteRule($id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "seo_redirect WHERE seo_redirect_id = '" . (int)$id . "'");
	}

	// Conservative check: reject paths entirely composed of current SEO aliases.
	// Runtime routing remains authoritative, including third-party routes.
	private function isActivePath($path, $store, $base, $settings, $languages) {
		$base = \Novacart\SeoRedirect::normalizePath($base);
		$relative = $base === '/' ? ltrim($path, '/') : ltrim(substr($path, strlen($base)), '/');
		$parts = explode('/', rawurldecode($relative));
		$prefixes = array();
		foreach ($languages as $language) {
			$code = strtolower(str_replace('_', '-', $language['code']));
			$prefixes[] = explode('-', $code)[0];
			if (!empty($settings['config_seo_language_prefix'][$code])) {
				$prefixes[] = strtolower(trim($settings['config_seo_language_prefix'][$code], '/ '));
			}
		}
		if (in_array(strtolower($parts[0]), $prefixes, true)) {
			array_shift($parts);
		}
		if (!$parts || $parts === array('')) {
			return true;
		}
		$postfix = (string)($settings['config_page_postfix'] ?? '');
		if ($postfix !== '' && substr(end($parts), -strlen($postfix)) === $postfix) {
			$parts[count($parts) - 1] = substr(end($parts), 0, -strlen($postfix));
		}
		foreach ($parts as $part) {
			$result = $this->db->query("SELECT seo_url_id FROM " . DB_PREFIX . "seo_url WHERE store_id = '" . (int)$store . "' AND keyword = '" . $this->db->escape($part) . "' LIMIT 1");
			if (!$result->num_rows) {
				return false;
			}
		}
		return true;
	}

	public function saveRule(array $data, $base, array $settings, array $languages, $preview = false) {
		$this->service();
		$id = (int)$data['seo_redirect_id'];
		$store = (int)$data['store_id'];
		$language = (int)$data['language_id'];
		$code = (int)$data['http_code'];
		$status = (int)$data['status'];
		$source = \Novacart\SeoRedirect::normalizePath(trim($data['source_path']));
		$target = $code === 410 ? '' : \Novacart\SeoRedirect::normalizePath(trim($data['target_path']));
		if (!in_array((string)$data['http_code'], array('301', '302', '410'), true) || !in_array((string)$data['status'], array('0', '1'), true) || $id < 0 || ($id && !$this->getRule($id))) {
			return 'error_redirect_rule';
		}
		if ($source === '' || $source === '/' || !\Novacart\SeoRedirect::inStore($source, $base) || ($code !== 410 && ($target === '' || !\Novacart\SeoRedirect::inStore($target, $base)))) {
			return 'error_redirect_path';
		}
		if ($source === $target || $this->isActivePath($source, $store, $base, $settings, $languages)) {
			return 'error_redirect_conflict';
		}
		$hash = hash('sha256', $source);
		$exclude = " AND seo_redirect_id <> '" . $id . "'";
		$scope = " WHERE store_id = '" . $store . "'" . $exclude;
		$duplicate = $this->db->query("SELECT seo_redirect_id FROM " . DB_PREFIX . "seo_redirect" . $scope . " AND language_id = '" . $language . "' AND source_hash = '" . $hash . "' LIMIT 1");
		if ($duplicate->num_rows) {
			return 'error_redirect_duplicate';
		}
		if ($target !== '' && $target !== '/' && substr(trim($data['target_path']), -1) === '/') {
			$target .= '/';
		}
		if ($status) {
			// Both directions: a new source must not be another rule's target,
			// and a new target must not be another rule's source.
			$condition = "target_path IN ('" . $this->db->escape($source) . "','" . $this->db->escape($source . '/') . "')";
			if ($target !== '') {
				$condition .= " OR source_hash = '" . hash('sha256', \Novacart\SeoRedirect::normalizePath($target)) . "'";
			}
			$chain = $this->db->query("SELECT seo_redirect_id FROM " . DB_PREFIX . "seo_redirect" . $scope . " AND status = '1' AND (" . $condition . ") LIMIT 1");
			if ($chain->num_rows) {
				return 'error_redirect_chain';
			}
		}
		if ($preview) { return ''; }
		$fields = "store_id = '" . $store . "', language_id = '" . $language . "', source_path = '" . $this->db->escape($source) . "', source_hash = '" . $hash . "', target_path = '" . $this->db->escape($target) . "', http_code = '" . $code . "', status = '" . $status . "', date_modified = NOW()";
		if ($id) {
			$this->db->query("UPDATE " . DB_PREFIX . "seo_redirect SET " . $fields . " WHERE seo_redirect_id = '" . $id . "'");
		} else {
			$this->db->query("INSERT INTO " . DB_PREFIX . "seo_redirect SET " . $fields);
		}
		return '';
	}
}
