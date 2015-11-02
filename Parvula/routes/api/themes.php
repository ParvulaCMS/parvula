<?php

namespace Parvula;

use Exception;
use Parvula\Core\Exception\IOException;

$themes = $app['themes'];

/**
 * @api {get} /themes Request list of themes
 * @apiName Get Theme
 * @apiGroup Theme
 *
 * @apiSuccess {array} Array of Theme
 *
 * @apiSuccessExample {json} Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *       "status":"success",
 *       "data":{
 *         "mytheme":{..},
 *         "othertheme":{...}
 *       }
 *     }
 */
$router->get('', function($req) use ($themes) {
	return apiResponse(true, $themes->index());
});

/**
 * @api {get} /themes/:name Request Theme information
 * @apiName Get Theme
 * @apiGroup Theme
 *
 * @apiParam {String} name Theme unique name
 *
 * @apiSuccess {Theme} Theme object
 *
 * @apiSuccessExample {json} Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *       "status":"success",
 *       "data":{
 *         "layouts":{..},
 *         "name":"My theme",
 *         "infos":{
 *           "description":"...",
 *           "filesType":"php"
 *         }
 *       }
 *     }
 */
$router->get('/{name:\w+}', function($req) use ($themes) {
	return apiResponse(true, $themes->read($req->params->name));
});

 /**
  * @api {get} /themes/:name/:field/:subfield Request a specific Theme field/subfield
  * @apiName Get a Theme field
  * @apiGroup Theme
  *
  * @apiParam {String} name Theme unique name
  * @apiParam {String} field Theme field
  * @apiParam {String} [subfield] Optional Theme subfield
  *
  * @apiSuccess {Theme} Theme propriety
  *
  * @apiSuccessExample {json} Success-Response:
  *     HTTP/1.1 200 OK
  *     {
  *       "status":"success",
  *       "data":{
  *         "layouts":{"index":"index.html"}
  *       }
  *     }
  */
$router->get('/{name:\w+}/{field:\w+}[/{subfield:\w+}]', function($req) use ($themes) {
	$obj = $themes->read($req->params->name);

	$field = $req->params->field;

	if (!isset($obj->{$field})) {
		return apiResponse(false, 'The field `' . $field . '` does not exists'); // TODO bad args
	}

	if (!isset($req->params->subfield)) {
		return apiResponse(true, $themes->read($req->params->name)->{$field});
	}

	$subfield = $req->params->subfield;

	if (!isset($obj->{$field}->{$subfield})) {
		return apiResponse(false, 'The sub field `' . $subfield . '` does not exists'); // TODO bad args
	}

	return apiResponse(true, $themes->read($req->params->name)->{$field}->{$subfield});
});
