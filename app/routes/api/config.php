<?php

namespace Parvula;

use Rs\Json\Patch;
use Rs\Json\Patch\InvalidPatchDocumentJsonException;
use Rs\Json\Patch\InvalidTargetDocumentJsonException;
use Rs\Json\Patch\InvalidOperationException;

// @ALPHA

$coreConfigs = [
	'database',
	'site',
	'system',
	'parvula',
];

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
	$config = app('configs')->find($args['name']);

	if (!$config) {
		return $this->api->json($res, ['error' => 'ConfigDoesNotExists'], 404);
	}

	return $this->api->json($res, $config);
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
	$config = app('configs')->find($args['name']);

	if (!$config) {
		return $this->api->json($res, ['error' => 'ConfigDoesNotExists'], 404);
	}

	$field = $args['field'];
	if (!isset($config[$field])) {
		return $this->api->json($res, [
			'error' => 'FieldError',
			'message' => 'The field `' . $field . '` does not exists'
		], 404);
	}

	return $this->api->json($res, $config[$args['field']]);
})->setName('configs.show.field');

/**
 * @api {post} /config Create a new config file
 * @apiName Create a config file
 * @apiGroup Config
 *
 * @apiParam {string} Config content
 *
 * @apiParamExample Request-Example:
 *     {
 *       "name": "myConfig",
 *       "data": {
 *         "field": "value"
 *       }
 *     }
 *
 * @apiSuccess (201) ConfigCreate
 * @apiError (409) ConfigAlreadyExists
 * @apiError (422) DataMalformed
 * @apiError (404) ConfigException If exception
 *
 * @apiSuccessExample ConfigCreated
 *     HTTP/1.1 201 No Content
 */
$this->post('', function ($req, $res, $args) {
	$parsedBody = $req->getParsedBody();
	$body = (array) $parsedBody;
	$repo = app('configs');

	if (!isset($body['name'], $body['data'])) {
		return $this->api->json($res, ['error' => 'DataMalformed'], 422);
	}

	if ($repo->find($body['name'])) {
		return $this->api->json($res, ['error' => 'ConfigAlreadyExists'], 409);
	}

	try {
		$repo->create($body);
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
	$repo = app('configs');

	// Config must exists
	if (!$repo->find($args['name'])) {
		return $this->api->json($res, [
			'error' => 'ConfigDoesNotExists',
			'message' => 'Configuration does not exists'
		], 404);
	}

	$config = (array) $parsedBody;

	try {
		$repo->update($args['name'], $config);
	} catch (Exception $e) {
		return $this->api->json($res, [
			'error' => 'ConfigException',
			'message' => $e->getMessage()
		], 404);
	}

	return $res->withStatus(204);
})->setName('configs.update');

/**
 * @api {patch} /config/:name Update specific field(s) of a config
 * @apiName Patch a config file
 * @apiGroup Config
 *
 * @apiParam {String} name Config name
 *
 * @apiParamExample Request-Example:
 *     [{
 *       "op": "replace",
 *       "path": "/field",
 *       "value": "New Name"
 *     }]
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
	$bodyJson = json_encode($req->getParsedBody());
	$name = $args['name'];

	$repo = app('configs');

	$config = $repo->find($name);

	// Config must exists
	if (!$config) {
		return $this->api->json($res, [
			'error' => 'ConfigDoesNotExists',
			'message' => 'Configuration does not exists'
		], 404);
	}

	$configJson = json_encode((object) $config);

	try {
		$patch = new Patch($configJson, $bodyJson);

		$patchedDocument = $patch->apply();

		$newConfig = json_decode($patchedDocument, true);

		$repo->update($name, $newConfig);

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

/*
 * @api {delete} /page/:name Delete a config.
 * @apiName Delete config
 * @apiGroup Config
 *
 * @apiSuccess (204) ConfigDeleted
 * @apiError (404) ConfigDoesNotExists If config does not exists and thus cannot be deleted
 * @apiError (404) ConfigCannotBeDeleted Core configurations cannot be deleted
 * @apiError (500) ConfigCannotBeDeleted Server side error
 */
$this->delete('/{name}', function ($req, $res, $args) use ($coreConfigs) {
	$name = urldecode($args['name']);

	if (in_array($name, $coreConfigs)) {
		return $this->api->json($res, [
			'error' => 'ConfigCannotBeDeleted',
			'message' => 'Core configurations cannot be deleted'
		], 404);
	}

	$repo = app('configs');

	// Config must exists
	if (!$repo->find($name)) {
		return $this->api->json($res, [
			'error' => 'ConfigDoesNotExists',
			'message' => 'Configuration does not exists'
		], 404);
	}

	if (!$repo->delete($name)) {
		return $this->api->json($res, [
			'error' => 'ConfigCannotBeDeleted',
			'message' => $e->getMessage()
		], 500);
	}

	return $res->withStatus(204);
});
