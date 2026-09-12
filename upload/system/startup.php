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

$_SERVER['HTTPS'] = $isHttps ? 'on' : '';

// Finish installer cleanup deferred by Windows/PHP 8.x
// Отложенное удаление папки install, если она не удалилась при завершении установки
if (defined('DIR_STORAGE') && defined('DIR_SYSTEM')) {
	$install_cleanup_flag = DIR_STORAGE . 'install_cleanup.flag';

	if (is_file($install_cleanup_flag)) {
		$install_path = dirname(DIR_SYSTEM) . DIRECTORY_SEPARATOR . 'install';

		if (is_dir($install_path)) {
			try {
				$iterator = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator($install_path, RecursiveDirectoryIterator::SKIP_DOTS | RecursiveDirectoryIterator::UNIX_PATHS),
				RecursiveIteratorIterator::CHILD_FIRST
				);

				foreach ($iterator as $file) {
					$path = $file->getRealPath();
					if ($path) {
						if ($file->isDir()) {
							@chmod($path, 0777);
							@rmdir($path);
						} else {
							@chmod($path, 0777);
							@unlink($path);
						}
					}
				}

				@chmod($install_path, 0777);
				@rmdir($install_path);

				// Сбрасываем кэш состояния файлов
				clearstatcache(true, $install_path);
			} catch (Throwable $e) {
				// Ловим любые ошибки (включая Error в PHP 8.x),
				// чтобы не сломать запуск магазина при проблемах с файловой системой.
			}
		}

		// Удаляем флаг только после фактического удаления каталога.
		// Если файловая система временно не позволила удалить install,
		// следующая загрузка магазина повторит очистку.
		clearstatcache(true, $install_path);

		if (!is_dir($install_path)) {
			@unlink($install_cleanup_flag);
		}
	}
}

/**
 * Validate generated modification PHP without executing it.
 *
 * Successful and failed checks are cached inside DIR_MODIFICATION and are
 * automatically removed by the normal modification-cache cleanup.
 */
function modificationPhpSyntaxValid(string $file): bool {
	static $request_cache = array();

	$file = str_replace('\\', '/', $file);

	if (isset($request_cache[$file])) {
		return $request_cache[$file];
	}

	$mtime = @filemtime($file);
	$size = @filesize($file);

	if ($mtime === false || $size === false) {
		$request_cache[$file] = false;
		return false;
	}

	$fingerprint = sha1($file . '|' . (int)$mtime . '|' . (int)$size);
	$check_dir = rtrim(DIR_MODIFICATION, '/\\') . DIRECTORY_SEPARATOR . 'runtime-check' . DIRECTORY_SEPARATOR;
	$ok_marker = $check_dir . $fingerprint . '.ok';
	$bad_marker = $check_dir . $fingerprint . '.bad';

	if (is_file($ok_marker)) {
		$request_cache[$file] = true;
		return true;
	}

	if (is_file($bad_marker)) {
		$request_cache[$file] = false;
		return false;
	}

	$code = @file_get_contents($file);

	if ($code === false) {
		$request_cache[$file] = false;
		return false;
	}

	$valid = true;
	$error_message = '';

	try {
		token_get_all($code, TOKEN_PARSE);
	} catch (ParseError $e) {
		$valid = false;
		$error_message = $e->getMessage() . ' on line ' . $e->getLine();
	} catch (Throwable $e) {
		$valid = false;
		$error_message = get_class($e) . ': ' . $e->getMessage();
	}

	if (!is_dir($check_dir)) {
		@mkdir($check_dir, 0777, true);
	}

	if (is_dir($check_dir)) {
		@file_put_contents($valid ? $ok_marker : $bad_marker, '1', LOCK_EX);
	}

	if (!$valid && defined('DIR_LOGS')) {
		$relative = $file;
		$base = rtrim(str_replace('\\', '/', DIR_MODIFICATION), '/') . '/';

		if (strpos($relative, $base) === 0) {
			$relative = substr($relative, strlen($base));
		}

		@file_put_contents(
			DIR_LOGS . 'ocmod-runtime-error.log',
			date('Y-m-d H:i:s') . ' - Invalid generated PHP: ' . $relative . ($error_message !== '' ? ' - ' . $error_message : '') . PHP_EOL,
			FILE_APPEND | LOCK_EX
		);
	}

	$request_cache[$file] = $valid;
	return $valid;
}

