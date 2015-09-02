<?php
// ----------------------------- //
// Let the magic begin
// ----------------------------- //

use Parvula\Core\Router;
use Parvula\Core\Config;
use Parvula\Core\Parvula;
use Parvula\Core\Mediator;

if(!defined('ROOT')) exit;

// Try to load composer autoloader
if(is_readable($autoload = ROOT . 'vendor/autoload.php')) {
	require $autoload;
} else {
	require APP . 'Core/Parvula.php';
	Parvula::registerAutoloader();
}

// Parvula::redirectIfTrailingSlash(); //@FIXME

require APP . 'helpers.php';

// Use custom exception handler
set_exception_handler('exceptionHandler');

// Populate Config wrapper
Config::populate(require APP . 'config.php');

// Display or not errors
ini_set('display_errors', (bool) Config::get('debug'));

// Load class aliases
loadAliases(Config::get('aliases'));

// Load user config
$config = Parvula::getUserConfig();

// Append user config to Config wrapper (override if exists)
Config::append((array) $config);

// Load plugins
$med = new Mediator;
$med->attach(getPluginList(Config::get('disabledPlugins')));
$med->trigger('Load');

// Auto set URLRewriting Config
if(Config::get('URLRewriting') === 'auto') {
	$scriptName = $_SERVER['SCRIPT_NAME'];
	if(substr($_SERVER['REQUEST_URI'], 0, strlen($scriptName)) === $scriptName) {
		Config::set('URLRewriting', false);
	} else {
		Config::set('URLRewriting', true);
	}
}

// Load routes
$router = new Router(Parvula::getURI());
require 'routes.php';
$router->run();
$med->trigger('End');
