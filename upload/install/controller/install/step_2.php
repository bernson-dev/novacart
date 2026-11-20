<?php
class ControllerInstallStep2 extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('install/step_2');

		// Создаём отсутствующие конфиги и удаляем config-dist.php если он есть.
		$this->createConfig(DIR_OPENCART . 'config.php');
		$this->createConfig(DIR_OPENCART . 'admin/config.php');

		// И GET, и POST используют одни и те же результаты проверки.
		$checks = $this->getChecks();

		if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validate($checks)) {
			$this->response->redirect($this->url->link('install/step_3'));
			return;
		}

		$this->document->setTitle($this->language->get('heading_title'));

		$language_keys = array(
		'heading_title', 'text_step_2', 'text_install_php', 'text_install_extension',
		'text_install_file', 'text_install_directory', 'text_setting', 'text_current',
		'text_required', 'text_extension', 'text_file', 'text_directory', 'text_status',
		'text_on', 'text_off', 'text_missing', 'text_writable', 'text_unwritable',
		'text_version', 'text_global', 'text_magic', 'text_file_upload', 'text_session',
		'text_db', 'text_gd', 'text_curl', 'text_openssl', 'text_zlib', 'text_zip',
		'text_mbstring', 'text_dom', 'text_hash', 'text_xmlwriter', 'text_json',
		'button_continue', 'button_back'
		);

		$data = array();
		foreach ($language_keys as $key) {
			$data[$key] = $this->language->get($key);
		}

		$data['error_warning'] = isset($this->error['warning']) ? $this->error['warning'] : '';
		$data['php_checks'] = $checks['php'];
		$data['extension_checks'] = $checks['extensions'];
		$data['file_checks'] = $checks['files'];
		$data['directory_checks'] = $checks['directories'];
		$data['checks_ok'] = $checks['ok'];
		$data['action'] = $this->url->link('install/step_2');
		$data['back'] = $this->url->link('install/step_1');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');
		$data['step_header'] = $this->load->controller('common/step_header');
		$data['column_left'] = $this->load->controller('common/column_left');

		$this->response->setOutput($this->load->view('install/step_2', $data));
	}

	private function createConfig($path) {
		if (!is_file($path) && is_dir(dirname($path)) && is_writable(dirname($path))) {
			// Режим x не перезаписывает существующий файл даже при параллельном запросе.
			$handle = @fopen($path, 'x');
			if ($handle !== false) {
				fclose($handle);

				// удалить config-dist.php
				$dist = dirname($path) . '/config-dist.php';
				if (is_file($dist)) {
					@unlink($dist);
				}
			}
		}
	}

	private function getChecks() {
		$on = $this->language->get('text_on');
		$off = $this->language->get('text_off');
		$version_ok = version_compare(PHP_VERSION, '7.3.0', '>=');
		$uploads_ok = (bool)ini_get('file_uploads');
		$session_ok = !(bool)ini_get('session.auto_start');

		$checks = array('php' => array(), 'extensions' => array(), 'files' => array(), 'directories' => array(), 'ok' => true);
		$checks['php'][] = $this->makeCheck($this->language->get('text_version'), PHP_VERSION, '7.3+', $version_ok, 'error_version');
		$checks['php'][] = $this->makeCheck($this->language->get('text_file_upload'), $uploads_ok ? $on : $off, $on, $uploads_ok, 'error_file_upload');
		$checks['php'][] = $this->makeCheck($this->language->get('text_session'), $session_ok ? $off : $on, $off, $session_ok, 'error_session');

		$db_ok = extension_loaded('mysqli') || extension_loaded('pdo') || extension_loaded('pgsql');
		$extensions = array(
			array('text_db', $db_ok, 'error_db'),
			array('text_gd', extension_loaded('gd'), 'error_gd'),
			array('text_curl', extension_loaded('curl'), 'error_curl'),
			array('text_openssl', function_exists('openssl_encrypt'), 'error_openssl'),
			array('text_zlib', extension_loaded('zlib'), 'error_zlib'),
			array('text_zip', extension_loaded('zip'), 'error_zip'),
			array('text_mbstring', function_exists('iconv') || extension_loaded('mbstring'), 'error_mbstring'),
			array('text_dom', extension_loaded('dom'), 'error_dom'),
			array('text_hash', extension_loaded('hash'), 'error_hash'),
			array('text_xmlwriter', extension_loaded('xmlwriter'), 'error_xmlwriter'),
			array('text_json', extension_loaded('json'), 'error_json')
		);

		foreach ($extensions as $extension) {
			$checks['extensions'][] = $this->makeCheck(
			$this->language->get($extension[0]),
			$extension[1] ? $on : $off,
			$on,
			$extension[1],
			$extension[2]
			);
		}

		$files = array(
			array(DIR_OPENCART . 'config.php', 'error_catalog_exist', 'error_catalog_writable'),
			array(DIR_OPENCART . 'admin/config.php', 'error_admin_exist', 'error_admin_writable')
		);

		foreach ($files as $file) {
			$checks['files'][] = $this->checkPath($file[0], false, $file[1], $file[2]);
		}

		$directories = array(
			array(DIR_IMAGE, 'error_image'),
			array(DIR_IMAGE . 'cache/', 'error_image_cache'),
			array(DIR_IMAGE . 'catalog/', 'error_image_catalog'),
			array(DIR_CACHE, 'error_cache'),
			array(DIR_LOGS, 'error_log'),
			array(DIR_DOWNLOAD, 'error_download'),
			array(DIR_UPLOAD, 'error_upload'),
			array(DIR_MODIFICATION, 'error_modification')
		);

		foreach ($directories as $directory) {
			$checks['directories'][] = $this->checkPath($directory[0], true, 'error_missing', $directory[1]);
		}

		foreach (array('php', 'extensions', 'files', 'directories') as $group) {
			foreach ($checks[$group] as $item) {
				if (!$item['ok']) {
					$checks['ok'] = false;
				}
			}
		}

		return $checks;
	}

	private function makeCheck($label, $current, $required, $ok, $error_key) {
		return array(
			'label' => $label,
			'current' => $current,
			'required' => $required,
			'ok' => (bool)$ok,
			'error_key' => $error_key,
			'permissions' => '',
			'permission_ok' => true
		);
	}

	private function checkPath($path, $directory, $missing_key, $writable_key) {
		$exists = $directory ? is_dir($path) : is_file($path);
		$permissions = '----';
		$permission_ok = false;
		$writable = false;

		if ($exists) {
			// Пытаемся установить 0755.
			@chmod($path, 0755);
			clearstatcache(true, $path);

			$mode = @fileperms($path);
			if ($mode !== false) {
				$permissions = substr(sprintf('%04o', $mode & 07777), -4);
				$permission_ok = (($mode & 07777) === 0755);
			}
			$writable = is_writable($path);
		}

		$row = $this->makeCheck(
		$path,
		!$exists ? $this->language->get('text_missing') : ($writable ? $this->language->get('text_writable') : $this->language->get('text_unwritable')),
		$this->language->get('text_writable'),
		$exists && $writable,
		!$exists ? $missing_key : $writable_key
		);
		$row['permissions'] = $permissions;
		$row['permission_ok'] = $permission_ok;
		return $row;
	}

	private function validate($checks) {
		foreach (array('php', 'extensions', 'files', 'directories') as $group) {
			foreach ($checks[$group] as $check) {
				if (!$check['ok']) {
					$this->error['warning'] = $this->language->get($check['error_key']);
					return false;
				}
			}
		}
		return true;
	}
}
