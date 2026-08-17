<?php
class ControllerStartupDatabase extends Controller {
	public function index() {
		$config_file = DIR_OPENCART . 'config.php';

		if (!is_file($config_file) || filesize($config_file) === 0) {
			return;
		}

		require_once($config_file);

		if (
			!defined('DB_DRIVER') ||
			!defined('DB_HOSTNAME') ||
			!defined('DB_USERNAME') ||
			!defined('DB_PASSWORD') ||
			!defined('DB_DATABASE')
		) {
			return;
		}

		if (defined('DB_PORT') && DB_PORT !== '') {
			$port = DB_PORT;
		} else {
			$port = ini_get('mysqli.default_port');
		}

		$this->registry->set(
			'db',
			new DB(
				DB_DRIVER,
				html_entity_decode(DB_HOSTNAME, ENT_QUOTES, 'UTF-8'),
				html_entity_decode(DB_USERNAME, ENT_QUOTES, 'UTF-8'),
				html_entity_decode(DB_PASSWORD, ENT_QUOTES, 'UTF-8'),
				html_entity_decode(DB_DATABASE, ENT_QUOTES, 'UTF-8'),
				$port
			)
		);
	}
}
