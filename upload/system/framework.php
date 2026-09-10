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

/**
* Normalize PHP timezone identifier.
*/
if (!function_exists('frameworkNormalizeTimezone')) {
	function frameworkNormalizeTimezone($timezone) {
		static $identifiers = null;

		if ($identifiers === null) {
			$identifiers = array_flip(timezone_identifiers_list());
		}

		$timezone = trim((string)$timezone);

		if ($timezone !== '' && isset($identifiers[$timezone])) {
			return $timezone;
		}

		/*
		 * Resolve renamed identifiers by availability in the installed
		 * timezone database instead of relying on the PHP version.
		 */
		$aliases = array(
			'Europe/Kyiv'      => 'Europe/Kiev',
			'Europe/Kiev'      => 'Europe/Kyiv',
			'Asia/Kolkata'     => 'Asia/Calcutta',
			'Asia/Calcutta'    => 'Asia/Kolkata',
			'Asia/Kathmandu'   => 'Asia/Katmandu',
			'Asia/Katmandu'    => 'Asia/Kathmandu',
			'Pacific/Chuuk'    => 'Pacific/Truk',
			'Pacific/Truk'     => 'Pacific/Chuuk',
			'Pacific/Pohnpei'  => 'Pacific/Ponape',
			'Pacific/Ponape'   => 'Pacific/Pohnpei'
		);

		if (isset($aliases[$timezone]) && isset($identifiers[$aliases[$timezone]])) {
			return $aliases[$timezone];
		}

		return 'UTC';
	}
}

