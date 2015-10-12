<?php
// ----------------------------- //
// Routes (controller)
// ----------------------------- //

use Parvula\Core\Parvula;
use Parvula\Core\Router\RouteCollection;

$dispatcher = FastRoute\simpleDispatcher(function(RouteCollection $router) use ($app) {
	$app['plugins']->trigger('router', [&$router]);

	// Api namespace
	$router->group('/_api', function($router) use ($app) {
		require APP . 'routes/api.php';
	});

	// Index namespace
	require APP . 'routes/index.php';

}, ['routeCollector' => 'Parvula\\Core\\Router\\RouteCollection']);

$req = $app['request'];
$uri = Parvula::getURI();
$method = Parvula::getMethod();

$plugins->trigger('dispatch', [$method, $uri]);

$routeInfo = $dispatcher->dispatch($method, $uri);

switch ($routeInfo[0]) {
	case FastRoute\Dispatcher::NOT_FOUND:
		echo 404; // 404 Not Found
		break;
	case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
		$allowedMethods = $routeInfo[1];
		echo 405; // 405 Method Not Allowed
		break;
	case FastRoute\Dispatcher::FOUND:
		$handler = $routeInfo[1];
		$vars = $routeInfo[2];
		$req->params = (object) $vars;

		echo $handler($req);
		break;
}
