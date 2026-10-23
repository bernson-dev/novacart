<?php
// Dedicated CLI bootstrap. Never change the normal storefront bootstrap.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
if (!is_file(__DIR__ . '/config.php')) {
    fwrite(STDERR, "NovaCart: config.php not found\n");
    exit(2);
}
$command = isset($argv[1]) ? $argv[1] : 'cron:run';
if (!in_array($command, array('cron:run', 'cron:install', 'cron:list', 'cron:test:register'), true)) {
    fwrite(STDERR, "Usage: php cli.php cron:install|cron:run|cron:list|cron:test:register\n");
    exit(2);
}
require_once(__DIR__ . '/config.php');
define('NOVACART_CRON_ENTRY', true);
// The catalog bootstrap expects a storefront URL even for CLI requests.
$parts = parse_url(HTTP_SERVER);
$_SERVER['HTTP_HOST'] = isset($parts['host']) ? $parts['host'] . (isset($parts['port']) ? ':' . $parts['port'] : '') : 'localhost';
$_SERVER['PHP_SELF'] = rtrim(isset($parts['path']) ? $parts['path'] : '/', '/') . '/index.php';
$_SERVER['SCRIPT_NAME'] = $_SERVER['PHP_SELF'];
$_SERVER['REQUEST_URI'] = $_SERVER['PHP_SELF'];
$_SERVER['HTTPS'] = isset($parts['scheme']) && strtolower($parts['scheme']) === 'https' ? 'on' : 'off';
$_GET['route'] = 'cron/run';
$_GET['command'] = substr($command, 5);
require_once(DIR_SYSTEM . 'startup.php');
start('catalog');
