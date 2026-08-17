<?php
// admin/controller/common/developer.php
class ControllerCommonDeveloper extends Controller {
	private $php_recommended = array(
		'max_input_vars' => array('value' => 2000, 'operator' => '>='),
		'session.gc_maxlifetime' => array('value' => 1800, 'operator' => '>='),
		'session.cookie_lifetime' => array('value' => 0, 'operator' => '==')
	);

	public function index() {
		$this->load->language('common/developer');

		$data['user_token'] = $this->session->data['user_token'];
		$data['developer_theme'] = (int)$this->config->get('developer_theme');
		$data['developer_sass'] = (int)$this->config->get('developer_sass');
		$data['cache_engine'] = (string)$this->config->get('cache_engine');

		$version_part = explode('-', phpversion());
		$data['php_version'] = $version_part[0];
		$data['twig_version'] = defined('\\Twig\\Environment::VERSION') ? constant('\\Twig\\Environment::VERSION') : '';

		// ionCube: показываем фактическое состояние без искусственного минимального требования.
		if (extension_loaded('ionCube Loader') && function_exists('ioncube_loader_version')) {
			$data['ioncube_version'] = ioncube_loader_version();
			$data['ioncube_class'] = 'text-success';
		} else {
			$data['ioncube_version'] = false;
			$data['ioncube_class'] = 'text-danger';
		}

		$data['opcache_enabled'] = function_exists('opcache_get_status') && (bool)ini_get('opcache.enable');

		// PHP-параметры с проверяемыми рекомендациями.
		$data['params'] = array();

		foreach ($this->php_recommended as $key => $recommended) {
			$value = ini_get($key);
			$warning = !$this->compareIniValue($value, $recommended['value'], $recommended['operator']);

			$data['params'][] = array(
				'name'        => $key,
				'info'        => sprintf($this->language->get('php_info_' . $key), $recommended['value']),
				'recommended' => $recommended['operator'] . ' ' . $recommended['value'],
				'value'       => $value,
				'warning'     => $warning
			);
		}

		// Информационные параметры: показываем значение, но не навязываем универсальный порог.
		$info_params = array('memory_limit', 'max_execution_time', 'upload_max_filesize', 'post_max_size');

		foreach ($info_params as $key) {
			$data['params'][] = array(
				'name'        => $key,
				'info'        => $this->language->get('php_info_' . $key),
				'recommended' => '',
				'value'       => ini_get($key),
				'warning'     => false
			);
		}

		$eval = false;
		$eval = '$eval = true;';
		eval($eval);

		if ($eval === true) {
			$data['eval'] = true;
		} else {
			$this->load->model('setting/setting');

			// Не перезаписываем всю группу developer одним ключом.
			$this->model_setting_setting->editSettingValue('developer', 'developer_theme', 1, 0);
			$data['developer_theme'] = 1;
			$data['eval'] = false;
		}

		$this->response->setOutput($this->load->view('common/developer', $data));
	}

	public function edit() {
		$this->load->language('common/developer');
		$json = array();

		if (!$this->user->hasPermission('modify', 'common/developer')) {
			$json['error'] = $this->language->get('error_permission');
		} else {
			$settings = array(
				'developer_theme' => isset($this->request->post['developer_theme'])
					? ((int)$this->request->post['developer_theme'] === 1 ? 1 : 0)
					: (int)$this->config->get('developer_theme'),
				'developer_sass' => isset($this->request->post['developer_sass'])
					? ((int)$this->request->post['developer_sass'] === 1 ? 1 : 0)
					: (int)$this->config->get('developer_sass')
			);

			$this->load->model('setting/setting');
			$this->model_setting_setting->editSetting('developer', $settings, 0);
			$json['success'] = $this->language->get('text_success');
		}

		$this->sendJson($json);
	}

	public function theme() {
		$this->sendJson($this->clearTemplateCache());
	}

	public function sass() {
		$this->sendJson($this->clearSassCache());
	}

	public function systemcache() {
		$this->sendJson($this->clearSystemCache());
	}

	public function imgcache() {
		$this->sendJson($this->clearImageCache());
	}

	public function cachestats() {
		$this->load->language('common/developer');
		$this->sendJson(array(
			'success' => true,
			'stats'   => $this->getAllCacheStats()
		));
	}

