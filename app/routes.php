<?php
// ----------------------------- //
// Routes (controller)
// ----------------------------- //

$app['plugins']->trigger('router', [&$router]);

// Api namespace
$prefix = rtrim($app['config']->get('apiPrefix'), '/');
$router->group($prefix, function () use ($app) {
	$app['plugins']->trigger('routerAPI', [&$this]);

	require _APP_ . 'routes/api/index.php';
});

// Index namespace
require _APP_ . 'routes/index.php';

$router->run();
