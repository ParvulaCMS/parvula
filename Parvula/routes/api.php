<?php

namespace Parvula;

use Exception;
use Parvula\Core\Exception\IOException;

// TODO Temporary version
$isAdmin = function () use ($app) {
	return $app['usersession'] && $app['usersession']->hasRole('admin');
};

/**
 * @api {post} /login Login
 * @apiName Login
 * @apiGroup Authentication
 * @apiDescription Create a new session if the credentials are OK
 *
 * @apiParam {String} username User unique username
 * @apiParam {String} password User password
 *
 * @apiSuccess (200) User logged
 * @apiError (400) BadArguments
 * @apiError (400) BadCredentials
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
$this->post('/login', function ($req, $res) use ($app) {
	$users = $app['users'];

	$parsedBody = $req->getParsedBody();

	if (!isset($parsedBody['username'], $parsedBody['password'])) {
		return $this->api->json($res, [
			'error' => 'BadArguments',
			'message' => 'You need to give a username and a password'
		], 400);
	}

	// Too late, data have been sent but force the redirection
	// TODO Tests ! // && !$req->secureLayer
	if ($app['config']->get('forceLoginOnTLS')) {
		$uri = $req->getUri();
		if ($uri->getScheme() !== 'https') {
			// Try to redirect to https
			header('Location: https://' . $uri->getHost() . $uri->getPath() . $uri->getQuery());
			exit;
		}
	}

	if (!($user = $users->read($parsedBody['username']))) {
		return $this->api->json($res, [
			'error' => 'BadCredentials',
			'message' => 'User or password not ok'
		], 403);
	}

	if (!$user->login($parsedBody['password'])) {
		return $this->api->json($res, [
			'error' => 'BadCredentials',
			'message' => 'User or password not ok'
		], 403);
	}

	// Create a session
	$app['auth']->log($user->username);

	return $res->withStatus(204);
});

/**
 * @api {get} /logout Logout
 * @apiName Logout
 * @apiGroup Authentication
 * @apiDescription Delete the current session
 *
 * @apiSuccessExample Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *       "status": "success"
 *     }
 */
$this->map(['GET', 'POST'], '/logout', function ($req, $res) use ($app) {
	$rep = $app['session']->destroy();
	return $this->api->json($res, $rep);
});

/**
 * @api {get} /islogged Is logged
 * @apiName Is logged
 * @apiGroup Authentication
 * @apiDescription Check if is the current session is logged
 *
 * @apiSuccessExample Success-Response:
 *     {
 *       "status": "success",
 *       "data": true
 *     }
 *
 * @apiSuccessExample Success-Response:
 *     {
 *       "status": "success",
 *       "data": false
 *     }
 */
$this->map(['GET', 'POST'], '/islogged', function ($req, $res) use ($isAdmin) {
	return $this->api->json($res, (bool) $isAdmin());
});

$this->group('/pages', function () use ($app, $isAdmin) {
	require 'api/pages.php';
});

if ($isAdmin()) {


	$this->group('/themes', function () use ($app) {
		require 'api/themes.php';
	});

	$this->group('/users', function () use ($app) {
		require 'api/users.php';
	});

	$this->group('/config', function () use ($app) {
		require 'api/config.php';
	});

	$this->group('/files', function () use ($app) {
		require 'api/files.php';
	});

}

// If nothing match in the api group and not loged
$this->any('/{r:.*}', function ($req, $res) use ($app) {
	return $this->api->json($res, [
		'error' => 'RouteOrCredentialsError',
		'message' => 'API route not found or bad credentials'
	], 400);
});
