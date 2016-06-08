<?php

namespace Parvula;

use DateTime;
use Exception;
use Firebase\JWT\JWT;
use Parvula\Exceptions\IOException;

function checkTokenScope(array $scope, $token) {
	if (!count(array_intersect($scope, $token->scope))) {
		throw new \Exception('Bad credentials for this path.', 403);
	}

	return true;
}

function checkAuth(array $scope) {
	return function ($req, $res, $next) use ($scope) {
		checkTokenScope($scope, $req->getAttribute('token'));
		// checkTokenScope($scope, $this->token);
		$res = $next($req, $res);
		return $res;
	};
}

/**
 * @api {get} /login Login
 * @apiName Login
 * @apiGroup Authorization
 * @apiDescription Returns a secret token if the credentials are OK
 *
 * @apiParam {String} Authorization Basic <base 64 />
 *
 * @apiSuccess (200) Valid credentials
 * @apiError (400) BadArguments
 * @apiError (403) BadCredentials
 *
 * @apiSuccessExample Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *       "status": "success"
 *     }
 *
 * @apiErrorExample Error-Response:
 *     HTTP/1.1 400
 *     {
 *       "error": "BadArguments",
 *       "message": "you need to give at a username and a password"
 *     }
 *
 * @apiErrorExample Error-Response:
 *     HTTP/1.1 403 Forbidden
 *     {
 *       "error": "BadCredentials",
 *       "message": "user or password not ok"
 *     }
 */
$this->map(['GET', 'POST'], '/login', function ($req, $res, $args) use ($app) {
	$users = $app['users'];

	$server = $req->getServerParams();

	if (!isset($server['PHP_AUTH_USER'], $server['PHP_AUTH_PW'])) {
		return $this->api->json($res, [
			'error' => 'BadArguments',
			'message' => 'You need to give a username and a password'
		], 400);
	}

	$username = $server['PHP_AUTH_USER'];
	$password = $server['PHP_AUTH_PW'];

	if (!($user = $users->read($username))) {
		return $this->api->json($res, [
			'error' => 'BadCredentials',
			'message' => 'User or password not ok'
		], 403);
	}

	if (!$user->login($password)) {
		return $this->api->json($res, [
			'error' => 'BadCredentials',
			'message' => 'User or password not ok'
		], 403);
	}

	$now = new DateTime();
	$future = new DateTime('now +2 hours');
	$server = $req->getServerParams();

	try {
		$payload = [
			'iat' => $now->getTimeStamp(), // issued at
			'exp' => $future->getTimeStamp(), // expiration time
			'jti' => JWT::urlsafeB64Encode(random_bytes(32)), // unique identifier
			'sub' => $username, // subject
			'scope' => $user->getRoles()
		];
	} catch (Exception $e) {
		die('Could not generate a random string. Is our OS secure?');
	}

	$secret = $app['config']->get('secretToken');
	$token = JWT::encode($payload, $secret, 'HS256');
	$data['status'] = 'ok';
	$data['token'] = $token;
	return $res->withStatus(201)
		->withHeader('Content-Type', 'application/json')
		->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
});

$this->get('/credentials', function ($req, $res) {
	if (!isset($this->token)) {
		return $res->withStatus(401)->withJson([]);
	}
	$res->withJson($this->token->scope);
});

$this->group('/pages', function () use ($app) {
	require 'pages.public.php';
});

$this->group('/pages', function () use ($app) {
	require 'pages.php';
})->add(checkAuth(['pages', 'all']));

$this->group('/themes', function () use ($app) {
	require 'themes.php';
})->add(checkAuth(['themes', 'all']));

$this->group('/users', function () use ($app) {
	require 'users.php';
})->add(checkAuth(['users', 'all']));

$this->group('/config', function () use ($app) {
	require 'config.php';
})->add(checkAuth(['config', 'all']));

$this->group('/files', function () use ($app) {
	require 'files.php';
})->add(checkAuth(['files', 'all']));

// If nothing match in the api group and client is not loged
$this->any('/{r:.*}', function ($req, $res) use ($app) {
	return $this->api->json($res, [
		'error' => 'RouteOrCredentialsError',
		'message' => 'API route not found'
	], 400);
});
