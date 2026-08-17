<?php
// NovaCart command line installer.
// PHP 7.3+

ini_set('display_errors', '1');
error_reporting(E_ALL);

$application_root = realpath(__DIR__);
$opencart_root = realpath(__DIR__ . '/../');

if ($application_root === false || $opencart_root === false) {
	fwrite(STDERR, "FAILED! Could not resolve installer paths.\n");
	exit(1);
}

define('DIR_APPLICATION', rtrim(str_replace('\\', '/', $application_root), '/') . '/');
define('DIR_OPENCART', rtrim(str_replace('\\', '/', $opencart_root), '/') . '/');
define('DIR_SYSTEM', DIR_OPENCART . 'system/');
define('DIR_STORAGE', DIR_SYSTEM . 'storage/');
define('DIR_DATABASE', DIR_SYSTEM . 'database/');
define('DIR_LANGUAGE', DIR_APPLICATION . 'language/');
define('DIR_TEMPLATE', DIR_APPLICATION . 'view/template/');
define('DIR_CONFIG', DIR_SYSTEM . 'config/');
define('DIR_MODIFICATION', DIR_STORAGE . 'modification/');
define('DIR_CACHE', DIR_STORAGE . 'cache/');
define('DIR_DOWNLOAD', DIR_STORAGE . 'download/');
define('DIR_LOGS', DIR_STORAGE . 'logs/');
define('DIR_SESSION', DIR_STORAGE . 'session/');
define('DIR_UPLOAD', DIR_STORAGE . 'upload/');

require_once(DIR_SYSTEM . 'startup.php');

function handleError($errno, $errstr, $errfile, $errline) {
	if (error_reporting() === 0) {
		return false;
	}

	throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}

set_error_handler('handleError');

function usage() {
	echo "Usage:\n";
	echo "  php cli_install.php install [options]\n\n";
	echo "Required options:\n";
	echo "  --db_username USER\n";
	echo "  --db_password PASS\n";
	echo "  --password ADMIN_PASS\n";
	echo "  --email ADMIN_EMAIL\n";
	echo "  --http_server http://localhost/novacart/\n\n";
	echo "Optional options:\n";
	echo "  --db_hostname localhost\n";
	echo "  --db_database opencart\n";
	echo "  --db_prefix oc_\n";
	echo "  --db_driver mysqli|pdo\n";
	echo "  --db_port 3306\n";
	echo "  --username admin\n";
	echo "  --sql_dump opencart.sql\n\n";
}

function getOptions($argv) {
	$defaults = array(
		'db_hostname' => 'localhost',
		'db_database' => 'opencart',
		'db_prefix' => 'oc_',
		'db_driver' => 'mysqli',
		'db_port' => '3306',
		'username' => 'admin',
		'sql_dump' => 'opencart.sql'
	);

	$options = array();
	$total = count($argv);

	if ($total % 2 !== 0) {
		throw new Exception('Every option must have a value.');
	}

	for ($i = 0; $i < $total; $i += 2) {
		if (!preg_match('/^--([a-z0-9_]+)$/i', $argv[$i], $match)) {
			throw new Exception('Invalid option name: ' . $argv[$i]);
		}

		$options[$match[1]] = $argv[$i + 1];
	}

	$options = array_merge($defaults, $options);
	$options['http_server'] = isset($options['http_server']) ? rtrim($options['http_server'], '/') . '/' : '';

	return $options;
}

