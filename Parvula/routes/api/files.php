<?php

namespace Parvula;

use Exception;
use RuntimeException;
use Parvula\Core\FilesSystem;

// @ALPHA

$fs = new FilesSystem(UPLOADS);

/**
 * @api {post} /upload Upload a file
 * @apiName Upload File
 * @apiGroup Files
 */
$router->post('/upload', function($req) {

	// header('Content-Type: text/plain; charset=utf-8');

	$evilExt = ['php', 'html', 'htm'];

	$file = $req->files['file'];

	try {

		// Undefined | Multiple Files | $files Corruption Attack
		// If this request falls under any of them, treat it invalid.
		if (
			!isset($file['error']) ||
			is_array($file['error'])
		) {
			throw new RuntimeException('Invalid parameters');
		}

		// Check $files['file0']['error'] value.
		switch ($file['error']) {
			case UPLOAD_ERR_OK:
				break;
			case UPLOAD_ERR_NO_FILE:
				throw new RuntimeException('No file sent');
			case UPLOAD_ERR_INI_SIZE:
			case UPLOAD_ERR_FORM_SIZE:
				throw new RuntimeException('Exceeded filesize limit');
			default:
				throw new RuntimeException('Unknown errors');
		}

		// You should also check filesize here.
		if ($file['size'] > 8000000) {
			// throw new RuntimeException('Exceeded file size limit');
			return apiResponse(400, 'Exceeded file size limit');
		}

		$info = new \SplFileInfo($file['name']);
		// $info = new \SplFileInfo($files['file0']['tmp_name']);
		$ext = $info->getExtension();
		$basename = $info->getBasename('.' . $ext);
		if (in_array($ext, $evilExt)) {
			$ext = 'txt';
			// throw new RuntimeException('File extension not allowed');
		}

		// You should name it uniquely.
		// DO NOT USE $files['file0']['name'] WITHOUT ANY VALIDATION !!
		// On this example, obtain safe unique name from its binary data.
		if (!move_uploaded_file(
			$file['tmp_name'],
			sprintf('%s/%s.%s', UPLOADS, $basename, $ext)
		)) {
			throw new RuntimeException('Failed to move uploaded file');
		}

	} catch (RuntimeException $e) {
		// echo $e->getMessage();
		return apiResponse(400, $e->getMessage());
	}

	//  File was uploaded successfully
	return apiResponse(204);
});

/**
 * @api {get} /index Index files
 * @apiName Index Files
 * @apiGroup Files
 */
$router->get('/index', function($req) use ($fs) {
	//TODO [] if no files
	return apiResponse(200, $fs->index());
});

/**
 * @api {delete} /:file delete file
 * @apiName Delete File
 * @apiGroup Files
 */
$router->delete('/{file:.+}', function($req) use ($fs) {
	try {
		$res = $fs->delete($req->params->file);
	} catch(Exception $e) {
		return apiResponse(404, $e->getMessage());
	}
	return apiResponse(204);
});
