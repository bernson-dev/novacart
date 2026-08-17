<?php
// ErrorRenderer
class ErrorRenderer {
	public static function render(string $errorType, string $message, string $file, int $line): string {
		$styles = array(
		'Fatal Error'       => 'background:#ff4d4d; color:white;',
		'Recoverable Error' => 'background:#ff4d4d; color:white;',
		'Warning'           => 'background:#ffd633; color:black;',
		'Notice'            => 'background:#66ccff; color:black;',
		'Deprecated'        => 'background:#cccccc; color:black;',
		'Unknown'           => 'background:#999999; color:white;'
		);

		$style = isset($styles[$errorType]) ? $styles[$errorType] : $styles['Unknown'];

		$errorType = htmlspecialchars($errorType, ENT_QUOTES, 'UTF-8');
		$message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
		$file = htmlspecialchars($file, ENT_QUOTES, 'UTF-8');

		return '<div style="' . $style . ' padding:10px; margin:5px; font-family:monospace;">
			<strong>' . $errorType . '</strong><br>
			' . $message . '<br>
			<small>File: ' . $file . ' (line ' . (int)$line . ')</small>
		</div>';
	}

	public static function renderException(\Throwable $e): string {
		$class = htmlspecialchars(get_class($e), ENT_QUOTES, 'UTF-8');
		$message = htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
		$file = htmlspecialchars($e->getFile(), ENT_QUOTES, 'UTF-8');

		return '<div style="background:#ff4d4d; color:white; padding:10px; margin:5px; font-family:monospace;">
			<strong>Exception: ' . $class . '</strong><br>
			' . $message . '<br>
			<small>File: ' . $file . ' (line ' . (int)$e->getLine() . ')</small>
		</div>';
	}
}

if (!function_exists('frameworkNormalizeTimezone')) {
	function frameworkNormalizeTimezone($timezone) {
		$timezone = trim((string)$timezone);

		if ($timezone && in_array($timezone, timezone_identifiers_list(), true)) {
			return $timezone;
		}

		$aliases = array(
		'Europe/Kyiv'     => 'Europe/Kiev',
		'Europe/Uzhgorod' => 'Europe/Uzhgorod',
		'Europe/Zaporozhye' => 'Europe/Zaporozhye',
		'Asia/Kolkata'    => 'Asia/Calcutta',
		'Asia/Kathmandu'  => 'Asia/Katmandu',
		'Pacific/Chuuk'   => 'Pacific/Truk',
		'Pacific/Pohnpei' => 'Pacific/Ponape'
		);

		if (isset($aliases[$timezone]) && in_array($aliases[$timezone], timezone_identifiers_list(), true)) {
			return $aliases[$timezone];
		}

		return 'UTC';
	}
}

if (!function_exists('frameworkSendErrorResponse')) {
	function frameworkSendErrorResponse($config, $message = 'A critical system error has occurred. Please try again later.') {
		if (!headers_sent()) {
			$error_page = $config->get('error_page');

			if ($error_page) {
				header('Location: ' . $error_page, true, 302);
			} else {
				header('HTTP/1.1 500 Internal Server Error');
				header('Content-Type: text/plain; charset=utf-8');
				echo $message;
			}
		} else {
			echo $message;
		}

		exit();
	}
}

// Registry
$registry = new Registry();

// Config
$config = new Config();

// Load the default config
$config->load('default');
$config->load($application_config ?? 'application');
$registry->set('config', $config);

// Set the default time zone
date_default_timezone_set(frameworkNormalizeTimezone($config->get('config_timezone') ?: $config->get('date_timezone') ?: 'UTC'));

// Log
$log = new Log($config->get('error_filename'));
$registry->set('log', $log);

// Error Handler
set_error_handler(function (int $code, string $message, string $file, int $line) use ($log, $config) {
	if (!(error_reporting() & $code)) {
		return false;
	}

	switch ($code) {
		case E_NOTICE:
		case E_USER_NOTICE:
			$error = 'Notice';
			break;

		case E_WARNING:
		case E_USER_WARNING:
			$error = 'Warning';
			break;

		case E_DEPRECATED:
		case E_USER_DEPRECATED:
			$error = 'Deprecated';
			break;

		case E_RECOVERABLE_ERROR:
			$error = 'Recoverable Error';
			break;

		case E_ERROR:
		case E_USER_ERROR:
			$error = 'Fatal Error';
			break;

		default:
			$error = 'Unknown';
			break;
	}

	if ($config->get('error_log')) {
		$log->write('PHP ' . $error . ': ' . $message . ' in ' . $file . ' on line ' . $line);
	}

	if ($config->get('error_display')) {
		echo ErrorRenderer::render($error, $message, $file, $line);

		return true;
	}

	// В production не ломаем сайт из-за обычных Notice / Warning / Deprecated.
	// Редирект или 500 только для реально критичных ошибок.
	if ($code == E_ERROR || $code == E_USER_ERROR || $code == E_RECOVERABLE_ERROR) {
		frameworkSendErrorResponse($config);
	}

	return true;
});

