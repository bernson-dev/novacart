<?php
class ModelExtensionDashboardHealth extends Model {
	public function getReport($low_stock_limit = 5, $log_warning_mb = 10) {
		$low_stock_limit = max(0, (int)$low_stock_limit);
		$log_warning_mb = max(1, (int)$log_warning_mb);

		return array(
			'system'  => $this->getSystemChecks($log_warning_mb),
			'catalog' => $this->getCatalogChecks($low_stock_limit),
			'seo'     => $this->getSeoChecks()
		);
	}

	private function getSystemChecks($log_warning_mb) {
		$checks = array();

		$checks[] = array(
			'code'  => 'php_version',
			'state' => version_compare(PHP_VERSION, '7.3.0', '>=') ? 'ok' : 'error',
			'value' => PHP_VERSION
		);

		$required = array('curl', 'openssl', 'json', 'zlib', 'zip', 'dom');
		$missing = array();

		foreach ($required as $extension) {
			if (!extension_loaded($extension)) {
				$missing[] = $extension;
			}
		}

		if (!extension_loaded('gd') && !extension_loaded('imagick')) {
			$missing[] = 'gd/imagick';
		}

		$checks[] = array(
			'code'  => 'php_extensions',
			'state' => $missing ? 'error' : 'ok',
			'value' => $missing ? implode(', ', $missing) : ''
		);

		$web_root = realpath(DIR_APPLICATION . '../');
		$storage = realpath(DIR_STORAGE);
		$storage_public = false;

		if ($web_root && $storage) {
			$web_root = rtrim(str_replace('\\', '/', $web_root), '/') . '/';
			$storage_path = rtrim(str_replace('\\', '/', $storage), '/') . '/';
			$storage_public = strpos($storage_path, $web_root) === 0;
		}

		$checks[] = array(
			'code'  => 'storage_location',
			'state' => !$storage ? 'error' : ($storage_public ? 'warning' : 'ok'),
			'value' => str_replace('\\', '/', (string)DIR_STORAGE)
		);

		$directories = array();

		foreach (array('DIR_CACHE', 'DIR_LOGS', 'DIR_UPLOAD', 'DIR_MODIFICATION', 'DIR_SESSION') as $constant) {
			if (!defined($constant)) {
				continue;
			}

			$path = constant($constant);

			if (!is_dir($path) || !is_writable($path)) {
				$directories[] = $constant;
			}
		}

		$checks[] = array(
			'code'  => 'writable_directories',
			'state' => $directories ? 'error' : 'ok',
			'value' => $directories ? implode(', ', $directories) : ''
		);

		$install_dir = rtrim(dirname(DIR_APPLICATION), '/\\') . DIRECTORY_SEPARATOR . 'install';
		$checks[] = array(
			'code'  => 'install_directory',
			'state' => is_dir($install_dir) ? 'warning' : 'ok',
			'value' => is_dir($install_dir) ? str_replace('\\', '/', $install_dir) : ''
		);

		$log_size = 0;
		$filename = (string)$this->config->get('config_error_filename');

		if ($filename !== '' && defined('DIR_LOGS')) {
			$log_file = rtrim(DIR_LOGS, '/\\') . DIRECTORY_SEPARATOR . basename($filename);

			if (is_file($log_file)) {
				$size = @filesize($log_file);
				$log_size = $size !== false ? (int)$size : 0;
			}
		}

		$warning_bytes = $log_warning_mb * 1024 * 1024;
		$checks[] = array(
			'code'  => 'error_log',
			'state' => $log_size > $warning_bytes ? 'warning' : 'ok',
			'value' => $this->formatBytes($log_size)
		);

		return $checks;
	}