/**
* Detect AJAX / JSON request.
*
* Standard OpenCart AJAX requests made through jQuery send:
* X-Requested-With: XMLHttpRequest
*
* Accept and Content-Type checks also cover fetch()/JSON requests
* where X-Requested-With may be absent.
*/
if (!function_exists('frameworkIsAjaxRequest')) {
	function frameworkIsAjaxRequest() {
		if (
		isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
		strtolower((string)$_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
		) {
			return true;
		}

		if (
		isset($_SERVER['HTTP_ACCEPT']) &&
		stripos((string)$_SERVER['HTTP_ACCEPT'], 'application/json') !== false
		) {
			return true;
		}

		if (
		isset($_SERVER['CONTENT_TYPE']) &&
		stripos((string)$_SERVER['CONTENT_TYPE'], 'application/json') !== false
		) {
			return true;
		}

		return false;
	}
}

/**
* Send critical error response.
*
* AJAX requests always receive JSON and HTTP 500.
* Regular requests may be redirected to configured error_page.
*/
if (!function_exists('frameworkSendErrorResponse')) {
	function frameworkSendErrorResponse($config, $message = 'A critical system error has occurred. Please try again later.') {
		if (frameworkIsAjaxRequest()) {
			if (!headers_sent()) {
				http_response_code(500);
				header('Content-Type: application/json; charset=utf-8');
			}

			echo json_encode(
			array(
			'success' => false,
			'error'   => $message
			),
			JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
			);

			exit();
		}

		if (!headers_sent()) {
			$error_page = $config->get('error_page');

			if ($error_page) {
				header('Location: ' . $error_page, true, 302);
			} else {
				http_response_code(500);
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
date_default_timezone_set(
frameworkNormalizeTimezone(
$config->get('config_timezone')
?: $config->get('date_timezone')
?: 'UTC'
)
);


// Log
$log = new Log($config->get('error_filename'));
$registry->set('log', $log);


// Error Handler
set_error_handler(function (int $code, string $message, string $file, int $line) use ($log, $config) {
/*
* Respect current error_reporting().
*
* This also correctly handles errors suppressed using @.
*/
	if (!(error_reporting() & $code)) {
		return false;
	}

	switch ($code) {
		case E_NOTICE:
		case E_USER_NOTICE:
		case E_STRICT:
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

		case E_USER_ERROR:
			$error = 'Fatal Error';
			break;

		default:
			$error = 'Unknown';
			break;
	}

	// Log error
	if ($config->get('error_log')) {
		$log->write(
		'PHP ' . $error . ': ' .
		$message .
		' in ' . $file .
		' on line ' . $line
		);
	}

/*
* IMPORTANT:
*
* Never print HTML error blocks into AJAX/JSON responses.
*
* Otherwise a valid response such as:
*
* {"success":true}
*
* becomes:
*
* <div>Warning...</div>{"success":true}
*
* and JavaScript can no longer parse it as JSON.
*/
	if ($config->get('error_display') && !frameworkIsAjaxRequest()) {
		echo ErrorRenderer::render(
		$error,
		$message,
		$file,
		$line
		);

		return true;
	}

/*
* Notice / Warning / Deprecated errors are logged,
* but do not terminate the application.
*
* Critical user/recoverable errors return HTTP 500.
*/
	if (
	$code == E_USER_ERROR ||
	$code == E_RECOVERABLE_ERROR
	) {
		$error_message = 'A critical system error has occurred. Please try again later.';

		if ($config->get('error_display')) {
			$error_message =
			$error . ': ' .
			$message .
			' in ' . $file .
			' on line ' . $line;
		}

		frameworkSendErrorResponse($config, $error_message);
	}

	return true;
});


// Exception Handler
set_exception_handler(function (\Throwable $e) use ($log, $config) {
	$message =
	get_class($e) . ': ' .
	$e->getMessage() .
	' in ' . $e->getFile() .
	' on line ' . $e->getLine();

	// Log exception
	if ($config->get('error_log')) {
		$log->write($message);
	}

/*
* AJAX must always receive JSON instead of HTML.
*/
	if (frameworkIsAjaxRequest()) {
		if ($config->get('error_display')) {
			frameworkSendErrorResponse($config, $message);
		} else {
			frameworkSendErrorResponse($config);
		}
	}

/*
* Normal browser request.
*/
	if ($config->get('error_display')) {
		if (!headers_sent()) {
			http_response_code(500);
		}

		echo ErrorRenderer::renderException($e);
	} else {
		frameworkSendErrorResponse($config);
	}
});


// Fatal Error Handler
register_shutdown_function(function () use ($log, $config) {
	$error = error_get_last();

	if (!$error) {
		return;
	}

/*
* Errors which cannot be handled by set_error_handler().
*/
	$fatal_errors = array(
	E_ERROR,
	E_PARSE,
	E_CORE_ERROR,
	E_COMPILE_ERROR
	);

	if (!in_array($error['type'], $fatal_errors, true)) {
		return;
	}

	$message = isset($error['message'])
	? (string)$error['message']
	: 'Unknown fatal error';

	$file = isset($error['file'])
	? (string)$error['file']
	: '';

	$line = isset($error['line'])
	? (int)$error['line']
	: 0;

	if ($config->get('error_log')) {
		$log->write(
		'PHP Fatal Error: ' .
		$message .
		' in ' . $file .
		' on line ' . $line
		);
	}

/*
* AJAX:
* return clean JSON error response where possible.
*/
	if (frameworkIsAjaxRequest()) {
		$error_message = 'A critical system error has occurred. Please try again later.';

		if ($config->get('error_display')) {
			$error_message =
			'Fatal Error: ' .
			$message .
			' in ' . $file .
			' on line ' . $line;
		}

		frameworkSendErrorResponse(
		$config,
		$error_message
		);
	}

/*
* Normal browser request.
*/
	if ($config->get('error_display')) {
		if (!headers_sent()) {
			http_response_code(500);
		}

		echo ErrorRenderer::render(
		'Fatal Error',
		$message,
		$file,
		$line
		);

		return;
	}

/*
* We cannot safely redirect here if output has already started.
*/
	if (!headers_sent()) {
		$error_page = $config->get('error_page');

		if ($error_page) {
			header('Location: ' . $error_page, true, 302);

			return;
		}

		http_response_code(500);
		header('Content-Type: text/plain; charset=utf-8');
	}

	echo 'A critical system error has occurred. Please try again later.';
});


// Event
$event = new Event($registry);
$registry->set('event', $event);


// Event Register
if ($config->has('action_event')) {
	foreach ($config->get('action_event') as $key => $value) {
		foreach ($value as $priority => $action) {
			$event->register(
			$key,
			new Action($action),
			$priority
			);
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

$response->addHeader(
'Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0'
);

$response->setCompression(
$config->get('config_compression')
);

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

}


// Session
$session = new Session(
$config->get('session_engine'),
$registry
);

$registry->set('session', $session);


if ($config->get('session_autostart')) {
	$session_name = $config->get('session_name')
	?: 'OCSESSID';

	$session_id = isset($_COOKIE[$session_name])
	? $_COOKIE[$session_name]
	: '';

	$session->start($session_id);

	$cookie_lifetime = (int)ini_get(
	'session.cookie_lifetime'
	);

/*
* Detect HTTPS directly or behind reverse proxy.
*/
	$is_ssl = false;

	if (
	isset($_SERVER['HTTPS']) &&
	(
	strtolower((string)$_SERVER['HTTPS']) === 'on' ||
	(string)$_SERVER['HTTPS'] === '1'
	)
	) {
		$is_ssl = true;
	} elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
		$forwarded_proto = explode(
		',',
		(string)$_SERVER['HTTP_X_FORWARDED_PROTO']
		);

		$forwarded_proto = strtolower(
		trim($forwarded_proto[0])
		);

		if ($forwarded_proto === 'https') {
			$is_ssl = true;
		}
	}

	setcookie(
	$session_name,
	$session->getId(),
	array(
	'expires'  => $cookie_lifetime
	? time() + $cookie_lifetime
	: 0,
	'path'     => ini_get('session.cookie_path')
	?: '/',
	'domain'   => ini_get('session.cookie_domain')
	?: '',
	'secure'   => $is_ssl,
	'httponly' => true,
	'samesite' => 'Lax'
	)
	);
}


// Cache
$registry->set(
'cache',
new Cache(
$config->get('cache_engine'),
$config->get('cache_expire')
)
);


// Url
if ($config->get('url_autostart')) {
	$registry->set(
	'url',
	new Url(
	$config->get('site_url'),
	$config->get('site_ssl')
	)
	);
}


// Language
$language = new Language(
$config->get('language_directory')
);

$registry->set('language', $language);


// Document
$registry->set(
'document',
new Document()
);


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
		$route->addPreAction(
		new Action($value)
		);
	}
}


// Dispatch
$route->dispatch(
new Action(
$config->get('action_router')
),
new Action(
$config->get('action_error')
)
);


// Output
$response->output();