	public function allcache() {
		$this->load->language('common/developer');

		if (!$this->user->hasPermission('modify', 'common/developer')) {
			$this->sendJson(array('error' => $this->language->get('error_permission')));
			return;
		}

		$results = array(
			$this->clearSystemCache(),
			$this->clearImageCache(),
			$this->clearSassCache(),
			$this->clearTemplateCache()
		);

		$deleted_files = 0;
		$freed_bytes = 0;
		$failed = 0;

		foreach ($results as $result) {
			$deleted_files += isset($result['deleted_files']) ? (int)$result['deleted_files'] : 0;
			$freed_bytes += isset($result['freed_bytes']) ? (int)$result['freed_bytes'] : 0;
			$failed += isset($result['failed']) ? (int)$result['failed'] : 0;
		}

		$json = array(
			'deleted_files' => $deleted_files,
			'freed_bytes'   => $freed_bytes,
			'failed'        => $failed,
			'stats'         => $this->getAllCacheStats()
		);

		if ($failed > 0) {
			$json['error'] = sprintf(
				$this->language->get('text_cache_partial'),
				$deleted_files,
				$this->formatBytes($freed_bytes),
				$failed
			);
		} else {
			$json['success'] = sprintf(
				$this->language->get('text_cache_cleared'),
				$this->language->get('text_allcache'),
				$deleted_files,
				$this->formatBytes($freed_bytes)
			);
		}

		$this->sendJson($json);
	}

	private function clearTemplateCache() {
		$this->load->language('common/developer');

		if (!$this->user->hasPermission('modify', 'common/developer')) {
			return array('error' => $this->language->get('error_permission'));
		}

		$result = $this->deleteDirectoryContents(DIR_CACHE . 'template', array('index.html'));
		return $this->buildClearResponse('theme', $this->language->get('text_theme'), $result);
	}

	private function clearSassCache() {
		$this->load->language('common/developer');

		if (!$this->user->hasPermission('modify', 'common/developer')) {
			return array('error' => $this->language->get('error_permission'));
		}

		$result = array('deleted_files' => 0, 'freed_bytes' => 0, 'failed' => 0);

		foreach ($this->getSassCacheFiles() as $file) {
			$this->deleteFile($file, $result);
		}

		return $this->buildClearResponse('sass', $this->language->get('text_sass'), $result);
	}

	private function clearSystemCache() {
		$this->load->language('common/developer');

		if (!$this->user->hasPermission('modify', 'common/developer')) {
			return array('error' => $this->language->get('error_permission'));
		}

		$result = array('deleted_files' => 0, 'freed_bytes' => 0, 'failed' => 0);

		if ($this->config->get('cache_engine') === 'fastfile') {
			// FastFile хранит записи в DIR_CACHE/cache/... с именами cache.*.json/cache.*.txt.
			// Его clear() возвращает void и подавляет ошибки unlink(), поэтому заранее
			// фиксируем реальные файлы и после clear() проверяем удаление именно их.
			$files = $this->getFastFileCacheFiles();
			$sizes = array();

			foreach ($files as $file) {
				$size = @filesize($file);
				$sizes[$file] = $size !== false ? (int)$size : 0;
			}

			try {
				require_once DIR_SYSTEM . 'library/cache/fastfile.php';
				$fastcache = new \Cache\FastFile();
				$fastcache->clear();
			} catch (\Throwable $e) {
				// Если сам clear() завершился исключением, ниже всё равно проверим
				// каждый исходный файл и получим фактический результат.
			}

			foreach ($files as $file) {
				if (is_file($file) || is_link($file)) {
					$result['failed']++;
				} else {
					$result['deleted_files']++;
					$result['freed_bytes'] += isset($sizes[$file]) ? $sizes[$file] : 0;
				}
			}
		} else {
			$files = glob(DIR_CACHE . 'cache.*');

			foreach ((array)$files as $file) {
				$this->deletePath($file, $result);
			}
		}

		return $this->buildClearResponse('systemcache', $this->language->get('text_systemcache'), $result);
	}

	private function clearImageCache() {
		$this->load->language('common/developer');

		if (!$this->user->hasPermission('modify', 'common/developer')) {
			return array('error' => $this->language->get('error_permission'));
		}

		$result = $this->deleteDirectoryContents(DIR_IMAGE . 'cache', array('index.html'));
		return $this->buildClearResponse('imgcache', $this->language->get('text_imgcache'), $result);
	}

