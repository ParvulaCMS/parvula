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
 */
$router->get('/{name}', function($req) use ($confIO) {
	if (!configPath($req->params->name)) {
		return apiResponse(404, 'Config does not exists');
	}

	return apiResponse(200, $confIO->read(configPath($req->params->name)));
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
$router->get('/{name}/{field}', function($req) use ($confIO) {
	if (!$configName = configPath($req->params->name)) {
		return apiResponse(404, 'Config does not exists');
	}

	$config = (object) $confIO->read($configName);

	$field = $req->params->field;
	if (!isset($config->{$field})) {
		return apiResponse(404, 'The field `' . $field . '` does not exists');
	}

	return apiResponse(200, $config->{$req->params->field});
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
 * @apiError (404) Exception If exception
 *
 * @apiSuccessExample ConfigPatched
 *     HTTP/1.1 204
 */
$router->patch('/{name}', function($req) use ($confIO) {
	if (!$configName = configPath($req->params->name)) {
		return apiResponse(404, 'Config does not exists');
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
		return apiResponse(404, $e->getMessage());
	}

	return apiResponse(204);
});
