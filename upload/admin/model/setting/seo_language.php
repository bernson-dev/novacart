<?php
class ModelSettingSeoLanguage extends Model {
	public function getSettings($store_id = 0) {
		$store_id = (int)$store_id;
		$status = null;
		$prefixes = null;

		// New native store settings.
		$config = $this->getSettingRows('config', $store_id);

		if (array_key_exists('config_seo_language', $config)) {
			$status = (int)$config['config_seo_language'];
		}

		if (isset($config['config_seo_language_prefix']) && is_array($config['config_seo_language_prefix'])) {
			$prefixes = $config['config_seo_language_prefix'];
		}

		// Compatibility with releases where the feature was an extension.
		if ($status === null || $prefixes === null) {
			$module = $this->getSettingRows('module_seo_language', $store_id);

			if ($status === null && array_key_exists('module_seo_language_status', $module)) {
				$status = (int)$module['module_seo_language_status'];
			}

			if ($prefixes === null && isset($module['module_seo_language_prefix']) && is_array($module['module_seo_language_prefix'])) {
				$prefixes = $module['module_seo_language_prefix'];
			}
		}

		// Compatibility with the first test build.
		if ($status === null || $prefixes === null) {
			$legacy = $this->getSettingRows('seo_language', $store_id);

			if ($status === null && array_key_exists('seo_language_status', $legacy)) {
				$status = (int)$legacy['seo_language_status'];
			}

			if ($prefixes === null && isset($legacy['seo_language_prefix']) && is_array($legacy['seo_language_prefix'])) {
				$prefixes = $legacy['seo_language_prefix'];
			}
		}

		/*
		 * Additional stores inherit missing settings from store 0, matching
		 * catalog startup settings loading (store 0 first, store override last).
		 */
		if ($store_id !== 0 && ($status === null || $prefixes === null)) {
			$default = $this->getSettings(0);

			if ($status === null) {
				$status = (int)$default['status'];
			}

			if ($prefixes === null) {
				$prefixes = $default['prefix'];
			}
		}

		return array(
			'status' => $status === null ? 0 : (int)$status,
			'prefix' => $this->normalizePrefixes(is_array($prefixes) ? $prefixes : array())
		);
	}

	public function normalizePrefixes($prefixes) {
		$result = array();

		if (!is_array($prefixes)) {
			return $result;
		}

		foreach ($prefixes as $code => $prefix) {
			if (!is_scalar($prefix)) {
				continue;
			}

			$prefix = strtolower(trim((string)$prefix, " /\\"));

			if ($prefix !== '') {
				$result[(string)$code] = $prefix;
			}
		}

		return $result;
	}

	public function getLanguageRows($store_id, $languages, $default_language, $prefixes) {
		$rows = array();
		$prefixes = $this->normalizePrefixes($prefixes);

		foreach ($languages as $language) {
			if (empty($language['status'])) {
				continue;
			}

			$code = (string)$language['code'];
			$prefix = isset($prefixes[$code]) ? $prefixes[$code] : '';

			if ($prefix === '' && $store_id !== null) {
				$legacy = $this->db->query("SELECT keyword FROM " . DB_PREFIX . "seo_url
					WHERE query = 'common/home'
					AND store_id = '" . (int)$store_id . "'
					AND language_id = '" . (int)$language['language_id'] . "'
					LIMIT 1");

				if ($legacy->num_rows && trim((string)$legacy->row['keyword']) !== '') {
					$prefix = strtolower(trim((string)$legacy->row['keyword'], " /\\"));
				}
			}

			if ($prefix === '') {
				$parts = preg_split('/[-_]/', strtolower($code));
				$prefix = !empty($parts[0]) ? $parts[0] : '';
			}

			$rows[] = array(
				'language_id' => (int)$language['language_id'],
				'name' => (string)$language['name'],
				'code' => $code,
				'prefix' => $prefix,
				'is_default' => $code === (string)$default_language
			);
		}

		return $rows;
	}

	public function validate($store_id, $status, $prefixes, $languages) {
		$errors = array(
			'warning' => '',
			'prefix' => array()
		);

		$status = (bool)$status;
		$prefixes = $this->normalizePrefixes($prefixes);

		if (!$status) {
			if ($store_id !== null && $this->hasSharedKeywords((int)$store_id)) {
				$errors['warning'] = 'disable_shared_keywords';
			}

			return $errors;
		}

		$used = array();

		foreach ($languages as $language) {
			if (empty($language['status'])) {
				continue;
			}

			$code = (string)$language['code'];
			$prefix = isset($prefixes[$code]) ? $prefixes[$code] : '';

			if ($prefix === '') {
				$errors['prefix'][$code] = 'prefix_required';
				continue;
			}

			if (!preg_match('/^[a-z0-9][a-z0-9_-]{0,31}$/', $prefix)) {
				$errors['prefix'][$code] = 'prefix_format';
				continue;
			}

			if (isset($used[$prefix]) && $used[$prefix] !== $code) {
				$errors['prefix'][$code] = 'prefix_duplicate';
				continue;
			}

			$used[$prefix] = $code;

			if ($store_id !== null) {
				$collision = $this->db->query("SELECT seo_url_id FROM " . DB_PREFIX . "seo_url
					WHERE store_id = '" . (int)$store_id . "'
					AND keyword = '" . $this->db->escape($prefix) . "'
					AND query <> 'common/home'
					LIMIT 1");

				if ($collision->num_rows) {
					$errors['prefix'][$code] = 'prefix_collision';
				}
			}
		}

		return $errors;
	}

	private function hasSharedKeywords($store_id) {
		$query = $this->db->query("SELECT keyword
			FROM " . DB_PREFIX . "seo_url
			WHERE store_id = '" . (int)$store_id . "'
			AND keyword <> ''
			GROUP BY keyword
			HAVING COUNT(DISTINCT language_id) > 1
			LIMIT 1");

		return (bool)$query->num_rows;
	}

	private function getSettingRows($code, $store_id) {
		$data = array();

		$query = $this->db->query("SELECT `key`, `value`, `serialized`
			FROM `" . DB_PREFIX . "setting`
			WHERE `store_id` = '" . (int)$store_id . "'
			AND `code` = '" . $this->db->escape($code) . "'");

		foreach ($query->rows as $row) {
			if (!empty($row['serialized'])) {
				$data[$row['key']] = json_decode($row['value'], true);
			} else {
				$data[$row['key']] = $row['value'];
			}
		}

		return $data;
	}
}