	private function buildClearResponse($cache_key, $cache_name, $result) {
		$response = array(
			'deleted_files' => (int)$result['deleted_files'],
			'freed_bytes'   => (int)$result['freed_bytes'],
			'failed'        => (int)$result['failed'],
			'stats'         => array($cache_key => $this->getCacheStatsByKey($cache_key))
		);

		if ($result['failed'] > 0) {
			$response['error'] = sprintf(
				$this->language->get('text_cache_partial'),
				$result['deleted_files'],
				$this->formatBytes($result['freed_bytes']),
				$result['failed']
			);
		} else {
			$response['success'] = sprintf(
				$this->language->get('text_cache_cleared'),
				$cache_name,
				$result['deleted_files'],
				$this->formatBytes($result['freed_bytes'])
			);
		}

		return $response;
	}

	private function getAllCacheStats() {
		return array(
			'systemcache' => $this->getSystemCacheStats(),
			'theme'       => $this->getTemplateCacheStats(),
			'sass'        => $this->getSassCacheStats(),
			'imgcache'    => $this->getImageCacheStats()
		);
	}

	private function getCacheStatsByKey($key) {
		switch ($key) {
			case 'systemcache':
				return $this->getSystemCacheStats();
			case 'theme':
				return $this->getTemplateCacheStats();
			case 'sass':
				return $this->getSassCacheStats();
			case 'imgcache':
				return $this->getImageCacheStats();
		}

		return $this->prepareStats(array('files' => 0, 'bytes' => 0, 'failed' => 0));
	}

	private function getSystemCacheStats() {
		$stats = array('files' => 0, 'bytes' => 0, 'failed' => 0);

		if ($this->config->get('cache_engine') === 'fastfile') {
			foreach ($this->getFastFileCacheFiles() as $file) {
				$this->scanPath($file, $stats);
			}
		} else {
			$files = glob(DIR_CACHE . 'cache.*');

			foreach ((array)$files as $file) {
				$this->scanPath($file, $stats);
			}
		}

		return $this->prepareStats($stats);
	}

	private function getFastFileCacheFiles() {
		$files = array();
		$base_dir = rtrim(DIR_CACHE, '/\\') . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR;

		if (!is_dir($base_dir)) {
			return $files;
		}

		try {
			$iterator = new \RecursiveIteratorIterator(
				new \RecursiveDirectoryIterator($base_dir, \FilesystemIterator::SKIP_DOTS),
				\RecursiveIteratorIterator::LEAVES_ONLY
			);

			foreach ($iterator as $file) {
				if (!$file->isFile()) {
					continue;
				}

				// Повторяем критерий FastFile::collectCacheFiles():
				// учитываются только файлы, имя которых начинается с "cache.".
				if (strpos($file->getFilename(), 'cache.') === 0) {
					$files[] = $file->getPathname();
				}
			}
		} catch (\UnexpectedValueException $e) {
			return $files;
		}

		return $files;
	}

	private function getTemplateCacheStats() {
		$stats = array('files' => 0, 'bytes' => 0, 'failed' => 0);
		$this->scanDirectory(DIR_CACHE . 'template', $stats, array('index.html'));
		return $this->prepareStats($stats);
	}

	private function getImageCacheStats() {
		$stats = array('files' => 0, 'bytes' => 0, 'failed' => 0);
		$this->scanDirectory(DIR_IMAGE . 'cache', $stats, array('index.html'));
		return $this->prepareStats($stats);
	}

	private function getSassCacheStats() {
		$stats = array('files' => 0, 'bytes' => 0, 'failed' => 0);

		foreach ($this->getSassCacheFiles() as $file) {
			$this->scanPath($file, $stats);
		}

		return $this->prepareStats($stats);
	}

	private function getSassCacheFiles() {
		$files = array();
		$admin_css = DIR_APPLICATION . 'view/stylesheet/bootstrap.css';
		$admin_scss = DIR_APPLICATION . 'view/stylesheet/sass/_bootstrap.scss';

		if (is_file($admin_css) && is_file($admin_scss)) {
			$files[] = $admin_css;
		}

		$scss_files = glob(DIR_CATALOG . 'view/theme/*/stylesheet/sass/_bootstrap.scss');

		foreach ((array)$scss_files as $scss_file) {
			$css_file = substr($scss_file, 0, -21) . '/bootstrap.css';

			if (is_file($css_file)) {
				$files[] = $css_file;
			}
		}

		return array_values(array_unique($files));
	}

