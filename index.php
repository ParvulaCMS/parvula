<?php
// PARVULA CMS

// Define some useful constants
if (!defined('_ROOT_')) {
	define('_ROOT_', '');
}
if (!defined('_USER_ROOT_')) {
	define('_USER_ROOT_', _ROOT_);
}
if (!defined('_RESOURCE_ROOT_')) {
	define('_RESOURCE_ROOT_', _ROOT_);
}
define('_APP_',         _ROOT_ . 'app/');
define('_DATA_',        _USER_ROOT_ . 'data/');
define('_STATIC_',      _USER_ROOT_ . 'static/');
define('_VENDOR_',      _ROOT_ . 'vendor/');

// Data
define('_PAGES_',       _DATA_ . 'pages/');
define('_CONFIG_',      _DATA_ . 'config/');
define('_USERS_',       _DATA_ . 'users/');
define('_CACHE_',       _DATA_ . 'cache/');
define('_LOGS_',        _DATA_ . 'logs/');

// Static
define('_UPLOADS_',     _STATIC_ . 'files/');

define('_BIN_',         _APP_ . 'bin/');
define('_THEMES_',      _RESOURCE_ROOT_ . 'themes/');
define('_PLUGINS_',     _RESOURCE_ROOT_ . 'plugins/');
define('_VERSION_',     '0.8.0-DEV');

require_once _APP_ . 'bootstrap.php';
