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
 * @apiSuccess {mixed} Config object
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
 * @apiSuccess {mixed} Config Field value
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
 * @api {patch} /:name Update specific field(s) of a config
 * @apiName Patch a config file
 * @apiGroup Config
 *
 * @apiParam {String} name Config name
 *
 * @apiParamExample Request-Example:
 *     myfield=My new value
 *
 * @apiSuccess ConfigPatched
 *     HTTP/1.1 200 OK
 *
 * @apiError Exception If exception
 *      HTTP/1.1 404
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

	return apiResponse(200);
});