	private function prepareStats($stats) {
		return array(
			'files'     => (int)$stats['files'],
			'bytes'     => (int)$stats['bytes'],
			'size'      => $this->formatBytes($stats['bytes']),
			'failed'    => (int)$stats['failed']
		);
	}

	private function scanDirectory($dirname, &$stats, $exclude_names = array()) {
		if (!is_dir($dirname)) {
			return;
		}

		try {
			$iterator = new \RecursiveIteratorIterator(
				new \RecursiveDirectoryIterator($dirname, \FilesystemIterator::SKIP_DOTS),
				\RecursiveIteratorIterator::LEAVES_ONLY
			);

			foreach ($iterator as $file) {
				if (in_array($file->getFilename(), $exclude_names, true)) {
					continue;
				}

				// Исключение каталога верхнего уровня (например template для FastFile-статистики).
				$relative = substr($file->getPathname(), strlen(rtrim($dirname, '/\\')) + 1);
				$first_part = preg_split('#[\\\\/]#', $relative);

				if ($first_part && in_array($first_part[0], $exclude_names, true)) {
					continue;
				}

				if ($file->isFile() || $file->isLink()) {
					$stats['files']++;
					$size = $file->getSize();
					$stats['bytes'] += $size > 0 ? $size : 0;
				}
			}
		} catch (\UnexpectedValueException $e) {
			$stats['failed']++;
		}
	}

	private function scanPath($path, &$stats) {
		if (is_file($path) || is_link($path)) {
			$stats['files']++;
			$size = @filesize($path);
			$stats['bytes'] += $size !== false ? (int)$size : 0;
		} elseif (is_dir($path)) {
			$this->scanDirectory($path, $stats);
		}
	}

	private function deleteDirectoryContents($dirname, $exclude_names = array()) {
		$result = array('deleted_files' => 0, 'freed_bytes' => 0, 'failed' => 0);

		if (!is_dir($dirname)) {
			return $result;
		}

		try {
			$iterator = new \DirectoryIterator($dirname);

			foreach ($iterator as $item) {
				if ($item->isDot() || in_array($item->getFilename(), $exclude_names, true)) {
					continue;
				}

				$this->deletePath($item->getPathname(), $result);
			}
		} catch (\UnexpectedValueException $e) {
			$result['failed']++;
		}

		return $result;
	}

	private function deletePath($path, &$result) {
		if (is_link($path) || is_file($path)) {
			$this->deleteFile($path, $result);
			return;
		}

		if (!is_dir($path)) {
			return;
		}

		try {
			$iterator = new \RecursiveIteratorIterator(
				new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS),
				\RecursiveIteratorIterator::CHILD_FIRST
			);

			foreach ($iterator as $item) {
				$item_path = $item->getPathname();

				if ($item->isDir() && !$item->isLink()) {
					if (!@rmdir($item_path)) {
						$result['failed']++;
					}
				} else {
					$this->deleteFile($item_path, $result);
				}
			}

			if (!@rmdir($path)) {
				$result['failed']++;
			}
		} catch (\UnexpectedValueException $e) {
			$result['failed']++;
		}
	}

	private function deleteFile($file, &$result) {
		if (!is_file($file) && !is_link($file)) {
			return;
		}

		$size = @filesize($file);
		$size = $size !== false ? (int)$size : 0;

		if (@unlink($file)) {
			$result['deleted_files']++;
			$result['freed_bytes'] += $size;
		} else {
			$result['failed']++;
		}
	}

	private function compareIniValue($value, $recommended, $operator) {
		$value = (float)$value;
		$recommended = (float)$recommended;

		switch ($operator) {
			case '==':
				return $value == $recommended;
			case '>=':
				return $value >= $recommended;
			case '<=':
				return $value <= $recommended;
			case '>':
				return $value > $recommended;
			case '<':
				return $value < $recommended;
		}

		return true;
	}

	private function formatBytes($bytes) {
		$bytes = max(0, (int)$bytes);
		$units = array('B', 'KB', 'MB', 'GB', 'TB');
		$index = 0;
		$value = (float)$bytes;

		while ($value >= 1024 && $index < count($units) - 1) {
			$value /= 1024;
			$index++;
		}

		if ($index === 0) {
			return (string)(int)$value . ' ' . $units[$index];
		}

		return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.') . ' ' . $units[$index];
	}

	private function sendJson($json) {
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
