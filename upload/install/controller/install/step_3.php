<?php
class ControllerInstallStep3 extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('install/step_3');

		$sql_dumps = $this->getSqlDumps();
		$drivers = $this->getDatabaseDrivers();

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			if ($this->validate($sql_dumps, $drivers)) {
				try {
					$this->load->model('install/install');

					$install_data = $this->request->post;
					$install_data['repair_schema'] = !empty($this->request->post['repair_schema']) ? 1 : 0;

					$this->model_install_install->database($install_data);

					$this->writeConfigFiles($install_data);

					$this->session->data['install'] = 1;
					$this->response->redirect($this->url->link('install/step_4'));
					return;
				} catch (\Throwable $e) {
					$this->error['warning'] = $e->getMessage();
				}
			}
		}

		$this->document->setTitle($this->language->get('heading_title'));

		$data['heading_title'] = $this->language->get('heading_title');
		$data['text_step_3'] = $this->language->get('text_step_3');
		$data['text_db_connection'] = $this->language->get('text_db_connection');
		$data['text_db_administration'] = $this->language->get('text_db_administration');
		$data['text_dump_select'] = $this->language->get('text_dump_select');
		$data['text_dump'] = $this->language->get('text_dump');
		$data['text_repair_schema'] = $this->language->get('text_repair_schema');
		$data['help_repair_schema'] = $this->language->get('help_repair_schema');

		$data['entry_db_driver'] = $this->language->get('entry_db_driver');
		$data['entry_db_hostname'] = $this->language->get('entry_db_hostname');
		$data['entry_db_username'] = $this->language->get('entry_db_username');
		$data['entry_db_password'] = $this->language->get('entry_db_password');
		$data['entry_db_database'] = $this->language->get('entry_db_database');
		$data['entry_db_port'] = $this->language->get('entry_db_port');
		$data['entry_db_prefix'] = $this->language->get('entry_db_prefix');
		$data['entry_username'] = $this->language->get('entry_username');
		$data['entry_password'] = $this->language->get('entry_password');
		$data['entry_email'] = $this->language->get('entry_email');
		$data['entry_repair_schema'] = $this->language->get('entry_repair_schema');

		$data['button_continue'] = $this->language->get('button_continue');
		$data['button_back'] = $this->language->get('button_back');

		$data['error_warning'] = isset($this->error['warning']) ? $this->error['warning'] : '';
		$data['error_db_driver'] = isset($this->error['db_driver']) ? $this->error['db_driver'] : '';
		$data['error_db_hostname'] = isset($this->error['db_hostname']) ? $this->error['db_hostname'] : '';
		$data['error_db_username'] = isset($this->error['db_username']) ? $this->error['db_username'] : '';
		$data['error_db_database'] = isset($this->error['db_database']) ? $this->error['db_database'] : '';
		$data['error_db_port'] = isset($this->error['db_port']) ? $this->error['db_port'] : '';
		$data['error_db_prefix'] = isset($this->error['db_prefix']) ? $this->error['db_prefix'] : '';
		$data['error_sql_dump'] = isset($this->error['sql_dump']) ? $this->error['sql_dump'] : '';
		$data['error_username'] = isset($this->error['username']) ? $this->error['username'] : '';
		$data['error_password'] = isset($this->error['password']) ? $this->error['password'] : '';
		$data['error_email'] = isset($this->error['email']) ? $this->error['email'] : '';

		$data['action'] = $this->url->link('install/step_3');
		$data['drivers'] = $drivers;
		$data['sql_dumps'] = $sql_dumps;

		$default_driver = $drivers ? $drivers[0]['value'] : '';
		$data['db_driver'] = isset($this->request->post['db_driver']) ? $this->request->post['db_driver'] : $default_driver;
		$data['db_hostname'] = isset($this->request->post['db_hostname']) ? $this->request->post['db_hostname'] : 'localhost';
		$data['db_username'] = isset($this->request->post['db_username']) ? $this->request->post['db_username'] : 'root';
		$data['db_password'] = isset($this->request->post['db_password']) ? $this->request->post['db_password'] : '';
		$data['db_database'] = isset($this->request->post['db_database']) ? $this->request->post['db_database'] : '';
		$data['db_port'] = isset($this->request->post['db_port']) ? $this->request->post['db_port'] : '3306';
		$data['db_prefix'] = isset($this->request->post['db_prefix']) ? $this->request->post['db_prefix'] : 'oc_';
		$data['username'] = isset($this->request->post['username']) ? $this->request->post['username'] : 'admin';
		$data['password'] = isset($this->request->post['password']) ? $this->request->post['password'] : '';
		$data['email'] = isset($this->request->post['email']) ? $this->request->post['email'] : '';
		$data['sql_dump'] = isset($this->request->post['sql_dump']) ? $this->request->post['sql_dump'] : (in_array('opencart.sql', $sql_dumps, true) ? 'opencart.sql' : ($sql_dumps ? $sql_dumps[0] : ''));
		$data['repair_schema'] = $this->request->server['REQUEST_METHOD'] == 'POST' ? !empty($this->request->post['repair_schema']) : true;

		$data['back'] = $this->url->link('install/step_2');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');

		$this->response->setOutput($this->load->view('install/step_3', $data));
	}

	private function getDatabaseDrivers() {
		$drivers = array();

		if (extension_loaded('mysqli')) {
			$drivers[] = array(
				'text' => $this->language->get('text_mysqli'),
				'value' => 'mysqli'
			);
		}

		if (extension_loaded('pdo') && extension_loaded('pdo_mysql')) {
			$drivers[] = array(
				'text' => $this->language->get('text_pdo'),
				'value' => 'pdo'
			);
		}

		return $drivers;
	}

	private function getSqlDumps() {
		$files = glob(DIR_APPLICATION . '*.sql');
		$dumps = array();

		if ($files) {
			foreach ($files as $file) {
				if (is_file($file)) {
					$dumps[] = basename($file);
				}
			}
		}

		sort($dumps, SORT_NATURAL | SORT_FLAG_CASE);

		$default = array_search('opencart.sql', $dumps, true);
		if ($default !== false && $default !== 0) {
			unset($dumps[$default]);
			array_unshift($dumps, 'opencart.sql');
			$dumps = array_values($dumps);
		}

		return $dumps;
	}

	private function validate($sql_dumps, $drivers) {
		$db_driver = isset($this->request->post['db_driver']) ? $this->request->post['db_driver'] : '';
		$db_hostname = isset($this->request->post['db_hostname']) ? trim($this->request->post['db_hostname']) : '';
		$db_username = isset($this->request->post['db_username']) ? trim($this->request->post['db_username']) : '';
		$db_password = isset($this->request->post['db_password']) ? $this->request->post['db_password'] : '';
		$db_database = isset($this->request->post['db_database']) ? trim($this->request->post['db_database']) : '';
		$db_port = isset($this->request->post['db_port']) ? trim($this->request->post['db_port']) : '';
		$db_prefix = isset($this->request->post['db_prefix']) ? $this->request->post['db_prefix'] : '';
		$sql_dump = isset($this->request->post['sql_dump']) ? $this->request->post['sql_dump'] : '';
		$username = isset($this->request->post['username']) ? trim($this->request->post['username']) : '';
		$password = isset($this->request->post['password']) ? $this->request->post['password'] : '';
		$email = isset($this->request->post['email']) ? trim($this->request->post['email']) : '';

		if ($db_hostname === '') {
			$this->error['db_hostname'] = $this->language->get('error_db_hostname');
		}

		if ($db_username === '') {
			$this->error['db_username'] = $this->language->get('error_db_username');
		}

		if ($db_database === '') {
			$this->error['db_database'] = $this->language->get('error_db_database');
		}

		if ($db_port === '' || !ctype_digit($db_port) || (int)$db_port < 1 || (int)$db_port > 65535) {
			$this->error['db_port'] = $this->language->get('error_db_port');
		}

		if ($db_prefix !== '' && preg_match('/[^a-z0-9_]/', $db_prefix)) {
			$this->error['db_prefix'] = $this->language->get('error_db_prefix');
		}

		$available_drivers = array();
		foreach ($drivers as $driver) {
			$available_drivers[] = $driver['value'];
		}

		if (!in_array($db_driver, $available_drivers, true)) {
			$this->error['db_driver'] = $this->language->get('error_db_driver');
		}

		if ($sql_dump === '' || basename($sql_dump) !== $sql_dump || strtolower(pathinfo($sql_dump, PATHINFO_EXTENSION)) !== 'sql' || !in_array($sql_dump, $sql_dumps, true)) {
			$this->error['sql_dump'] = $this->language->get('error_sql_dump');
		}

		if ($username === '') {
			$this->error['username'] = $this->language->get('error_username');
		}

		if ((utf8_strlen($email) > 96) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$this->error['email'] = $this->language->get('error_email');
		}

		if ($password === '') {
			$this->error['password'] = $this->language->get('error_password');
		}

		if (!is_file(DIR_OPENCART . 'config.php') || !is_writable(DIR_OPENCART . 'config.php')) {
			$this->error['warning'] = $this->language->get('error_config') . DIR_OPENCART . 'config.php!';
		}

		if (!is_file(DIR_OPENCART . 'admin/config.php') || !is_writable(DIR_OPENCART . 'admin/config.php')) {
			$this->error['warning'] = $this->language->get('error_config') . DIR_OPENCART . 'admin/config.php!';
		}

		if (!$this->error) {
			try {
				new \DB(
					$db_driver,
					html_entity_decode($db_hostname, ENT_QUOTES, 'UTF-8'),
					html_entity_decode($db_username, ENT_QUOTES, 'UTF-8'),
					html_entity_decode($db_password, ENT_QUOTES, 'UTF-8'),
					html_entity_decode($db_database, ENT_QUOTES, 'UTF-8'),
					(int)$db_port
				);
			} catch (\Throwable $e) {
				$this->error['warning'] = $e->getMessage();
			}
		}

		return !$this->error;
	}

	private function writeConfigFiles($data) {
		$catalog = $this->buildCatalogConfig($data);
		$admin = $this->buildAdminConfig($data);

		$this->writeConfigFile(DIR_OPENCART . 'config.php', $catalog);
		$this->writeConfigFile(DIR_OPENCART . 'admin/config.php', $admin);
	}

	private function writeConfigFile($file, $content) {
		$result = @file_put_contents($file, $content, LOCK_EX);

		if ($result === false || $result !== strlen($content)) {
			throw new \Exception($this->language->get('error_config') . $file . '!');
		}
	}

	private function buildCatalogConfig($data) {
		$output = "<?php\n";
		$output .= "// HTTP\n";
		$output .= "define('HTTP_SERVER', " . var_export(HTTP_OPENCART, true) . ");\n\n";
		$output .= "// HTTPS\n";
		$output .= "define('HTTPS_SERVER', " . var_export(HTTP_OPENCART, true) . ");\n\n";
		$output .= "// DIR\n";
		$output .= "define('DIR_APPLICATION', " . var_export(DIR_OPENCART . 'catalog/', true) . ");\n";
		$output .= "define('DIR_SYSTEM', " . var_export(DIR_OPENCART . 'system/', true) . ");\n";
		$output .= "define('DIR_IMAGE', " . var_export(DIR_OPENCART . 'image/', true) . ");\n";
		$output .= "define('DIR_STORAGE', DIR_SYSTEM . 'storage/');\n";
		$output .= "define('DIR_LANGUAGE', DIR_APPLICATION . 'language/');\n";
		$output .= "define('DIR_TEMPLATE', DIR_APPLICATION . 'view/theme/');\n";
		$output .= "define('DIR_CONFIG', DIR_SYSTEM . 'config/');\n";
		$output .= "define('DIR_CACHE', DIR_STORAGE . 'cache/');\n";
		$output .= "define('DIR_DOWNLOAD', DIR_STORAGE . 'download/');\n";
		$output .= "define('DIR_LOGS', DIR_STORAGE . 'logs/');\n";
		$output .= "define('DIR_MODIFICATION', DIR_STORAGE . 'modification/');\n";
		$output .= "define('DIR_SESSION', DIR_STORAGE . 'session/');\n";
		$output .= "define('DIR_UPLOAD', DIR_STORAGE . 'upload/');\n\n";
		$output .= $this->buildDatabaseConfig($data);

		return $output;
	}

	private function buildAdminConfig($data) {
		$output = "<?php\n";
		$output .= "// HTTP\n";
		$output .= "define('HTTP_SERVER', " . var_export(HTTP_OPENCART . 'admin/', true) . ");\n";
		$output .= "define('HTTP_CATALOG', " . var_export(HTTP_OPENCART, true) . ");\n\n";
		$output .= "// HTTPS\n";
		$output .= "define('HTTPS_SERVER', " . var_export(HTTP_OPENCART . 'admin/', true) . ");\n";
		$output .= "define('HTTPS_CATALOG', " . var_export(HTTP_OPENCART, true) . ");\n\n";
		$output .= "// DIR\n";
		$output .= "define('DIR_APPLICATION', " . var_export(DIR_OPENCART . 'admin/', true) . ");\n";
		$output .= "define('DIR_SYSTEM', " . var_export(DIR_OPENCART . 'system/', true) . ");\n";
		$output .= "define('DIR_IMAGE', " . var_export(DIR_OPENCART . 'image/', true) . ");\n";
		$output .= "define('DIR_STORAGE', DIR_SYSTEM . 'storage/');\n";
		$output .= "define('DIR_CATALOG', " . var_export(DIR_OPENCART . 'catalog/', true) . ");\n";
		$output .= "define('DIR_LANGUAGE', DIR_APPLICATION . 'language/');\n";
		$output .= "define('DIR_TEMPLATE', DIR_APPLICATION . 'view/template/');\n";
		$output .= "define('DIR_CONFIG', DIR_SYSTEM . 'config/');\n";
		$output .= "define('DIR_CACHE', DIR_STORAGE . 'cache/');\n";
		$output .= "define('DIR_DOWNLOAD', DIR_STORAGE . 'download/');\n";
		$output .= "define('DIR_LOGS', DIR_STORAGE . 'logs/');\n";
		$output .= "define('DIR_MODIFICATION', DIR_STORAGE . 'modification/');\n";
		$output .= "define('DIR_SESSION', DIR_STORAGE . 'session/');\n";
		$output .= "define('DIR_UPLOAD', DIR_STORAGE . 'upload/');\n\n";
		$output .= $this->buildDatabaseConfig($data);

		return $output;
	}

	private function buildDatabaseConfig($data) {
		$password = html_entity_decode($data['db_password'], ENT_QUOTES, 'UTF-8');

		$output = "// DB\n";
		$output .= "define('DB_DRIVER', " . var_export($data['db_driver'], true) . ");\n";
		$output .= "define('DB_HOSTNAME', " . var_export($data['db_hostname'], true) . ");\n";
		$output .= "define('DB_USERNAME', " . var_export($data['db_username'], true) . ");\n";
		$output .= "define('DB_PASSWORD', " . var_export($password, true) . ");\n";
		$output .= "define('DB_DATABASE', " . var_export($data['db_database'], true) . ");\n";
		$output .= "define('DB_PORT', " . var_export((string)$data['db_port'], true) . ");\n";
		$output .= "define('DB_PREFIX', " . var_export($data['db_prefix'], true) . ");\n";

		return $output;
	}
}
