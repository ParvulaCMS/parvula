<?php

namespace Parvula;

// @ALPHA

$confIO = $app['fileParser'];

/**
 * Return config path if the config file exists
 *
 * @param string $name Config name
 * @return bool|string Config path or false if config does not exists
 */
function configPath($name) {
	if (!is_file($path = _CONFIG_ . basename($name . '.yaml'))) { // TODO not force .yaml config serivce
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
$router->get('/{name}', function ($req, $res) use ($confIO) {
	if (!configPath($req->params->name)) {
		return $res->status(404)->send(['error' => 'ConfigDoesNotExists']);
	}

	return $res->send($confIO->read(configPath($req->params->name)));
});

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
$router->get('/{name}/{field}', function ($req, $res) use ($confIO) {
	if (!$configName = configPath($req->params->name)) {
		return $res->status(404)->send(['error' => 'ConfigDoesNotExists']);
	}

	$config = (object) $confIO->read($configName);

	$field = $req->params->field;
	if (!isset($config->{$field})) {
		return $res->status(404)->send([
			'error' => 'FieldError',
			'message' => 'The field `' . $field . '` does not exists'
		]);
	}

	return $res->send($config->{$req->params->field});
});

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
$router->post('/{name}', function ($req, $res) use ($confIO) {
	if (configPath($req->params->name)) {
		return $res->status(409)->send(['error' => 'ConfigAlreadyExists']);
	}

	$path = _CONFIG_ . basename($req->params->name . '.yaml'); // TODO

	$config = (array) $req->body;

	try {
		$confIO->write($path, $config);
	} catch (Exception $e) {
		return $res->status(404)->send([
			'error' => 'ConfigException',
			'message' => $e->getMessage()
		]);
	}

	return $res->sendStatus(201);
});

/**
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
$router->patch('/{name}', function ($req, $res) use ($confIO) {
	if (!$configName = configPath($req->params->name)) {
		return $res->status(404)->send(['error' => 'ConfigDoesNotExists']);
	}

	$configOld = $confIO->read($configName);

	if (empty($configOld)) {
		$configOld = [];
	}

	$newFields = (array) $req->body;
	if ((array) $configOld === $configOld) { // is array
		$config = array_replace_recursive($configOld, $newFields);
	} else if (is_object($configOld)) {
		$config = (object) array_replace_recursive((array) $configOld, $newFields);
	} else {
		return $res->status(400)->send([
			'error' => 'InvalidData',
			'message' => 'Data type must be array or object'
		]);
	}

	try {
		$confIO->write($configName, $config);
	} catch (Exception $e) {
		return $res->status(404)->send([
			'error' => 'ConfigException',
			'message' => $e->getMessage()
		]);
	}

	return $res->sendStatus(204);
});
