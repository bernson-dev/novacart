<?php
class ControllerStartupDatabase extends Controller {
	public function index() {
		$config_file = DIR_OPENCART . 'config.php';

		if (!is_file($config_file) || filesize($config_file) === 0) {
			return;
		}

		$config = file_get_contents($config_file);

		if ($config === false || $config === '') {
			return;
		}

		$db_config = $this->parseDatabaseConfig($config);

		$required = array(
			'DB_DRIVER',
			'DB_HOSTNAME',
			'DB_USERNAME',
			'DB_PASSWORD',
			'DB_DATABASE'
		);

		foreach ($required as $name) {
			if (!array_key_exists($name, $db_config)) {
				return;
			}
		}

		$port = isset($db_config['DB_PORT']) && $db_config['DB_PORT'] !== ''
			? $db_config['DB_PORT']
			: ini_get('mysqli.default_port');

		$this->registry->set(
			'db',
			new DB(
				$db_config['DB_DRIVER'],
				html_entity_decode($db_config['DB_HOSTNAME'], ENT_QUOTES, 'UTF-8'),
				html_entity_decode($db_config['DB_USERNAME'], ENT_QUOTES, 'UTF-8'),
				html_entity_decode($db_config['DB_PASSWORD'], ENT_QUOTES, 'UTF-8'),
				html_entity_decode($db_config['DB_DATABASE'], ENT_QUOTES, 'UTF-8'),
				$port
			)
		);
	}

	private function parseDatabaseConfig($config) {
		$result = array();
		$allowed = array(
			'DB_DRIVER',
			'DB_HOSTNAME',
			'DB_USERNAME',
			'DB_PASSWORD',
			'DB_DATABASE',
			'DB_PORT',
			'DB_PREFIX'
		);

		$pattern = '/define\s*\(\s*([\'\"])(DB_[A-Z_]+)\1\s*,\s*((?:\'[^\']*(?:\\.[^\']*)*\')|(?:\"[^\"]*(?:\\.[^\"]*)*\")|(?:-?\d+(?:\.\d+)?))\s*\)\s*;/s';

		if (!preg_match_all($pattern, $config, $matches, PREG_SET_ORDER)) {
			return $result;
		}

		foreach ($matches as $match) {
			$name = $match[2];

			if (!in_array($name, $allowed, true)) {
				continue;
			}

			$result[$name] = $this->decodePhpLiteral($match[3]);
		}

		return $result;
	}

	private function decodePhpLiteral($literal) {
		$literal = trim($literal);
		$length = strlen($literal);

		if ($length >= 2 && $literal[0] === "'" && $literal[$length - 1] === "'") {
			$value = substr($literal, 1, -1);
			$value = str_replace(array('\\\\', '\\\''), array('\\', '\''), $value);

			return $value;
		}

		if ($length >= 2 && $literal[0] === '"' && $literal[$length - 1] === '"') {
			return stripcslashes(substr($literal, 1, -1));
		}

		return $literal;
	}
}
