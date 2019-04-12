<?php

use function Parvula\app;

function checkTokenScope(array $scope, $token) {
	$scopeToken = isset($token->scope) ? $token->scope : [];
	if (!count(array_intersect($scope, $scopeToken))) {
		throw new Exception('Bad credentials for this path.', 403);
	}

	return true;
}

$shouldCheckRoles = app('config')->get('checkRoles', true);

$checkRolesMiddleware = function (array $scope) use ($shouldCheckRoles) {
	return function ($req, $res, $next) use ($scope, $shouldCheckRoles) {
		// Check role token in JWT if the 'checkRoles' option is true
		if ($shouldCheckRoles) {
			checkTokenScope($scope, $req->getAttribute('token'));
		}
		return $next($req, $res);
	};
};

$this->group('/auth', function () use ($app) {
	require 'auth.php';
});

$this->group('/pages', function () use ($app) {
	require 'pages.public.php';
});

$this->group('/pages', function () use ($app) {
	require 'pages.php';
})->add($checkRolesMiddleware(['pages', 'all']));

$this->group('/themes', function () use ($app) {
	require 'themes.php';
})->add($checkRolesMiddleware(['themes', 'all']));

$this->group('/users', function () use ($app) {
	require 'users.php';
})->add($checkRolesMiddleware(['users', 'all']));

$this->group('/config', function () use ($app) {
	require 'config.php';
})->add($checkRolesMiddleware(['config', 'all']));

$this->group('/files', function () use ($app) {
	require 'files.php';
})->add($checkRolesMiddleware(['files', 'all']));

// If nothing match in the api group and client is not loged
$this->any('/{r:.*}', function ($req, $res) use ($app) {
	return $this->api->json($res, [
		'error' => 'RouteOrCredentialsError',
		'message' => 'API route not found'
	], 400);
});
