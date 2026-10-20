<?php
/**
 * Generate the curated NovaCart core integrity manifest.
 *
 * PHP 7.3+ compatible.
 *
 * Usage:
 *   php tools/integrity_manifest.php
 *   php tools/integrity_manifest.php --output=/tmp/novacart_integrity.json
 */

$root = dirname(__DIR__);
$upload = $root . '/upload/';
$output = $upload . 'system/config/novacart_integrity.json';

foreach ($argv as $argument) {
	if (strpos($argument, '--output=') === 0) {
		$output = substr($argument, strlen('--output='));
	}
}

$paths = array(
	'system/engine/action.php',
	'system/engine/controller.php',
	'system/engine/event.php',
	'system/engine/loader.php',
	'system/engine/model.php',
	'system/engine/proxy.php',
	'system/engine/registry.php',
	'system/engine/router.php',

	'system/library/cache.php',
	'system/library/db.php',
	'system/library/encryption.php',
	'system/library/image.php',
	'system/library/language.php',
	'system/library/log.php',
	'system/library/request.php',
	'system/library/response.php',
	'system/library/session.php',
	'system/library/template.php',
	'system/library/url.php',
	'system/library/cart/cart.php',

	'catalog/controller/startup/error.php',
	'catalog/controller/startup/event.php',
	'catalog/controller/startup/language_url.php',
	'catalog/controller/startup/maintenance.php',
	'catalog/controller/startup/router.php',
	'catalog/controller/startup/seo_url.php',
	'catalog/controller/startup/session.php',
	'catalog/controller/startup/startup.php',

	'catalog/controller/checkout/cart.php',
	'catalog/controller/checkout/checkout.php',
	'catalog/controller/checkout/confirm.php',
	'catalog/model/checkout/order.php',

	'admin/controller/common/developer.php',
	'admin/controller/common/filemanager.php',
	'admin/controller/common/forgotten.php',
	'admin/controller/common/login.php',
	'admin/controller/common/reset.php',
	'admin/controller/common/security.php'
);

sort($paths);

$files = array();
$missing = array();

foreach ($paths as $path) {
	$file = $upload . str_replace('/', DIRECTORY_SEPARATOR, $path);

	if (!is_file($file)) {
		$missing[] = $path;
		continue;
	}

	$hash = hash_file('sha256', $file);

	if ($hash === false) {
		fwrite(STDERR, 'Unable to hash: ' . $path . PHP_EOL);
		exit(2);
	}

	$files[$path] = $hash;
}

if ($missing) {
	foreach ($missing as $path) {
		fwrite(STDERR, 'Missing curated core file: ' . $path . PHP_EOL);
	}

	exit(1);
}

$manifest = array(
	'format' => 1,
	'algorithm' => 'sha256',
	'scope' => 'curated-core',
	'files' => $files
);

$json = json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

if ($json === false) {
	fwrite(STDERR, 'Unable to encode integrity manifest.' . PHP_EOL);
	exit(2);
}

$directory = dirname($output);

if (!is_dir($directory) && !mkdir($directory, 0777, true) && !is_dir($directory)) {
	fwrite(STDERR, 'Unable to create output directory: ' . $directory . PHP_EOL);
	exit(2);
}

if (file_put_contents($output, $json . PHP_EOL) === false) {
	fwrite(STDERR, 'Unable to write integrity manifest: ' . $output . PHP_EOL);
	exit(2);
}

echo 'Integrity manifest written: ' . $output . PHP_EOL;
echo 'Files: ' . count($files) . PHP_EOL;

exit(0);
