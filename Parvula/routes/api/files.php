<?php

namespace Parvula;

use Exception;
use RuntimeException;
use Parvula\Core\FilesSystem;
use Parvula\Core\Exception\IOException;

// @ALPHA.2

$fs = new FilesSystem(_UPLOADS_);

/**
 * @api {get} /files Index files
 * @apiName Index Files
 * @apiGroup Files
 *
 * @apiSuccess (200) Array Array of files paths
 */
$router->get('', function ($req, $res) use ($fs) {
	try {
		return $res->send($fs->index());
	} catch(IOException $e) {
		return $res->status(500)->send([
			'error' => 'IOException',
			'message' => $e->getMessage()
		]);
	} catch(Exception $e) {
		return $res->status(500)->send([
			'error' => 'Exception',
			'message' => 'Server error'
		]);
	}
});

/**
 * @api {post} /files/upload Upload a file
 * @apiName Upload File
 * @apiGroup Files
 * @apiDescription Upload file(s) via multipart data upload
 *
 * @apiSuccess (204) FileUploaded File uploaded
 * @apiError (400) NoFileSent No file was sent
 * @apiError (400) FileSizeExceeded Exceeded file size limit
 * @apiError (400) FileNameError Exceeded file name limit
 * @apiError (500) InternalError
 * @apiError (500) UploadException
 */
$router->post('/upload', function ($req, $res) use ($app, $fs) {
	$config = $app['config'];

	try {
		// Undefined | Multiple Files | $files Corruption Attack
		// If this request falls under any of them, treat it invalid.
		if (
			!isset($req->files['file']['error']) ||
			is_array($req->files['file']['error'])
		) {
			throw new RuntimeException('Invalid parameters');
		}

		$file = $req->files['file'];

		if ($file['error'] === UPLOAD_ERR_NO_FILE || !isset($req->files['file'])) {
			return $res->status(400)->send([
				'error' => 'NoFileSent',
				'message' => 'No file was sent'
			]);
		}

		if (!$fs->isWritable()) {
			return $res->status(500)->send([
				'error' => 'InternalError',
				'message' => 'Upload folder is not writable'
			]);
		}

		// Check file name length
		if (strlen($file['name']) > 128) {
			return $res->status(400)->send([
				'error' => 'FileNameError',
				'message' => 'Exceeded file name limit'
			]);
		}

		// Filesize re-check
		$maxSize = $config->get('upload.maxSize') * 1000 * 1000;
		if ($maxSize >= 0 && $file['size'] > $maxSize) {
			// throw new RuntimeException('Exceeded file size limit');
			return $res->status(400)->send([
				'error' => 'FileSizeExceeded',
				'message' => 'Exceeded file size limit'
			]);
		}

		// @TODO Check $file['error'] value.
		switch ($file['error']) {
			case UPLOAD_ERR_OK:
				break;
			case UPLOAD_ERR_INI_SIZE:
			case UPLOAD_ERR_FORM_SIZE:
				throw new RuntimeException('Exceeded file size limit');
			default:
				throw new RuntimeException('Unknown errors');
		}

		$info = new \SplFileInfo($file['name']);
		$ext = $info->getExtension();
		$basename = $info->getBasename('.' . $ext);
		if (in_array($ext, $config->get('upload.evilExtensions'))) {
			$ext = 'txt';
			// throw new RuntimeException('File extension not allowed');
		}

		// Name should be unique // TODO
		if (!move_uploaded_file(
			$file['tmp_name'],
			sprintf('%s/%s.%s', _UPLOADS_, $basename, $ext)
		)) {
			throw new RuntimeException('Failed to move uploaded file');
		}

	} catch (RuntimeException $e) {
		return $res->status(500)->send([
			'error' => 'UploadException',
			'message' => $e->getMessage()
		]);
	}

	return $res->sendStatus(204);
});

/**
 * @api {delete} /files/:file delete file
 * @apiName Delete File
 * @apiGroup Files
 *
 * @apiParam {String} file File path to delete
 *
 * @apiSuccess (204) FileDeleted File deleted
 * @apiError (404) CannotBeDeleted File cannot be deleted
 */
$router->delete('/{file:.+}', function ($req, $res) use ($fs) {
	try {
		$result = $fs->delete($req->params->file);
	} catch (Exception $e) {
		return $res->status(404)->send([
			'error' => 'CannotBeDeleted',
			'message' => $e->getMessage()
		]);
	}
	return $res->sendStatus(204);
});
