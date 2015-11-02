<?php

namespace Parvula;

use Exception;
use Parvula\Core\Exception\IOException;

// Send API message @TODO Temp
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
		$res['data'] = $data;
	}

	header('Content-Type: application/json');
	http_response_code($responseCode);

	return json_encode($res);
}

// TODO
// Temporary version
$isAdmin = function () use ($app) {
	$session = $app['session'];
	return $session->get('login');
};

//
// Public API
//

/**
 * @api {post} /login
 * @apiName Login
 * @apiGroup Login
 *
 * @apiParam {String} username User unique username
 * @apiParam {String} password User password
 *
 * @apiSuccessExample Success-Response:
 *     HTTP/1.1 200
 *     {
 *       "status": "success",
 *       "message": "Login ok"
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

	return apiResponse(true, 'Login ok');
});

//
// Admin API
//
if($isAdmin()) {

	// Logout
	$router->map('GET|POST', '/logout', function() use ($app) {
		$res = $app['session']->destroy();
		// $res = session_destroy();
		// session_unset();
		return apiResponse($res);
	});

	$router->group('/_api/themes', function($router) use ($app) {
		require 'api/themes.php';
	});

// } else {
	// @TODO
	// echo '{"message": "Not found or not logged"}';
}

require 'api/pages.php';
