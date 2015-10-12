<?php
// ----------------------------- //
// Routes (controller)
// ----------------------------- //


// Api namespace
$router->group('/_api', function($router) use ($app) {
	return require APP . 'routes/api.php';
});
$plugins->trigger('router', [&$router]);
$plugins->trigger('route', [$router->getMethod(), $router->getUri()]);

// Index namespace
require APP . 'routes/index.php';
