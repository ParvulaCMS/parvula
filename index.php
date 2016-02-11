<?php

namespace Parvula;

// Define some useful constants
define('_ROOT_',        '');
define('_APP_',         _ROOT_ . 'Parvula/');
define('_DATA_',        _ROOT_ . 'data/');
define('_STATIC_',      _ROOT_ . 'static/');
define('_VENDOR_',      _ROOT_ . 'vendor/');

// Data
define('_PAGES_',       _DATA_ . 'pages/');
define('_CONFIG_',      _DATA_ . 'config/');
define('_USERS_',       _DATA_ . 'users/');

// Static
define('_IMAGES_',      _STATIC_ . 'media/');
define('_UPLOADS_',     _STATIC_ . 'files/');
define('_COMPONENTS_',  _STATIC_ . 'components/');

define('_THEMES_',      _ROOT_ . 'themes/');
define('_PLUGINS_',     _ROOT_ . 'plugins/');
define('_VERSION_',     '0.5.0');

require_once _APP_ . 'bootstrap.php';
