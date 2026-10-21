<?php
// Diagnostic profiling is opt-in through the PHP process environment only.
if (getenv('NOVACART_PROFILE_ADMIN') === '1') {
    require_once __DIR__ . '/../system/library/admin_cold_profiler.php';
    NovaCartAdminProfiler::start();
}

// Version
define('VERSION', '3.0.3.9');

// Configuration
if (is_file('config.php')) {
	require_once('config.php');
}

// Install
if (!defined('DIR_APPLICATION')) {
	if (!is_file(__DIR__ . '/../install/index.php')) {
        http_response_code(500);
        header('Content-Type: text/plain; charset=utf-8');
        exit('NovaCart: configuration is missing or invalid, and the installer directory is unavailable. Restore config.php and admin/config.php, or restore the install directory to perform installation.');
    }
	header('Location: ../install/index.php');
	exit;
}

if (class_exists('NovaCartAdminProfiler', false)) {
    NovaCartAdminProfiler::mark('before_startup');
}

// Startup
require_once(DIR_SYSTEM . 'startup.php');

start('admin');
if (class_exists('NovaCartAdminProfiler', false)) {
    NovaCartAdminProfiler::mark('after_startup');
}
