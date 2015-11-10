<?php

namespace Parvula;

use Exception;
use Parvula\Core\Exception\IOException;

// Send API message @TODO Temporary
function apiResponse($responseCode = 200, $data = null) {

	if (headers_sent()) {
		return;
	}

	if (is_array($responseCode)) {
		$data = $responseCode;
		$responseCode = true;
	}

	if($responseCode === true) {
		$responseCode = 204;
	} else if($responseCode == false) {
		$responseCode = 400;
	}

	if($responseCode >= 300) {
		$res = [];
		$res['message'] = $data;
	} else {
		if ($data !== null) {
			$res = $data;
		}
	}

	// Fix code 200 if no body
	if (empty($res) && $responseCode === 200) {
		$responseCode = 204;
	}

	header('Content-Type: application/json');
	http_response_code($responseCode);

	if (isset($res) && !empty($res)) {
		return json_encode($res);
	}

	return;
}

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
	$app['auth']->log($user->username);

	return apiResponse(204);
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
	return apiResponse(200, (bool) $isAdmin());
});

require 'api/pages.php';

if ($isAdmin()) {

	$router->group('/_api/themes', function($router) use ($app) {
		require 'api/themes.php';
	});

	$router->group('/_api/users', function($router) use ($app) {
		require 'api/users.php';
	});

	$router->group('/_api/config', function($router) use ($app) {
		require 'api/config.php';
	});

}

// If nothing match in the api group and not loged
$router->map('GET|POST|PUT|DELETE|PATCH', '/{r:.*}', function() use ($app) {
	return apiResponse(400, 'API route not found or bad credentials');
});
