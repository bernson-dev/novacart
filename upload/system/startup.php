<?php
// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', '0');

// Check PHP Version (7.3+)
if (PHP_VERSION_ID < 70300) {
	exit('PHP 7.3+ Required');
}

// Timezone fallback
if (!ini_get('date.timezone')) {
	date_default_timezone_set('UTC');
}

// Windows IIS Compatibility
if (!isset($_SERVER['DOCUMENT_ROOT'])) {
	if (!empty($_SERVER['SCRIPT_FILENAME'])) {
		$_SERVER['DOCUMENT_ROOT'] = str_replace('\\', '/', dirname($_SERVER['SCRIPT_FILENAME']));
	} elseif (!empty($_SERVER['PATH_TRANSLATED'])) {
		$_SERVER['DOCUMENT_ROOT'] = str_replace('\\', '/', dirname(str_replace('\\\\', '\\', $_SERVER['PATH_TRANSLATED'])));
	}
}

// REQUEST_URI fallback
if (!isset($_SERVER['REQUEST_URI'])) {
	$_SERVER['REQUEST_URI'] = ltrim((string)$_SERVER['PHP_SELF'], '/');

	if (!empty($_SERVER['QUERY_STRING'])) {
		$_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
	}
}

// HTTP_HOST fallback
if (!isset($_SERVER['HTTP_HOST'])) {
	$host = getenv('HTTP_HOST');

	if ($host !== false && $host !== '') {
		$_SERVER['HTTP_HOST'] = $host;
	}
}

// HTTPS detection
$isHttps = (
	(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' && $_SERVER['HTTPS'] !== '0')
	|| (!empty($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443)
	|| (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower((string)$_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https')
	|| (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && strtolower((string)$_SERVER['HTTP_X_FORWARDED_SSL']) === 'on')
);

/*if ($isHttps) {
	$_SERVER['HTTPS'] = 'on';
} else {
	unset($_SERVER['HTTPS']);
}*/

$_SERVER['HTTPS'] = $isHttps ? 'on' : '';

// Modification Override
function modification(string $filename): string {
	$filename = str_replace('\\', '/', $filename);
	$dirApplication = str_replace('\\', '/', DIR_APPLICATION);
	$dirSystem = str_replace('\\', '/', DIR_SYSTEM);

	if (strpos($filename, $dirSystem) === 0) {
		$file = DIR_MODIFICATION . 'system/' . str_replace($dirSystem, '', $filename);
		return is_file($file) ? $file : $filename;
	}

	$appName = basename(rtrim($dirApplication, '/'));

	if ($appName === 'admin') {
		$file = DIR_MODIFICATION . 'admin/' . str_replace($dirApplication, '', $filename);
	} elseif ($appName === 'install') {
		$file = DIR_MODIFICATION . 'install/' . str_replace($dirApplication, '', $filename);
	} else {
		$file = DIR_MODIFICATION . 'catalog/' . str_replace($dirApplication, '', $filename);
	}

	return is_file($file) ? $file : $filename;
}

// Autoloader
if (defined('DIR_STORAGE') && is_file(DIR_STORAGE . 'vendor/autoload.php')) {
	require_once(DIR_STORAGE . 'vendor/autoload.php');
}

spl_autoload_register(function ($class): void {
	$file = DIR_SYSTEM . 'library/' . str_replace('\\', '/', strtolower($class)) . '.php';

	if (is_file($file)) {
		include_once(modification($file));
	}
});

// Engine
require_once(modification(DIR_SYSTEM . 'engine/action.php'));
require_once(modification(DIR_SYSTEM . 'engine/controller.php'));
require_once(modification(DIR_SYSTEM . 'engine/event.php'));
require_once(modification(DIR_SYSTEM . 'engine/router.php'));
require_once(modification(DIR_SYSTEM . 'engine/loader.php'));
require_once(modification(DIR_SYSTEM . 'engine/model.php'));
require_once(modification(DIR_SYSTEM . 'engine/registry.php'));
require_once(modification(DIR_SYSTEM . 'engine/proxy.php'));

// Helper
require_once(modification(DIR_SYSTEM . 'helper/general.php'));
require_once(modification(DIR_SYSTEM . 'helper/utf8.php'));

// Start
function start($application_config): void {
	require_once(DIR_SYSTEM . 'framework.php');
}