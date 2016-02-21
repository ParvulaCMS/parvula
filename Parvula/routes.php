<?php
// ----------------------------- //
// Routes (controller)
// ----------------------------- //

$app['plugins']->trigger('router', [&$router]);

// Api namespace
$prefix = rtrim($app['config']->get('apiPrefix'), '/');
$router->group($prefix, function () use ($app) {
	require _APP_ . 'routes/api.php';
});

// Index namespace
require _APP_ . 'routes/index.php';

$router->run();