	private function getCatalogChecks($low_stock_limit) {
		$checks = array();

		try {
			$query = $this->db->query(
				"SELECT " .
				"SUM(CASE WHEN quantity <= 0 THEN 1 ELSE 0 END) AS out_of_stock, " .
				"SUM(CASE WHEN quantity > 0 AND quantity <= '" . (int)$low_stock_limit . "' THEN 1 ELSE 0 END) AS low_stock " .
				"FROM " . DB_PREFIX . "product WHERE status = '1'"
			);

			$out_of_stock = isset($query->row['out_of_stock']) ? (int)$query->row['out_of_stock'] : 0;
			$low_stock = isset($query->row['low_stock']) ? (int)$query->row['low_stock'] : 0;

			$checks[] = array(
				'code'  => 'out_of_stock',
				'state' => $out_of_stock > 0 ? 'warning' : 'ok',
				'value' => $out_of_stock
			);

			$checks[] = array(
				'code'  => 'low_stock',
				'state' => $low_stock > 0 ? 'warning' : 'ok',
				'value' => $low_stock
			);
		} catch (Throwable $e) {
			$checks[] = array(
				'code'  => 'catalog_query',
				'state' => 'error',
				'value' => ''
			);
		}

		return $checks;
	}

	private function getSeoChecks() {
		$checks = array();
		$language_id = (int)$this->config->get('config_language_id');
		$store_id = (int)$this->config->get('config_store_id');

		try {
			$checks[] = $this->seoCheck(
				'seo_product',
				"SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product p " .
				"WHERE p.status = '1' AND NOT EXISTS (" .
				"SELECT 1 FROM " . DB_PREFIX . "seo_url su " .
				"WHERE su.store_id = '" . $store_id . "' " .
				"AND su.language_id = '" . $language_id . "' " .
				"AND su.query = CONCAT('product_id=', p.product_id) AND su.keyword <> '')"
			);

			$checks[] = $this->seoCheck(
				'seo_category',
				"SELECT COUNT(*) AS total FROM " . DB_PREFIX . "category c " .
				"WHERE c.status = '1' AND NOT EXISTS (" .
				"SELECT 1 FROM " . DB_PREFIX . "seo_url su " .
				"WHERE su.store_id = '" . $store_id . "' " .
				"AND su.language_id = '" . $language_id . "' " .
				"AND su.query = CONCAT('category_id=', c.category_id) AND su.keyword <> '')"
			);

			$checks[] = $this->seoCheck(
				'seo_manufacturer',
				"SELECT COUNT(*) AS total FROM " . DB_PREFIX . "manufacturer m " .
				"WHERE NOT EXISTS (" .
				"SELECT 1 FROM " . DB_PREFIX . "seo_url su " .
				"WHERE su.store_id = '" . $store_id . "' " .
				"AND su.language_id = '" . $language_id . "' " .
				"AND su.query = CONCAT('manufacturer_id=', m.manufacturer_id) AND su.keyword <> '')"
			);

			$checks[] = $this->seoCheck(
				'seo_information',
				"SELECT COUNT(*) AS total FROM " . DB_PREFIX . "information i " .
				"WHERE i.status = '1' AND NOT EXISTS (" .
				"SELECT 1 FROM " . DB_PREFIX . "seo_url su " .
				"WHERE su.store_id = '" . $store_id . "' " .
				"AND su.language_id = '" . $language_id . "' " .
				"AND su.query = CONCAT('information_id=', i.information_id) AND su.keyword <> '')"
			);
		} catch (Throwable $e) {
			$checks = array(
				array(
					'code'  => 'seo_query',
					'state' => 'error',
					'value' => ''
				)
			);
		}

		return $checks;
	}

	private function seoCheck($code, $sql) {
		$query = $this->db->query($sql);
		$total = isset($query->row['total']) ? (int)$query->row['total'] : 0;

		return array(
			'code'  => $code,
			'state' => $total > 0 ? 'warning' : 'ok',
			'value' => $total
		);
	}

	private function formatBytes($bytes) {
		$bytes = max(0, (int)$bytes);
		$units = array('B', 'KB', 'MB', 'GB');
		$value = (float)$bytes;
		$index = 0;

		while ($value >= 1024 && $index < count($units) - 1) {
			$value /= 1024;
			$index++;
		}

		if ($index === 0) {
			return (string)$bytes . ' ' . $units[$index];
		}

		return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.') . ' ' . $units[$index];
	}
}
