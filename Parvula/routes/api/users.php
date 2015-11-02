<?php

namespace Parvula;

use Exception;
use Parvula\Core\Exception\IOException;

$users = $app['users'];

/**
 * @api {get} /themes Request a list of users usernames
 * @apiName Index users
 * @apiGroup User
 *
 * @apiSuccess {array} Array of User usernames
 *
 * @apiSuccessExample Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *       "status":"success",
 *       "data":[
 *         "admin",
 *         "james",
 *         "pat1987"
 *       ]
 *     }
 */
$router->get('', function($req) use ($users) {
	return apiResponse(true, $users->index());
});

/**
 * @api {get} /themes/:name Request Theme information
 * @apiName Get User
 * @apiGroup User
 *
 * @apiParam {String} username User unique username
 *
 * @apiSuccess {User} User object
 *
 * @apiSuccessExample Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *       "status":"success",
 *       "data":[
 *         "username": "admin",
 *         "email": "pro@compagny.com"
 *       ]
 *     }
 *
 * @apiError UserNotFound The username of the User was not found
 *
 * @apiErrorExample Error-Response:
 *     HTTP/1.1 404 Not Found
 *     {
 *       "error": "UserNotFound"
 *     }
 */
$router->get('/{username:\w+}', function($req) use ($users) {
	// TODO 404
	return apiResponse(true, $users->read($req->params->username));
});
