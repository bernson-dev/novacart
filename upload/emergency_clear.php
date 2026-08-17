<?php
// emergency_clear.php

if (!headers_sent()) {
	header('Content-Type: text/html; charset=utf-8');
	header('X-Robots-Tag: noindex, nofollow', true);
	header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
	header('Pragma: no-cache');
}

$config_file = __DIR__ . '/config.php';

if (!is_file($config_file)) {
	http_response_code(500);
	echo 'Config file not found.';
	exit;
}

require_once($config_file);

if (!defined('DIR_STORAGE') || !defined('DIR_MODIFICATION')) {
	http_response_code(500);
	echo 'Required constants are not defined.';
	exit;
}

$token_file = DIR_STORAGE . 'emergency_clear_token.json';

if (!is_file($token_file)) {
	http_response_code(403);
	echo 'Emergency token not found or already used.';
	exit;
}

$data = json_decode(file_get_contents($token_file), true);

if (!is_array($data) || empty($data['hash']) || empty($data['expires'])) {
	@unlink($token_file);

	http_response_code(403);
	echo 'Invalid emergency token.';
	exit;
}

if ((int)$data['expires'] < time()) {
	@unlink($token_file);

	http_response_code(403);
	echo 'Emergency token expired.';
	exit;
}

$token = isset($_GET['token']) ? (string)$_GET['token'] : '';

if ($token === '' || !hash_equals($data['hash'], hash('sha256', $token))) {
	http_response_code(403);
	echo 'Invalid token.';
	exit;
}

$deleted = 0;
$errors = array();

$base = rtrim(DIR_MODIFICATION, '/\\') . DIRECTORY_SEPARATOR;

if (!is_dir($base)) {
	@unlink($token_file);

	echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Emergency clear</title></head><body>';
	echo '<h1>Modification cache directory not found</h1>';
	echo '</body></html>';
	exit;
}

$items = array();
$stack = array($base . '*');

while ($stack) {
	$pattern = array_shift($stack);

	foreach (glob($pattern) as $path) {
		if (is_dir($path)) {
			$stack[] = rtrim($path, '/\\') . DIRECTORY_SEPARATOR . '*';
		}

		$items[] = $path;
	}
}

rsort($items);

foreach ($items as $path) {
	$normalized = str_replace('\\', '/', $path);
	$base_normalized = str_replace('\\', '/', $base);

	if (strpos($normalized, $base_normalized) !== 0) {
		$errors[] = 'Skipped unsafe path: ' . $path;
		continue;
	}

	if (basename($path) === 'index.html') {
		continue;
	}

	if (is_file($path)) {
		if (@unlink($path)) {
			$deleted++;
		} else {
			$errors[] = 'Cannot delete file: ' . $path;
		}
	} elseif (is_dir($path)) {
		if (@rmdir($path)) {
			$deleted++;
		} else {
			$errors[] = 'Cannot delete directory: ' . $path;
		}
	}
}

@unlink($token_file);

if (defined('DIR_LOGS')) {
	@file_put_contents(
		DIR_LOGS . 'emergency-clear.log',
		date('Y-m-d H:i:s') . ' - Deleted items: ' . $deleted . PHP_EOL,
		FILE_APPEND | LOCK_EX
	);
}

echo '<!DOCTYPE html>';
echo '<html>';
echo '<head>';
echo '<meta charset="UTF-8">';
echo '<title>Emergency clear</title>';
echo '<style>';
echo 'body{font-family:Arial,sans-serif;background:#f6f8fa;color:#222;padding:30px;}';
echo '.box{max-width:760px;background:#fff;border:1px solid #ddd;border-radius:8px;padding:20px;}';
echo '.ok{color:#1e7e34;font-weight:bold;}';
echo '.err{color:#b00020;}';
echo 'code{background:#f1f1f1;padding:2px 5px;border-radius:4px;}';
echo '</style>';
echo '</head>';
echo '<body>';
echo '<div class="box">';
echo '<h1>Emergency clear completed</h1>';
echo '<p class="ok">Modification cache cleared.</p>';
echo '<p>Deleted items: <code>' . (int)$deleted . '</code></p>';

if ($errors) {
	echo '<h3 class="err">Errors</h3>';
	echo '<ul>';

	foreach ($errors as $error) {
		echo '<li>' . htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . '</li>';
	}

	echo '</ul>';
}

echo '<p>Now open admin panel and refresh modifications again.</p>';
echo '</div>';
echo '</body>';
echo '</html>';