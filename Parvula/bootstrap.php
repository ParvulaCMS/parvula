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
	$app['errorHandler'];
}

// Display or not errors
ini_set('display_errors', $debug);

// Set timezone
date_default_timezone_set($config->get('timezone', 'UTC'));

// Load class aliases
loadAliases($config->get('aliases'));






class APIRender {
	/**
	 * Output rendered template
	 *
	 * @param  ResponseInterface $response
	 * @param  array $data Associative array of data to be returned
	 * @param  int $status HTTP status code
	 * @return ResponseInterface
	 */
	public function json(\Psr\Http\Message\ResponseInterface $res, $data = [], $status = 200) {
		return $res
			->withStatus($status)
			->withHeader('Content-Type', 'application/json')
			->write(json_encode($data));
	}
};

$slimConf = [
	'settings' => [
		'displayErrorDetails' => true,
	],
	'api' => new APIRender()
];
$c = new \Slim\Container($slimConf);
$router = new \Slim\App($c);

Parvula::setRequest($c['request']);

// Load plugins
$plugins = $app['plugins'];
$plugins->trigger('bootstrap', [$app]);
$plugins->trigger('load');

// Load routes
require 'routes.php';
$plugins->trigger('end');
