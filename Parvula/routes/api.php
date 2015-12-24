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
$router->post('/login', function ($req, $res) use ($app) {

	$users = $app['users'];

	if (!isset($req->body->username, $req->body->password)) {
		return $res->status(400)->send([
			'error' => 'BadArguments',
			'message' => 'You need to give a username and a password'
		]);
	}

	// TODO Tests !
	if ($app['config']->get('forceLoginOnTLS') && !$req->secureLayer) {
		if ($req->scheme !== 'https') {
			// Try to redirect to https
			header('Location: https://' . $req->host . $req->uri);
			exit;
		}
	}

	$password = $req->body->password;

	if (!($user = $users->read($req->body->username))) {
		return $res->status(403)->send([
			'error' => 'BadCredentials',
			'message' => 'User or password not ok'
		]);
	}

	if (!$user->login($password)) {
		return $res->status(403)->send([
			'error' => 'BadCredentials',
			'message' => 'User or password not ok'
		]);
	}

	// Create a session
	$app['auth']->log($user->username);

	return $res->sendStatus(204);
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
$router->map('GET|POST', '/logout', function($req, $res) use ($app) {
	$rep = $app['session']->destroy();
	return $res->json($rep);
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
$router->map('GET|POST', '/islogged', function($req, $res) use ($isAdmin) {
	return $res->json((bool) $isAdmin());
});

require 'api/pages.php';

if ($isAdmin()) {

	$router->group($prefix . '/themes', function($router) use ($app) {
		require 'api/themes.php';
	});

	$router->group($prefix . '/users', function($router) use ($app) {
		require 'api/users.php';
	});

	$router->group($prefix . '/config', function($router) use ($app) {
		require 'api/config.php';
	});

	$router->group($prefix . '/files', function($router) use ($app) {
		require 'api/files.php';
	});

}

// If nothing match in the api group and not loged
$router->map('GET|POST|PUT|DELETE|PATCH', '/{r:.*}', function($req, $res) use ($app) {
	return $res->status(400)->send([
		'error' => 'RouteOrCredentialsError',
		'message' => 'API route not found or bad credentials'
	]);
});
