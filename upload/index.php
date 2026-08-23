<?php
// Version
define('VERSION', '3.0.3.9');
define('VERSION_CORE', 'NovaCart');
define('VERSION_LANGPACK', 'RU-UK-EN');

// Configuration
if (is_file('config.php')) {
	require_once('config.php');
}

// Install
if (!defined('DIR_APPLICATION')) {
	header('Location: install/index.php');
	exit;
}

// Startup
require_once(DIR_SYSTEM . 'startup.php');

start('catalog');
