<?php

namespace Parvula;

use Exception;

function checkTokenScope(array $scope, $token) {
	$scopeToken = isset($token->scope) ? $token->scope : [];
	if (!count(array_intersect($scope, $scopeToken))) {
		throw new Exception('Bad credentials for this path.', 403);
	}

	return true;
}

function checkRole(array $scope) {
	return function ($req, $res, $next) use ($scope) {
		checkTokenScope($scope, $req->getAttribute('token'));
		return $next($req, $res);
	};
}

$this->group('/auth', function () use ($app) {
	require 'auth.php';
});

$this->group('/pages', function () use ($app) {
	require 'pages.public.php';
});

$this->group('/pages', function () use ($app) {
	require 'pages.php';
})->add(checkRole(['pages', 'all']));

$this->group('/themes', function () use ($app) {
	require 'themes.php';
})->add(checkRole(['themes', 'all']));

$this->group('/users', function () use ($app) {
	require 'users.php';
})->add(checkRole(['users', 'all']));

$this->group('/config', function () use ($app) {
	require 'config.php';
})->add(checkRole(['config', 'all']));

$this->group('/files', function () use ($app) {
	require 'files.php';
})->add(checkRole(['files', 'all']));

// If nothing match in the api group and client is not loged
$this->any('/{r:.*}', function ($req, $res) use ($app) {
	return $this->api->json($res, [
		'error' => 'RouteOrCredentialsError',
		'message' => 'API route not found'
	], 400);
});
