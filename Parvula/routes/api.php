<?php

namespace Parvula;

use Exception;
use Parvula\Core\Exception\IOException;

// Send API message @TODO Temporary
function apiResponse($responseCode = 200, $data = null) {

	if (is_array($responseCode)) {
		$data = $responseCode;
		$responseCode = true;
	}

	if($responseCode === true) {
		$responseCode = 200;
	} else if($responseCode == false) {
		$responseCode = 400;
	}

	$res = [];
	if($responseCode >= 300) {
		$res['status'] = 'error';
		$res['message'] = $data;
	} else {
		$res['status'] = 'success';
		if ($data !== null) {
			$res['data'] = $data;
		}
	}

	header('Content-Type: application/json');
	http_response_code($responseCode);

	return json_encode($res);
}

// TODO Temporary version
$isAdmin = function () use ($app) {
	$session = $app['session'];
	return $session->get('login');
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
 * @apiSuccessExample Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *       "status": "success"
 *     }
 *
 * @apiErrorExample Error-Response:
 *     HTTP/1.1 404
 *     {
 *       "status": "error",
 *       "message": "you need to give at a username and a password"
 *     }
 *
 * @apiErrorExample Error-Response:
 *     HTTP/1.1 404
 *     {
 *       "status": "error",
 *       "message": "user or password not ok"
 *     }
 */
$router->post('/login', function ($req) use ($app) {

	$users = $app['users'];

	if (!isset($req->body->username, $req->body->password)) {
		return apiResponse(false, 'you need to give at a username and a password');
	}

	// TODO Tests !
	if ($app['config']->get('forceLoginOnTLS') === true && $req->secureLayer !== true) {
		if ($req->scheme !== 'https') {
			// Try to redirect to https
			header('Location: https://' . $req->host . $req->uri);
			exit;
		}
	}

	$password = $req->body->password;

	if (!($user = $users->read($req->body->username))) {
		return apiResponse(false, 'user or password not ok');
	}

	if (!$user->login($password)) {
		return apiResponse(false, 'user or password not ok');
	}

	// Create a session
	$app['session']->set('login', true);
	$app['session']->set('username', $req->body->username);
	$app['session']->set('token', hash('sha1', $req->ip . $req->userAgent));

	return apiResponse(true);
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
$router->map('GET|POST', '/logout', function() use ($app) {
	$res = $app['session']->destroy();
	return apiResponse($res);
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
$router->map('GET|POST', '/islogged', function() use ($isAdmin) {
	return apiResponse(true, (bool) $isAdmin());
});

require 'api/pages.php';

if ($isAdmin()) {

	$router->group('/_api/themes', function($router) use ($app) {
		require 'api/themes.php';
	});

	$router->group('/_api/users', function($router) use ($app) {
		require 'api/users.php';
	});

}

// If nothing match in the api group and not loged
$router->map('GET|POST|PUT|DELETE|PATCH', '/{r:.*}', function() use ($app) {
	return apiResponse(400, 'API route not found or bad credentials');
});