// Modification Override
function modification(string $filename): string {
	$filename = str_replace('\\', '/', $filename);
	$dirApplication = str_replace('\\', '/', DIR_APPLICATION);
	$dirSystem = str_replace('\\', '/', DIR_SYSTEM);

	if (strpos($filename, $dirSystem) === 0) {
		$file = DIR_MODIFICATION . 'system/' . str_replace($dirSystem, '', $filename);
	} else {
		$appName = basename(rtrim($dirApplication, '/'));

		if ($appName === 'admin') {
			$file = DIR_MODIFICATION . 'admin/' . str_replace($dirApplication, '', $filename);
		} elseif ($appName === 'install') {
			$file = DIR_MODIFICATION . 'install/' . str_replace($dirApplication, '', $filename);
		} else {
			$file = DIR_MODIFICATION . 'catalog/' . str_replace($dirApplication, '', $filename);
		}
	}

	if (!is_file($file)) {
		return $filename;
	}

	if (strtolower(pathinfo($file, PATHINFO_EXTENSION)) === 'php' && !modificationPhpSyntaxValid($file)) {
		return $filename;
	}

	return $file;
}

/**
 * Check whether an error originated from the generated modification cache.
 */
function emergencyModificationFile($filename): bool {
	if (!defined('DIR_MODIFICATION') || !$filename) {
		return false;
	}

	$filename = str_replace('\\', '/', (string)$filename);
	$base = rtrim(str_replace('\\', '/', DIR_MODIFICATION), '/') . '/';

	return strpos($filename, $base) === 0;
}

/**
 * Create a short-lived one-time URL for emergency modification cache cleanup.
 */
function emergencyModificationClearLink(): string {
	if (!defined('DIR_STORAGE') || !is_dir(DIR_STORAGE)) {
		return '';
	}

	try {
		$token = bin2hex(random_bytes(32));
	} catch (Throwable $e) {
		return '';
	}

	$data = array(
		'hash'    => hash('sha256', $token),
		'expires' => time() + 600
	);

	$json = json_encode($data);

	if ($json === false) {
		return '';
	}

	$token_file = DIR_STORAGE . 'emergency_clear_token.json';

	if (@file_put_contents($token_file, $json, LOCK_EX) === false) {
		return '';
	}

	@chmod($token_file, 0600);

	$is_https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' && $_SERVER['HTTPS'] !== '0';

	if ($is_https && defined('HTTPS_CATALOG')) {
		$base_url = HTTPS_CATALOG;
	} elseif (!$is_https && defined('HTTP_CATALOG')) {
		$base_url = HTTP_CATALOG;
	} elseif ($is_https && defined('HTTPS_SERVER')) {
		$base_url = HTTPS_SERVER;
	} elseif (defined('HTTP_SERVER')) {
		$base_url = HTTP_SERVER;
	} elseif (!empty($_SERVER['HTTP_HOST'])) {
		$base_url = ($is_https ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/';
	} else {
		@unlink($token_file);
		return '';
	}

	return rtrim($base_url, '/') . '/emergency_clear.php?token=' . rawurlencode($token);
}

/**
 * Return a minimal recovery response when a generated OCMOD PHP file is broken.
 */
function emergencyModificationFailure(Throwable $e): void {
	$link = emergencyModificationClearLink();

	if ($link === '') {
		throw $e;
	}

	$message = get_class($e) . ': ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine();
	$is_ajax = false;

	if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower((string)$_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
		$is_ajax = true;
	} elseif (!empty($_SERVER['HTTP_ACCEPT']) && stripos((string)$_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
		$is_ajax = true;
	} elseif (!empty($_SERVER['CONTENT_TYPE']) && stripos((string)$_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
		$is_ajax = true;
	}

	while (ob_get_level() > 0) {
		@ob_end_clean();
	}

	if (!headers_sent()) {
		http_response_code(500);
	}

	if ($is_ajax) {
		if (!headers_sent()) {
			header('Content-Type: application/json; charset=utf-8');
		}

		echo json_encode(
			array(
				'success'         => false,
				'error'           => $message,
				'emergency_clear' => $link
			),
			JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE
		);
		exit;
	}

	if (!headers_sent()) {
		header('Content-Type: text/html; charset=utf-8');
	}

	echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Modification error</title></head><body>';
	echo '<div style="max-width:900px;margin:30px auto;font-family:Arial,sans-serif;line-height:1.5">';
	echo '<h1>Modification error</h1>';
	echo '<p><strong>' . htmlspecialchars(get_class($e), ENT_QUOTES, 'UTF-8') . '</strong>: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</p>';
	echo '<p><small>File: ' . htmlspecialchars($e->getFile(), ENT_QUOTES, 'UTF-8') . ' (line ' . (int)$e->getLine() . ')</small></p>';
	echo '<p>The error was detected in the generated modification cache.</p>';
	echo '<p><a href="' . htmlspecialchars($link, ENT_QUOTES, 'UTF-8') . '">Clear modification cache</a></p>';
	echo '<p><small>The recovery link is valid for 10 minutes and can be used once.</small></p>';
	echo '</div></body></html>';
	exit;
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
	try {
		require_once(DIR_SYSTEM . 'framework.php');
	} catch (Throwable $e) {
		if (emergencyModificationFile($e->getFile())) {
			emergencyModificationFailure($e);
		}

		throw $e;
	}
}
