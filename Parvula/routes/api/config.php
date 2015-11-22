<?php

namespace Parvula;

// @ALPHA

$confIO = $app['fileParser'];

function configPath($name) {
	if ($name === 'site') {
		return DATA . 'site.conf.yaml';
	}

	return false;
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

	$config = $confIO->read($configName);

	$fields = $req->body;
	if (is_array($config)) {
		foreach ($fields as $key => $value) {
			$config[$key] = $value;
		}
	} else if (is_object($config)) {
		foreach ($fields as $key => $value) {
			$config->{$key} = $value;
		}
	}

	try {
		$confIO->write($configName, (array)$config);
	} catch(Exception $e) {
		return $res->status(404)->send([
			'error' => 'ConfigException',
			'message' => $e->getMessage()
		]);
	}

	return $res->sendStatus(204);
});
