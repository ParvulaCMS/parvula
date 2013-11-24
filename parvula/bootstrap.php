<?php
// ----------------------------- //
// Starting point for the magic
// ----------------------------- //

use Parvula\Core\Router;
use Parvula\Core\Config;
use Parvula\Core\Parvula;

if(!defined('ROOT')) exit;

// Try to load composer autoloader
if(is_readable($autoload = ROOT . 'vendor/autoload.php')) {
	require $autoload;
} else {
	require APP . 'Core/Parvula.php';
	Parvula::registerAutoloader();
}

require APP . 'helpers.php';

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

// Auto set URLRewriting Config
if(Config::get('URLRewriting') === 'auto') {
	$scriptName = $_SERVER['SCRIPT_NAME'];
	if(substr($_SERVER['REQUEST_URI'], 0, strlen($scriptName)) === $scriptName) {
		Config::set('URLRewriting', false);
	} else {
		Config::set('URLRewriting', true);
	}
}

$router = new Router(Parvula::getURI());
require 'routes.php';
$router->run();
