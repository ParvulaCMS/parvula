<?php
// ----------------------------- //
// Let the magic begin
// ----------------------------- //

use Parvula\Core\Parvula;

if (!defined('_ROOT_')) exit;
$time = -microtime(true);

// Try to load composer autoloader
if (is_file($autoload = _VENDOR_ . '/autoload.php')) {
	require $autoload;
} else {
	throw new \RuntimeException('Please install the dependencies with composer: <code>composer install</code>');
}

$app = new Parvula;

require _APP_ . 'helpers.php';

// Register services
require _APP_ . 'services.php';

$config = $app['config'];
$config->set('__time__', $time);

// Set timezone
date_default_timezone_set($config->get('timezone', 'UTC'));

$router = $app['router'];
Parvula::setRequest($router->getContainer()['request']);

$debug = (bool) $config->get('debug', false);
$logErrors = (bool) $config->get('logErrors', false);

if ($logErrors) {
	// Register the logger
	$app['loggerHandler'];
}

if ($debug) {
	// Report all errors
	error_reporting(E_ALL);
	$app['errorHandler'];
} else {
	// Don't display errors to the client
	ini_set('display_errors', 0);
}

// Load class aliases
loadAliases($config->get('aliases'));

// Load plugins
$plugins = $app['plugins'];
$plugins->trigger('bootstrap', [$app]);
$plugins->trigger('load');

// Load routes
require 'routes.php';
$plugins->trigger('end');
