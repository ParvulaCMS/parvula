<?php
// ----------------------------- //
// Let the magic begin
// ----------------------------- //

use Parvula\Core\Router;
use Parvula\Core\Parvula;
use Parvula\Core\PluginMediator;

if(!defined('ROOT')) exit;

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
$med = new PluginMediator;
$med->attach(getPluginList($config->get('disabledPlugins')));
$med->trigger('bootstrap', [$app]);
$med->trigger('load');

// Load routes
$router = new Router();
require 'routes.php';
echo $router->run(Parvula::getMethod(), Parvula::getURI());
$med->trigger('end');
