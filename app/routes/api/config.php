<?php

namespace Parvula;

use Rs\Json\Patch;
use Rs\Json\Patch\InvalidPatchDocumentJsonException;
use Rs\Json\Patch\InvalidTargetDocumentJsonException;
use Rs\Json\Patch\InvalidOperationException;

// @ALPHA

$confIO = $app['fileParser'];

/**
 * Return config path if the config file exists
 *
 * @param string $name Config name
 * @return bool|string Config path or false if config does not exists
 */
function configPath($name) {
	if (!is_file($path = _CONFIG_ . basename($name . '.yml'))) { // TODO not force .yml config serivce
		return false;
	}

	return $path;
}

/**
 * @api {get} /config/:name Get config
 * @apiName Get Config
 * @apiGroup Config
 *
 * @apiParam {String} name Config name
 *
 * @apiSuccess (200) {mixed} Config object
 * @apiError (404) ConfigDoesNotExists
 */
$this->get('/{name}', function ($req, $res, $args) {
	if (!configPath($args['name'])) {
		return $this->api->json($res, ['error' => 'ConfigDoesNotExists'], 404);
	}

	return $this->api->json($res, app('fileParser')->read(configPath($args['name'])));
})->setName('configs.show');

/**
* @api {get} /config/:name/:field Get config field
* @apiName Get Config Field
* @apiGroup Config
 *
 * @apiParam {String} name Config name
 * @apiParam {String} field Field name
 *
 * @apiSuccess (200) {mixed} Config Field value
 * @apiError (404) ConfigDoesNotExists Config does not exists
 * @apiError (404) FieldError The field :field does not exists
 */
$this->get('/{name}/{field}', function ($req, $res, $args) {
	if (!$configName = configPath($args['name'])) {
		return $this->api->json($res, ['error' => 'ConfigDoesNotExists'], 404);
	}

	$config = (object) app('fileParser')->read($configName);

	$field = $args['field'];
	if (!isset($config->{$field})) {
		return $this->api->json($res, [
			'error' => 'FieldError',
			'message' => 'The field `' . $field . '` does not exists'
		], 404);
	}

	return $this->api->json($res, $config->{$args['field']});
})->setName('configs.show.field');

/**
 * @api {post} /config/:name Create a new config file
 * @apiName Create a config file
 * @apiGroup Config
 *
 * @apiParam {string} Config content
 *
 * @apiParamExample Request-Example:
 *     myfield=My new value&other=123
 *
 * @apiSuccess (201) ConfigCreate
 * @apiError (409) ConfigAlreadyExists
 * @apiError (404) ConfigException If exception
 *
 * @apiSuccessExample ConfigCreated
 *     HTTP/1.1 201 No Content
 */
$this->post('/{name}', function ($req, $res, $args) {
	$parsedBody = $req->getParsedBody();

	if (configPath($args['name'])) {
		return $this->api->json($res, ['error' => 'ConfigAlreadyExists'], 409);
	}

	$path = configPath($args['name']); // TODO

	$config = (array) $parsedBody;

	try {
		app('fileParser')->write($path, $config);
	} catch (Exception $e) {
		return $this->api->json($res, [
			'error' => 'ConfigException',
			'message' => $e->getMessage()
		], 404);
	}

	return $res->withStatus(201);
})->setName('configs.create');

// TODO @DEV
/**
 * @api {put} /config/:name Update a config
 * @apiDescription Config file **must** exists to be updated
 * @apiName Update config
 * @apiGroup Config
 */
$this->put('/{name}', function ($req, $res, $args) {
	$parsedBody = $req->getParsedBody();

	// Config must exists
	if (!configPath($args['name'])) {
		return $this->api->json($res, ['error' => 'ConfigDoesNotExists'], 404);
	}

	$path = configPath($args['name']); // TODO

	$config = (array) $parsedBody;

	try {
		app('fileParser')->write($path, $config);
	} catch (Exception $e) {
		return $this->api->json($res, [
			'error' => 'ConfigException',
			'message' => $e->getMessage()
		], 404);
	}

	return $res->withStatus(200);
})->setName('configs.update');

/**
 * TODO - jsonpatch doc
 * @api {patch} /config/:name Update specific field(s) of a config
 * @apiName Patch a config file
 * @apiGroup Config
 *
 * @apiParam {String} name Config name
 *
 * @apiParamExample Request-Example:
 *     myfield=My new value
 *
 * @apiSuccess (204) ConfigPatched
 * @apiError (400) InvalidData Data type must be array or object
 * @apiError (404) ConfigDoesNotExists
 * @apiError (404) ConfigException If exception
 *
 * @apiSuccessExample ConfigPatched
 *     HTTP/1.1 204 No Content
 */
$this->patch('/{name}', function ($req, $res, $args) {
	$parsedBody = $req->getParsedBody();
	$bodyJson = json_encode($req->getParsedBody());
	$name = $args['name'];

	// Config must exists
	if (!($path = configPath($name))) {
		return $this->api->json($res, [
			'error' => 'ConfigDoesNotExists'
		], 404);
	}

	$configJson = json_encode((object) app('fileParser')->read(configPath($name)));

	try {
		$patch = new Patch($configJson, $bodyJson);

		$patchedDocument = $patch->apply();

		$newConfig = (json_decode($patchedDocument, true));

		$confIO->write($path, $newConfig);

		return $res->withStatus(204);
	} catch (InvalidPatchDocumentJsonException $e) {
		return $this->api->json($res, [
			'error' => 'InvalidPatchDocumentJsonException',
			'message' => $e->getMessage()
		], 400);
	} catch (InvalidTargetDocumentJsonException $e) {
		return $this->api->json($res, [
			'error' => 'InvalidTargetDocumentJsonException',
			'message' => $e->getMessage()
		], 400);
	} catch (InvalidOperationException $e) {
		return $this->api->json($res, [
			'error' => 'InvalidOperationException',
			'message' => $e->getMessage()
		], 400);
	}
})->setName('configs.patch');
