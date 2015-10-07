<?php
// ----------------------------- //
// Routes (controller)
// ----------------------------- //

use Parvula\Core\Config;
use Parvula\Core\Parvula;
use Parvula\Core\Model\Pages;

$med->trigger('router', [&$router]);
$med->trigger('route', [$router->getMethod(), $router->getUri()]);

// Api namespace
$router->group('/_api', function($router) use ($config) {
	return require APP . 'routes/api.php';
});

// Index namespace
require APP . 'routes/index.php';
