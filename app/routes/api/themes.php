<?php

namespace Parvula;

use Exception;
use Parvula\Exceptions\IOException;

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
$this->get('', function ($req, $res) use ($themes) {
	return $this->api->json($res, $themes->index());
})->setName('themes.index');

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
$this->get('/{name}', function ($req, $res, $args) use ($themes) {
	if (false === $result = $themes->read($args['name'])) {
		return $this->api->json($res, [
			'error' => 'ThemeNotFound'
		], 404);
	}

	return $this->api->json($res, $result);
})->setName('themes.show');

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
$this->get('/{name}/{field}[/{subfield}]', function ($req, $res, $args) use ($themes) {
	$theme = $themes->read($args['name']);

	$field = $args['field'];

	if (!isset($theme->{$field})) {
		return $this->api->json($res, [
			'error' => 'FieldDoesNotExists',
			'message' => 'The field `' . $field . '` does not exists'
		], 404); // TODO bad args
	}

	if (!isset($args['subfield'])) {
		return $this->api->json($res, $themes->read($args['name'])->{$field});
	}

	$subfield = $args['subfield'];

	if (!isset($theme->{$field}->{$subfield})) {
		return $this->api->json($res, [
			'error' => 'FieldDoesNotExists',
			'message' => 'The sub field `' . $subfield . '` does not exists'
		], 404); // TODO bad args
	}

	return $this->api->json($res, $themes->read($args['name'])->{$field}->{$subfield});
})->setName('themes.show.field');
