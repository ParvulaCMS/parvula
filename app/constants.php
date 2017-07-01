<?php
// Define some useful constants

if (!defined('_ROOT_')) {
	define('_ROOT_', '');
}

if (!defined('_DATA_')) {
	define('_DATA_', _ROOT_ . 'data/');
}

if (!defined('_PUBLIC_')) {
	define('_PUBLIC_', _ROOT_ . 'public/');
}

if (!defined('_STORAGE_')) {
	define('_STORAGE_', _ROOT_ . 'storage/');
}

if (!defined('_VENDOR_')) {
	define('_VENDOR_', _ROOT_ . 'vendor/');
}

// Public (the only accessible point from the outside)
define('_THEMES_', _PUBLIC_ . 'themes/');
define('_PLUGINS_', _PUBLIC_ . 'plugins/');
define('_UPLOADS_', _PUBLIC_ . 'files/');

// Data
define('_PAGES_', _DATA_ . 'pages/');
define('_CONFIG_', _DATA_ . 'config/');
define('_USERS_', _DATA_ . 'users/');

// Storage
define('_CACHE_', _STORAGE_ . 'cache/');
define('_LOGS_', _STORAGE_ . 'logs/');

// Custom path to extends configs or services
if (!defined('_CUSTOM_')) {
	define('_CUSTOM_', _DATA_ . 'custom/');
}

define('_CUSTOM_CONFIG_', _CUSTOM_ . 'config/');

// Main
define('_APP_', _ROOT_ . 'app/');
define('_BIN_', _APP_ . 'bin/');
define('_VERSION_', '0.8.0-BETA.1');
