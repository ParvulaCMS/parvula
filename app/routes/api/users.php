<?php

use Parvula\Models\User;
use function Parvula\app;

/**
 * @api {get} /users List of users usernames
 * @apiName Index users
 * @apiGroup User
 *
 * @apiSuccess (200) {array} Array of User usernames
 *
 * @apiSuccessExample Success-Response:
 *     HTTP/1.1 200 OK
 *     [
 *       "admin",
 *       "James",
 *       "pat1987"
 *     ]
 */
$this->get('', function ($req, $res) {
	return $this->api->json($res, app('users')->all()->map(function (User $u) {
			return $u->toArray();
	}));
})->setName('users.index');

/**
 * @api {get} /users/:name User information
 * @apiDescription User password will **not** be send
 * @apiName Get User
 * @apiGroup User
 *
 * @apiParam {String} username User unique username
 *
 * @apiSuccess (200) {Object} User object
 * @apiError (404) UserNotFound User's username was not found
 *
 * @apiSuccessExample Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *       "username": "admin",
 *       "email": "pro@compagny.com"
 *     }
 *
 * @apiErrorExample Error-Response:
 *     HTTP/1.1 404 Not Found
 *     {
 *       "error": "UserNotFound",
 *       "message": "User's username was not found"
 *     }
 */
$this->get('/{username:\w+}', function ($req, $res, $args) {
	if (false !== $user = app('users')->findBy('username', $args['username'])) {
		return $this->api->json($res, $user->toArray());
	}

	return $this->api->json($res, [
		'error' => 'UserNotFound',
		'message' => 'User\'s username was not found'
	], 404);
})->setName('users.show');
