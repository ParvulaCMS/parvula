<?php

namespace Parvula;

use Exception;
use Parvula\Core\Exception\IOException;

$themes = $app['themes'];

/**
 * @api {get} /themes List of themes
 * @apiName Index Themes
 * @apiGroup Theme
 *
 * @apiSuccess (200) {array} Array of Theme
 *
 * @apiSuccessExample {json} Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *       "mytheme": {...},
 *       "othertheme": {...}
 *     }
 */
$router->get('', function ($req, $res) use ($themes) {
	return $res->send($themes->index());
});

/**
 * @api {get} /themes/:name Theme information
 * @apiName Get Theme
 * @apiGroup Theme
 *
 * @apiParam {String} name Theme unique name
 *
 * @apiSuccess (200) {Object} Theme object
 * @apiError (404) ThemeNotFound
 *
 * @apiSuccessExample {json} Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *       "layouts": {..},
 *       "name": "My theme",
 *       "infos": {
 *         "description": "...",
 *         "filesType": "php"
 *       }
 *     }
 */
$router->get('/{name}', function ($req, $res) use ($themes) {

	if (false === $result = $themes->read($req->params->name)) {
		return $res->status(404)->send([
			'error' => 'ThemeNotFound'
		]);
	}

	return $res->send($result);
});

 /**
  * @api {get} /themes/:name/:field/:subfield Specific Theme field/subfield
  * @apiName Get a Theme field
  * @apiGroup Theme
  *
  * @apiParam {String} name Theme unique name
  * @apiParam {String} field Theme field
  * @apiParam {String} [subfield] Optional Theme subfield
  *
  * @apiSuccess (200) {Object} Theme propriety
  * @apiError (404) FieldDoesNotExists
  *
  * @apiSuccessExample {json} Success-Response:
  *     HTTP/1.1 200 OK
  *     {
  *       "layouts": {"index": "index.html"}
  *     }
  */
$router->get('/{name}/{field}[/{subfield}]', function ($req, $res) use ($themes) {
	$theme = $themes->read($req->params->name);

	$field = $req->params->field;

	if (!isset($theme->{$field})) {
		return $res->status(404)->send([
			'error' => 'FieldDoesNotExists',
			'message' => 'The field `' . $field . '` does not exists'
		]); // TODO bad args
	}

	if (!isset($req->params->subfield)) {
		return $res->send($themes->read($req->params->name)->{$field});
	}

	$subfield = $req->params->subfield;

	if (!isset($theme->{$field}->{$subfield})) {
		return $res->status(404)->send([
			'error' => 'FieldDoesNotExists',
			'message' => 'The sub field `' . $subfield . '` does not exists'
		]); // TODO bad args
	}

	return $res->send($themes->read($req->params->name)->{$field}->{$subfield});
});
