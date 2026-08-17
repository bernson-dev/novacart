<?php
// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Protocol
if (
	(isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] === 'on' || $_SERVER['HTTPS'] === '1'))
	|| (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443)
	|| (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https')
	|| (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && strtolower($_SERVER['HTTP_X_FORWARDED_SSL']) === 'on')
) {
	$protocol = 'https://';
} else {
	$protocol = 'http://';
}

$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
$script_name = isset($_SERVER['SCRIPT_NAME']) ? str_replace('\\', '/', $_SERVER['SCRIPT_NAME']) : '/install/index.php';
$install_path = rtrim(str_replace('\\', '/', dirname($script_name)), '/');
$opencart_path = rtrim(str_replace('\\', '/', dirname($install_path)), '/');

if ($install_path === '.' || $install_path === '/') {
	$install_path = '';
}

if ($opencart_path === '.' || $opencart_path === '/') {
	$opencart_path = '';
}

define('HTTP_SERVER', $protocol . $host . $install_path . '/');
define('HTTP_OPENCART', $protocol . $host . $opencart_path . '/');

// DIR
$opencart_root = realpath(__DIR__ . '/../');
if ($opencart_root === false) {
	die('Error: Could not resolve OpenCart root directory.');
}

define('DIR_OPENCART', rtrim(str_replace('\\', '/', $opencart_root), '/') . '/');
define('DIR_APPLICATION', DIR_OPENCART . 'install/');
define('DIR_SYSTEM', DIR_OPENCART . 'system/');
define('DIR_IMAGE', DIR_OPENCART . 'image/');
define('DIR_STORAGE', DIR_SYSTEM . 'storage/');
define('DIR_LANGUAGE', DIR_APPLICATION . 'language/');
define('DIR_TEMPLATE', DIR_APPLICATION . 'view/template/');
define('DIR_DATABASE', DIR_SYSTEM . 'database/');
define('DIR_CONFIG', DIR_SYSTEM . 'config/');
define('DIR_CACHE', DIR_STORAGE . 'cache/');
define('DIR_DOWNLOAD', DIR_STORAGE . 'download/');
define('DIR_LOGS', DIR_STORAGE . 'logs/');
define('DIR_MODIFICATION', DIR_STORAGE . 'modification/');
define('DIR_SESSION', DIR_STORAGE . 'session/');
define('DIR_UPLOAD', DIR_STORAGE . 'upload/');

// Startup
require_once(DIR_SYSTEM . 'startup.php');

start('install');