function validateOptions($options) {
	$errors = array();
	$required = array('db_hostname', 'db_username', 'db_database', 'db_prefix', 'db_driver', 'db_port', 'username', 'password', 'email', 'http_server', 'sql_dump');

	foreach ($required as $key) {
		if (!isset($options[$key]) || $options[$key] === '') {
			$errors[] = 'Missing option: --' . $key;
		}
	}

	if (!in_array($options['db_driver'], array('mysqli', 'pdo'), true)) {
		$errors[] = 'Invalid --db_driver. Supported values: mysqli, pdo.';
	}

	if (!ctype_digit((string)$options['db_port']) || (int)$options['db_port'] < 1 || (int)$options['db_port'] > 65535) {
		$errors[] = 'Invalid --db_port. Expected 1-65535.';
	}

	if (!preg_match('/^[a-z0-9_]+$/', $options['db_prefix'])) {
		$errors[] = 'Invalid --db_prefix. Use lowercase letters, digits and underscore only.';
	}

	if (!filter_var($options['email'], FILTER_VALIDATE_EMAIL)) {
		$errors[] = 'Invalid --email.';
	}

	if (!preg_match('#^https?://#i', $options['http_server'])) {
		$errors[] = 'Invalid --http_server. Expected http:// or https:// URL.';
	}

	if (basename($options['sql_dump']) !== $options['sql_dump'] || strtolower(pathinfo($options['sql_dump'], PATHINFO_EXTENSION)) !== 'sql') {
		$errors[] = 'Invalid --sql_dump.';
	}

	return $errors;
}

function checkRequirements($driver) {
	$errors = array();

	if (version_compare(PHP_VERSION, '7.3.0', '<')) {
		$errors[] = 'PHP 7.3 or newer is required.';
	}

	if (ini_get('session.auto_start')) {
		$errors[] = 'session.auto_start must be disabled.';
	}

	if ($driver === 'mysqli' && !extension_loaded('mysqli')) {
		$errors[] = 'MySQLi extension is required for mysqli driver.';
	}

	if ($driver === 'pdo' && (!extension_loaded('pdo') || !extension_loaded('pdo_mysql'))) {
		$errors[] = 'PDO and pdo_mysql extensions are required for pdo driver.';
	}

	foreach (array('curl', 'zlib') as $extension) {
		if (!extension_loaded($extension)) {
			$errors[] = $extension . ' extension is required.';
		}
	}

	if (!extension_loaded('gd') && !extension_loaded('imagick')) {
		$errors[] = 'GD or Imagick extension is required.';
	}

	if (!function_exists('openssl_encrypt')) {
		$errors[] = 'OpenSSL extension is required.';
	}

	foreach (array(DIR_STORAGE, DIR_CACHE, DIR_DOWNLOAD, DIR_LOGS, DIR_MODIFICATION, DIR_SESSION, DIR_UPLOAD) as $directory) {
		if (!is_dir($directory)) {
			if (!@mkdir($directory, 0775, true) && !is_dir($directory)) {
				$errors[] = 'Could not create directory: ' . $directory;
				continue;
			}
		}

		if (!is_writable($directory)) {
			$errors[] = 'Directory is not writable: ' . $directory;
		}
	}

	foreach (array(DIR_OPENCART . 'config.php', DIR_OPENCART . 'admin/config.php') as $file) {
		if (!file_exists($file)) {
			if (@file_put_contents($file, '') === false) {
				$errors[] = 'Could not create config file: ' . $file;
				continue;
			}
		}

		if (!is_writable($file)) {
			$errors[] = 'Config file is not writable: ' . $file;
		}
	}

	return $errors;
}

function getSqlDumpFile($name) {
	if (!is_string($name) || $name === '' || basename($name) !== $name || strtolower(pathinfo($name, PATHINFO_EXTENSION)) !== 'sql') {
		throw new Exception('Invalid SQL dump file.');
	}

	$base = realpath(DIR_APPLICATION);
	$file = realpath(DIR_APPLICATION . $name);

	if ($base === false || $file === false || dirname($file) !== $base || !is_file($file) || !is_readable($file)) {
		throw new Exception('Could not load SQL dump: ' . $name);
	}

	return $file;
}

