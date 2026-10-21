<?php
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

// Startup
require_once(DIR_SYSTEM . 'startup.php');

start('admin');
