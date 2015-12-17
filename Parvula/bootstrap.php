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

// Parvula::redirectIfTrailingSlash(); //@FIXME

require _APP_ . 'helpers.php';

// Register services
require _APP_ . 'services.php';

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

Parvula::setRequest($app['request']);

// Load plugins
$plugins = $app['plugins'];
$plugins->trigger('bootstrap', [$app]);
$plugins->trigger('load');

// Load routes
require 'routes.php';
$plugins->trigger('end');