function setupDatabase($data) {
	$db = new DB(
		$data['db_driver'],
		html_entity_decode($data['db_hostname'], ENT_QUOTES, 'UTF-8'),
		html_entity_decode($data['db_username'], ENT_QUOTES, 'UTF-8'),
		html_entity_decode($data['db_password'], ENT_QUOTES, 'UTF-8'),
		html_entity_decode($data['db_database'], ENT_QUOTES, 'UTF-8'),
		$data['db_port']
	);

	$file = getSqlDumpFile($data['sql_dump']);
	$db->query("SET NAMES 'utf8mb4'");
	$db->query("SET CHARACTER SET utf8mb4");
	$db->query("SET @@session.sql_mode = ''");

	$handle = fopen($file, 'r');
	if (!$handle) {
		throw new Exception('Could not open SQL dump: ' . $file);
	}

	$sql = '';
	$in_block_comment = false;

	try {
		while (($line = fgets($handle)) !== false) {
			$line = trim($line);

			if ($line === '') {
				continue;
			}

			if ($in_block_comment) {
				if (strpos($line, '*/') !== false) {
					$in_block_comment = false;
				}
				continue;
			}

			if (strpos($line, '/*') === 0) {
				if (strpos($line, '*/') === false) {
					$in_block_comment = true;
				}
				continue;
			}

			if (strpos($line, '--') === 0 || strpos($line, '#') === 0) {
				continue;
			}

			$sql .= $line . "\n";

			if (substr($line, -1) === ';') {
				$db->query(str_replace('`oc_', '`' . $data['db_prefix'], $sql));
				$sql = '';
			}
		}
	} finally {
		fclose($handle);
	}

	$sql = trim($sql);
	if ($sql !== '') {
		$db->query(str_replace('`oc_', '`' . $data['db_prefix'], $sql));
	}

	$db->query("DELETE FROM `" . $data['db_prefix'] . "user` WHERE `user_id` = '1'");
	$password_hash = password_hash(html_entity_decode($data['password'], ENT_QUOTES, 'UTF-8'), PASSWORD_DEFAULT);
	$db->query(
		"INSERT INTO `" . $data['db_prefix'] . "user` SET
			user_id = '1',
			user_group_id = '1',
			username = '" . $db->escape($data['username']) . "',
			password = '" . $db->escape($password_hash) . "',
			firstname = 'Super',
			lastname = 'Admin',
			email = '" . $db->escape($data['email']) . "',
			status = '1',
			date_added = NOW()"
	);

	$db->query("DELETE FROM `" . $data['db_prefix'] . "setting` WHERE `key` = 'config_email'");
	$db->query("INSERT INTO `" . $data['db_prefix'] . "setting` SET `code` = 'config', `key` = 'config_email', `value` = '" . $db->escape($data['email']) . "'");

	$db->query("DELETE FROM `" . $data['db_prefix'] . "setting` WHERE `key` = 'config_encryption'");
	$db->query("INSERT INTO `" . $data['db_prefix'] . "setting` SET `code` = 'config', `key` = 'config_encryption', `value` = '" . $db->escape(token(1024)) . "'");

	$db->query("UPDATE `" . $data['db_prefix'] . "product` SET `viewed` = '0'");
	$db->query("DELETE FROM `" . $data['db_prefix'] . "api`");
	$db->query("INSERT INTO `" . $data['db_prefix'] . "api` SET `username` = 'Default', `key` = '" . $db->escape(token(256)) . "', `status` = '1', `date_added` = NOW(), `date_modified` = NOW()");
	$api_id = $db->getLastId();

	$db->query("DELETE FROM `" . $data['db_prefix'] . "setting` WHERE `key` = 'config_api_id'");
	$db->query("INSERT INTO `" . $data['db_prefix'] . "setting` SET `code` = 'config', `key` = 'config_api_id', `value` = '" . (int)$api_id . "'");
}

function phpDefine($name, $value) {
	return "define('" . $name . "', " . var_export((string)$value, true) . ");\n";
}

function databaseConfig($options) {
	$output = "// DB\n";
	$output .= phpDefine('DB_DRIVER', $options['db_driver']);
	$output .= phpDefine('DB_HOSTNAME', $options['db_hostname']);
	$output .= phpDefine('DB_USERNAME', $options['db_username']);
	$output .= phpDefine('DB_PASSWORD', $options['db_password']);
	$output .= phpDefine('DB_DATABASE', $options['db_database']);
	$output .= phpDefine('DB_PREFIX', $options['db_prefix']);
	$output .= phpDefine('DB_PORT', $options['db_port']);

	return $output;
}

