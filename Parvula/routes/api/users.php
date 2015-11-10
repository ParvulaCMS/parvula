<?php

namespace Parvula;

use Exception;
use Parvula\Core\Exception\IOException;

$users = $app['users'];

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
$router->get('', function($req) use ($users) {
	return apiResponse(true, $users->index());
});

/**
 * @api {get} /users/:name User information
 * @apiDescription User password will **not** be send
 * @apiName Get User
 * @apiGroup User
 *
 * @apiParam {String} username User unique username
 *
 * @apiSuccess (200) {User} User object
 *
 * @apiSuccessExample Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *       "username": "admin",
 *       "email": "pro@compagny.com"
 *     }
 *
 * @apiError (404) UserNotFound The username of the User was not found
 *
 * @apiErrorExample Error-Response:
 *     HTTP/1.1 404 Not Found
 *     {
 *       "message": "User not found"
 *     }
 */
$router->get('/{username:\w+}', function($req) use ($users) {
	if (false !== $user = $users->read($req->params->username)) {
		return apiResponse(200, $user);
	}

	return apiResponse(404, 'User not found');
});
