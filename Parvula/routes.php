<?php
// ----------------------------- //
// Routes (controller)
// ----------------------------- //

$med->trigger('router', [&$router]);
$med->trigger('route', [$router->getMethod(), $router->getUri()]);

// Api namespace
$router->group('/_api', function($router) use ($app) {
	return require APP . 'routes/api.php';
});

// Index namespace
require APP . 'routes/index.php';
