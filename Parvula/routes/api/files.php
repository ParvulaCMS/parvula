<?php

namespace Parvula;

use RuntimeException;
use Parvula\Core\FilesSystem;

// @ALPHA

/**
 * @api {get} /index Index files
 * @apiName Index Files
 * @apiGroup Files
 */
$router->get('/index', function($req) {
	$fs = new FilesSystem(UPLOADS);
	return apiResponse(200, $fs->index());
});
