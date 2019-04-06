<?php

namespace Parvula;

require 'constants.php';

if (!defined('_ROOT_')) {
	exit;
}

$time = -microtime(true);

// Try to load composer autoloader
if (!is_file($autoload = _VENDOR_ . '/autoload.php')) {
	throw new \RuntimeException('Please install the dependencies with composer: `composer install`');
}

require $autoload;

$app = Parvula::getContainer();

// Register services and helpers
require _APP_ . 'services.php';

// Load helpers
require _APP_ . 'helpers.php';

$config = $app['config'];
$config->set('__time__', $time);

if (is_file(_CUSTOM_ . 'services.php')) {
	require _CUSTOM_ . 'services.php';
}

// Set timezone (default to UTC)
date_default_timezone_set($config->get('timezone', 'UTC'));

if ($config->get('logErrors', false)) {
	// Register the logger
	$app['loggerHandler'];
}

if ($config->get('debug', false)) {
	// Report all errors
	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	$app['errorHandler'];
} else {
	// Don't display errors to the client
	ini_set('display_errors', 0);
}

// Load class aliases
classAliases($config->get('aliases'));

// Command line script loader
if (php_sapi_name() === 'cli' && isset($_executeFromComposerScript)) {
	if ($_executeFromComposerScript) {
		return;
	}
}

$router = $app['router'];
Parvula::setRequest($router->getContainer()['request']);

// Load plugins
$plugins = $app['plugins'];
$plugins->trigger('boot', [$app]);

// Load routes
require 'routes.php';
$plugins->trigger('end');
