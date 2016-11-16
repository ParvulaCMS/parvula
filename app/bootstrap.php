<?php
use Parvula\Parvula;

if (!defined('_ROOT_')) {
	exit;
}
$time = -microtime(true);

// Try to load composer autoloader
if (is_file($autoload = _VENDOR_ . '/autoload.php')) {
	require $autoload;
} else {
	throw new \RuntimeException('Please install the dependencies with composer: <code>composer install</code>');
}

$app = Parvula::getContainer();

// Register services
require _APP_ . 'services.php';

require _APP_ . 'helpers.php';

$config = $app['config'];
$config->set('__time__', $time);

// Set timezone
date_default_timezone_set($config->get('timezone', 'UTC'));

$debug = (bool) $config->get('debug', false);
$logErrors = (bool) $config->get('logErrors', false);

if ($logErrors) {
	// Register the logger
	$app['loggerHandler'];
}

if ($debug) {
	// Report all errors
	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	$app['errorHandler'];
} else {
	// Don't display errors to the client
	ini_set('display_errors', 0);
}

// Load class aliases
loadAliases($config->get('aliases'));

// Command line script loader
if (php_sapi_name() === 'cli' && count($argv) > 1) {
	$phpFile = _BIN_ . trim($argv[1], " /\\.\0") . '.php';
	if (is_readable($phpFile)) {
		array_shift($argv);
		if (!require $phpFile) {
			return;
		}
	}
}

$router = $app['router'];
Parvula::setRequest($router->getContainer()['request']);

// Load plugins
$plugins = $app['plugins'];
$plugins->trigger('bootstrap', [$app]); // depreciated
$plugins->trigger('boot', [$app]);
$plugins->trigger('load');

// Load routes
require 'routes.php';
$plugins->trigger('end');
