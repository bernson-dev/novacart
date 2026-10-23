<?php
// HTTP alternative to system CLI cron. Requires a key stored outside the web root.
if (!is_file(__DIR__ . '/config.php')) {
    http_response_code(503);
    exit;
}
require_once(__DIR__ . '/config.php');
define('NOVACART_CRON_ENTRY', true);
$_GET['route'] = 'cron/run';
unset($_GET['command']);
require_once(DIR_SYSTEM . 'startup.php');
start('catalog');