function catalogConfig($options) {
	$output = "<?php\n";
	$output .= "// HTTP\n" . phpDefine('HTTP_SERVER', $options['http_server']);
	$output .= "// HTTPS\n" . phpDefine('HTTPS_SERVER', $options['http_server']);
	$output .= "// DIR\n";
	$output .= phpDefine('DIR_APPLICATION', DIR_OPENCART . 'catalog/');
	$output .= phpDefine('DIR_SYSTEM', DIR_OPENCART . 'system/');
	$output .= phpDefine('DIR_IMAGE', DIR_OPENCART . 'image/');
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
	$output .= databaseConfig($options);

	return $output;
}

function adminConfig($options) {
	$output = "<?php\n";
	$output .= "// HTTP\n";
	$output .= phpDefine('HTTP_SERVER', $options['http_server'] . 'admin/');
	$output .= phpDefine('HTTP_CATALOG', $options['http_server']);
	$output .= "// HTTPS\n";
	$output .= phpDefine('HTTPS_SERVER', $options['http_server'] . 'admin/');
	$output .= phpDefine('HTTPS_CATALOG', $options['http_server']);
	$output .= "// DIR\n";
	$output .= phpDefine('DIR_APPLICATION', DIR_OPENCART . 'admin/');
	$output .= phpDefine('DIR_SYSTEM', DIR_OPENCART . 'system/');
	$output .= phpDefine('DIR_IMAGE', DIR_OPENCART . 'image/');
	$output .= "define('DIR_STORAGE', DIR_SYSTEM . 'storage/');\n";
	$output .= phpDefine('DIR_CATALOG', DIR_OPENCART . 'catalog/');
	$output .= "define('DIR_LANGUAGE', DIR_APPLICATION . 'language/');\n";
	$output .= "define('DIR_TEMPLATE', DIR_APPLICATION . 'view/template/');\n";
	$output .= "define('DIR_CONFIG', DIR_SYSTEM . 'config/');\n";
	$output .= "define('DIR_CACHE', DIR_STORAGE . 'cache/');\n";
	$output .= "define('DIR_DOWNLOAD', DIR_STORAGE . 'download/');\n";
	$output .= "define('DIR_LOGS', DIR_STORAGE . 'logs/');\n";
	$output .= "define('DIR_MODIFICATION', DIR_STORAGE . 'modification/');\n";
	$output .= "define('DIR_SESSION', DIR_STORAGE . 'session/');\n";
	$output .= "define('DIR_UPLOAD', DIR_STORAGE . 'upload/');\n\n";
	$output .= databaseConfig($options);
	$output .= "// OpenCart API\n" . phpDefine('OPENCART_SERVER', 'https://www.opencart.com/');

	return $output;
}

function writeConfigFile($file, $content) {
	$bytes = @file_put_contents($file, $content, LOCK_EX);

	if ($bytes === false || $bytes !== strlen($content)) {
		throw new Exception('Could not write config file: ' . $file);
	}
}

function writeConfigFiles($options) {
	writeConfigFile(DIR_OPENCART . 'config.php', catalogConfig($options));
	writeConfigFile(DIR_OPENCART . 'admin/config.php', adminConfig($options));
}

function installNovaCart($options) {
	$errors = checkRequirements($options['db_driver']);
	if ($errors) {
		throw new Exception("Pre-installation check failed:\n - " . implode("\n - ", $errors));
	}

	setupDatabase($options);
	writeConfigFiles($options);
}

$argv = isset($_SERVER['argv']) ? $_SERVER['argv'] : array();
array_shift($argv);
$subcommand = array_shift($argv);

switch ($subcommand) {
	case 'install':
		try {
			$options = getOptions($argv);
			$errors = validateOptions($options);

			if ($errors) {
				throw new Exception("Invalid options:\n - " . implode("\n - ", $errors));
			}

			define('HTTP_OPENCART', $options['http_server']);
			installNovaCart($options);

			echo "SUCCESS! NovaCart successfully installed.\n";
			echo 'Store link: ' . $options['http_server'] . "\n";
			echo 'Admin link: ' . $options['http_server'] . "admin/\n";
		} catch (Throwable $e) {
			fwrite(STDERR, 'FAILED! ' . $e->getMessage() . "\n");
			exit(1);
		}
		break;

	case 'usage':
	default:
		usage();
		break;
}