// Exception Handler
set_exception_handler(function (\Throwable $e) use ($log, $config) {
	$message = get_class($e) . ': ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine();

	if ($config->get('error_log')) {
		$log->write($message);
	}

	if ($config->get('error_display')) {
		echo ErrorRenderer::renderException($e);
	} else {
		frameworkSendErrorResponse($config);
	}
});

// Event
$event = new Event($registry);
$registry->set('event', $event);

// Event Register
if ($config->has('action_event')) {
	foreach ($config->get('action_event') as $key => $value) {
		foreach ($value as $priority => $action) {
			$event->register($key, new Action($action), $priority);
		}
	}
}

// Loader
$loader = new Loader($registry);
$registry->set('load', $loader);

// Request
$request = new Request();
$registry->set('request', $request);

// Response
$response = new Response();
$response->addHeader('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
$response->setCompression($config->get('config_compression'));
$registry->set('response', $response);

// Database
if ($config->get('db_autostart')) {
	$db = new DB(
	$config->get('db_engine'),
	$config->get('db_hostname'),
	$config->get('db_username'),
	$config->get('db_password'),
	$config->get('db_database'),
	$config->get('db_port')
	);

	$registry->set('db', $db);

	// Set time zone from store settings
	$query = $db->query("SELECT value FROM " . DB_PREFIX . "setting WHERE `key` = 'config_timezone' AND store_id = '0' LIMIT 1");

	if ($query->num_rows && $query->row['value']) {
		date_default_timezone_set(frameworkNormalizeTimezone($query->row['value']));
	}

	// Sync PHP and DB time zones
	$db->query("SET time_zone = '" . $db->escape(date('P')) . "'");
}

// Session
$session = new Session($config->get('session_engine'), $registry);
$registry->set('session', $session);

if ($config->get('session_autostart')) {
	$session_name = $config->get('session_name') ?: 'OCSESSID';
	$session_id = isset($_COOKIE[$session_name]) ? $_COOKIE[$session_name] : '';

	$session->start($session_id);

	$cookie_lifetime = (int)ini_get('session.cookie_lifetime');

	$is_ssl = (
	isset($_SERVER['HTTPS']) &&
	($_SERVER['HTTPS'] === 'on' || $_SERVER['HTTPS'] === '1')
	) || (
	isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
	$_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https'
	);

	setcookie($session_name, $session->getId(), array(
	'expires'  => $cookie_lifetime ? time() + $cookie_lifetime : 0,
	'path'     => ini_get('session.cookie_path') ?: '/',
	'domain'   => ini_get('session.cookie_domain') ?: '',
	'secure'   => $is_ssl,
	'httponly' => true,
	'samesite' => 'Lax'
	));
}

// Cache
$registry->set('cache', new Cache($config->get('cache_engine'), $config->get('cache_expire')));

// Url
if ($config->get('url_autostart')) {
	$registry->set('url', new Url($config->get('site_url'), $config->get('site_ssl')));
}

// Language
$language = new Language($config->get('language_directory'));
$registry->set('language', $language);

// Document
$registry->set('document', new Document());

// Config Autoload
if ($config->has('config_autoload')) {
	foreach ($config->get('config_autoload') as $value) {
		$loader->config($value);
	}
}

// Language Autoload
if ($config->has('language_autoload')) {
	foreach ($config->get('language_autoload') as $value) {
		$loader->language($value);
	}
}

// Library Autoload
if ($config->has('library_autoload')) {
	foreach ($config->get('library_autoload') as $value) {
		$loader->library($value);
	}
}

// Model Autoload
if ($config->has('model_autoload')) {
	foreach ($config->get('model_autoload') as $value) {
		$loader->model($value);
	}
}

// Route
$route = new Router($registry);

// Pre Actions
if ($config->has('action_pre_action')) {
	foreach ($config->get('action_pre_action') as $value) {
		$route->addPreAction(new Action($value));
	}
}

// Dispatch
$route->dispatch(new Action($config->get('action_router')), new Action($config->get('action_error')));

// Output
$response->output();