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
 *
 * @apiSuccess (204) File uploaded
 * @apiError (400) NoFileUploaded
 * @apiError (400) FileSizeExceeded
 * @apiError (400) UploadException
 */
$router->post('/upload', function ($req, $res) {

	// header('Content-Type: text/plain; charset=utf-8');

	$evilExt = ['php', 'html', 'htm'];

	if (!isset($req->files['file'])) {
		return $res->status(400)->send([
			'error' => 'NoFileUploaded'
		]);
	}

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

		// Filesize check
		if ($file['size'] > 8000000) {
			// throw new RuntimeException('Exceeded file size limit');
			return $res->status(400)->send([
				'error' => 'FileSizeExceeded',
				'message' => 'Exceeded file size limit'
			]);
		}

		$info = new \SplFileInfo($file['name']);
		// $info = new \SplFileInfo($files['file0']['tmp_name']);
		$ext = $info->getExtension();
		$basename = $info->getBasename('.' . $ext);
		if (in_array($ext, $evilExt)) {
			$ext = 'txt';
			// throw new RuntimeException('File extension not allowed');
		}

		// Name should be unique // TODO
		// DO NOT USE $files['file0']['name'] WITHOUT ANY VALIDATION
		if (!move_uploaded_file(
			$file['tmp_name'],
			sprintf('%s/%s.%s', UPLOADS, $basename, $ext)
		)) {
			throw new RuntimeException('Failed to move uploaded file');
		}

	} catch (RuntimeException $e) {
		return $res->status(400)->send([
			'error' => 'UploadException',
			'message' => $e->getMessage()
		]);
	}

	return $res->sendStatus(204);
});

/**
 * @api {get} /index Index files
 * @apiName Index Files
 * @apiGroup Files
 *
 * @apiSuccess (200) Files index
 */
$router->get('/index', function ($req, $res) use ($fs) {
	//TODO [] if no files
	return $res->send($fs->index());
});

/**
 * @api {delete} /:file delete file
 * @apiName Delete File
 * @apiGroup Files
 *
 * @apiSuccess (204) File deleted
 * @apiError (404) CannotBeDeleted
 */
$router->delete('/{file:.+}', function ($req, $res) use ($fs) {
	try {
		$res = $fs->delete($req->params->file);
	} catch (Exception $e) {
		return $res->status(404)->send([
			'error' => 'CannotBeDeleted',
			'message' => $e->getMessage()
		]);
	}
	return $res->sendStatus(204);
});
