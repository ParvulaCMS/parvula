<?php
// ----------------------------- //
// Let the magic begin
// ----------------------------- //

use Parvula\Core\Parvula;

if(!defined('ROOT')) exit;
$time = -microtime(true);

// Try to load composer autoloader
if(is_readable($autoload = ROOT . 'vendor/autoload.php')) {
	require $autoload;
} else {
	require APP . 'Core/Parvula.php';
	Parvula::registerAutoloader();
}

$app = new Parvula;

// Parvula::redirectIfTrailingSlash(); //@FIXME

require APP . 'helpers.php';

// Register services
require APP . 'services.php';

$config = $app['config'];
$config->set('__time__', $time);

$debug = (bool) $config->get('debug', false);

if ($debug) {
	error_reporting(E_ALL);
	$app->get('errorHandler');
}

// Display or not errors
ini_set('display_errors', $debug);

// Load class aliases
loadAliases($config->get('aliases'));

// Load user config
// Append user config to Config wrapper (override if exists)
$config->append((array) $app->getUserConfig());

// Load plugins
$plugins = $app['plugins'];
$plugins->trigger('bootstrap', [$app]);
$plugins->trigger('load');

// Load routes
require 'routes.php';
$plugins->trigger('end');
