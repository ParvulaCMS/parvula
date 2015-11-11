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
 *       "mytheme": {..},
 *       "othertheme": {...}
 *     }
 */
$router->get('', function($req) use ($themes) {
	return apiResponse(200, $themes->index());
});

/**
 * @api {get} /themes/:name Theme information
 * @apiName Get Theme
 * @apiGroup Theme
 *
 * @apiParam {String} name Theme unique name
 *
 * @apiSuccess (200) {Object} Theme object
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
$router->get('/{name}', function($req) use ($themes) {
	return apiResponse(200, $themes->read($req->params->name));
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
  *
  * @apiSuccessExample {json} Success-Response:
  *     HTTP/1.1 200 OK
  *     {
  *       "layouts": {"index":"index.html"}
  *     }
  */
$router->get('/{name}/{field}[/{subfield}]', function($req) use ($themes) {
	$theme = $themes->read($req->params->name);

	$field = $req->params->field;

	if (!isset($theme->{$field})) {
		return apiResponse(false, 'The field `' . $field . '` does not exists'); // TODO bad args
	}

	if (!isset($req->params->subfield)) {
		return apiResponse(200, $themes->read($req->params->name)->{$field});
	}

	$subfield = $req->params->subfield;

	if (!isset($theme->{$field}->{$subfield})) {
		return apiResponse(false, 'The sub field `' . $subfield . '` does not exists'); // TODO bad args
	}

	return apiResponse(200, $themes->read($req->params->name)->{$field}->{$subfield});
});
