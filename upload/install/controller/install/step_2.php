<?php
class ControllerInstallStep2 extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('install/step_2');

		$this->createConfigFile(DIR_OPENCART . 'config.php', DIR_OPENCART);
		$this->createConfigFile(DIR_OPENCART . 'admin/config.php', DIR_OPENCART . 'admin/');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->response->redirect($this->url->link('install/step_3'));
			return;
		}

		$this->document->setTitle($this->language->get('heading_title'));

		$data['heading_title'] = $this->language->get('heading_title');
		$data['text_step_2'] = $this->language->get('text_step_2');
		$data['text_install_php'] = $this->language->get('text_install_php');
		$data['text_install_extension'] = $this->language->get('text_install_extension');
		$data['text_install_file'] = $this->language->get('text_install_file');
		$data['text_install_directory'] = $this->language->get('text_install_directory');
		$data['text_setting'] = $this->language->get('text_setting');
		$data['text_current'] = $this->language->get('text_current');
		$data['text_required'] = $this->language->get('text_required');
		$data['text_extension'] = $this->language->get('text_extension');
		$data['text_file'] = $this->language->get('text_file');
		$data['text_directory'] = $this->language->get('text_directory');
		$data['text_status'] = $this->language->get('text_status');
		$data['text_on'] = $this->language->get('text_on');
		$data['text_off'] = $this->language->get('text_off');
		$data['text_writable'] = $this->language->get('text_writable');
		$data['text_version'] = $this->language->get('text_version');
		$data['text_file_upload'] = $this->language->get('text_file_upload');
		$data['text_session'] = $this->language->get('text_session');
		$data['text_db'] = $this->language->get('text_db');
		$data['text_gd'] = $this->language->get('text_gd');
		$data['text_curl'] = $this->language->get('text_curl');
		$data['text_openssl'] = $this->language->get('text_openssl');
		$data['text_zlib'] = $this->language->get('text_zlib');
		$data['text_zip'] = $this->language->get('text_zip');
		$data['text_mbstring'] = $this->language->get('text_mbstring');
		$data['text_dom'] = $this->language->get('text_dom');
		$data['text_hash'] = $this->language->get('text_hash');
		$data['text_xmlwriter'] = $this->language->get('text_xmlwriter');
		$data['text_json'] = $this->language->get('text_json');
		$data['button_continue'] = $this->language->get('button_continue');
		$data['button_back'] = $this->language->get('button_back');
		$data['error_warning'] = isset($this->error['warning']) ? $this->error['warning'] : '';
		$data['action'] = $this->url->link('install/step_2');

		$paths = array(
			'admin_config'   => DIR_OPENCART . 'admin/config.php',
			'catalog_config' => DIR_OPENCART . 'config.php',
			'image'          => DIR_IMAGE,
			'image_cache'    => DIR_IMAGE . 'cache/',
			'image_catalog'  => DIR_IMAGE . 'catalog/',
			'cache'          => DIR_CACHE,
			'logs'           => DIR_LOGS,
			'download'       => DIR_DOWNLOAD,
			'upload'         => DIR_UPLOAD,
			'modification'   => DIR_MODIFICATION,
			'session_dir'    => DIR_SESSION
		);

		foreach ($paths as $key => $path) {
			if (!file_exists($path)) {
				$data['error_' . $key] = $this->language->get('error_missing');
			} elseif (!is_writable($path)) {
				$data['error_' . $key] = $this->language->get('error_unwritable');
			} else {
				$data['error_' . $key] = '';
			}
		}

		$data['php_version'] = PHP_VERSION;
		$data['version'] = version_compare(PHP_VERSION, '7.3.0', '>=');
		$data['file_uploads'] = (bool)ini_get('file_uploads');
		$data['session_auto_start'] = (bool)ini_get('session.auto_start');
		$data['db'] = $this->hasSupportedDatabaseDriver();
		$data['gd'] = extension_loaded('gd');
		$data['curl'] = extension_loaded('curl');
		$data['openssl'] = function_exists('openssl_encrypt');
		$data['zlib'] = extension_loaded('zlib');
		$data['zip'] = extension_loaded('zip');
		$data['iconv'] = function_exists('iconv');
		$data['mbstring'] = extension_loaded('mbstring');
		$data['dom'] = extension_loaded('dom');
		$data['hash'] = extension_loaded('hash');
		$data['xmlwriter'] = extension_loaded('xmlwriter');
		$data['json'] = extension_loaded('json');

		$data['catalog_config'] = DIR_OPENCART . 'config.php';
		$data['admin_config'] = DIR_OPENCART . 'admin/config.php';
		$data['image'] = DIR_IMAGE;
		$data['image_cache'] = DIR_IMAGE . 'cache';
		$data['image_catalog'] = DIR_IMAGE . 'catalog';
		$data['cache'] = DIR_CACHE;
		$data['logs'] = DIR_LOGS;
		$data['download'] = DIR_DOWNLOAD;
		$data['upload'] = DIR_UPLOAD;
		$data['modification'] = DIR_MODIFICATION;
		$data['session_dir'] = DIR_SESSION;
		$data['back'] = $this->url->link('install/step_1');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');

		$this->response->setOutput($this->load->view('install/step_2', $data));
	}

	private function createConfigFile($file, $directory) {
		if (is_file($file) || !is_writable($directory)) {
			return;
		}

		$handle = @fopen($file, 'x');
		if ($handle) {
			fclose($handle);
		}
	}

	private function hasSupportedDatabaseDriver() {
		return extension_loaded('mysqli') || (extension_loaded('pdo') && extension_loaded('pdo_mysql'));
	}

	private function setWarning($language_key) {
		if (!isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get($language_key);
		}
	}

	private function validate() {
		if (version_compare(PHP_VERSION, '7.3.0', '<')) {
			$this->setWarning('error_version');
		}
		if (!ini_get('file_uploads')) {
			$this->setWarning('error_file_upload');
		}
		if (ini_get('session.auto_start')) {
			$this->setWarning('error_session');
		}
		if (!$this->hasSupportedDatabaseDriver()) {
			$this->setWarning('error_db');
		}
		if (!extension_loaded('gd')) {
			$this->setWarning('error_gd');
		}
		if (!extension_loaded('curl')) {
			$this->setWarning('error_curl');
		}
		if (!function_exists('openssl_encrypt')) {
			$this->setWarning('error_openssl');
		}
		if (!extension_loaded('zlib')) {
			$this->setWarning('error_zlib');
		}
		if (!extension_loaded('zip')) {
			$this->setWarning('error_zip');
		}
		if (!function_exists('iconv') && !extension_loaded('mbstring')) {
			$this->setWarning('error_mbstring');
		}
		if (!extension_loaded('dom')) {
			$this->setWarning('error_dom');
		}
		if (!extension_loaded('hash')) {
			$this->setWarning('error_hash');
		}
		if (!extension_loaded('xmlwriter')) {
			$this->setWarning('error_xmlwriter');
		}
		if (!extension_loaded('json')) {
			$this->setWarning('error_json');
		}

		$requirements = array(
			array(DIR_OPENCART . 'config.php', 'error_catalog_exist', 'error_catalog_writable'),
			array(DIR_OPENCART . 'admin/config.php', 'error_admin_exist', 'error_admin_writable'),
			array(DIR_IMAGE, 'error_image', 'error_image'),
			array(DIR_IMAGE . 'cache/', 'error_image_cache', 'error_image_cache'),
			array(DIR_IMAGE . 'catalog/', 'error_image_catalog', 'error_image_catalog'),
			array(DIR_CACHE, 'error_cache', 'error_cache'),
			array(DIR_LOGS, 'error_log', 'error_log'),
			array(DIR_DOWNLOAD, 'error_download', 'error_download'),
			array(DIR_UPLOAD, 'error_upload', 'error_upload'),
			array(DIR_MODIFICATION, 'error_modification', 'error_modification'),
			array(DIR_SESSION, 'error_missing', 'error_unwritable')
		);

		foreach ($requirements as $requirement) {
			if (!file_exists($requirement[0])) {
				$this->setWarning($requirement[1]);
			} elseif (!is_writable($requirement[0])) {
				$this->setWarning($requirement[2]);
			}
		}

		return !$this->error;
	}
}
