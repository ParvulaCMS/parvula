<?php

namespace Parvula;

use Exception;
use SplFileInfo;
use RuntimeException;
use Parvula\FilesSystem;
use Parvula\Exceptions\IOException;

// @ALPHA.2

$fs = new FilesSystem(_UPLOADS_);

/**
 * @api {get} /files Index files
 * @apiName Index Files
 * @apiGroup Files
 *
 * @apiParam {string} [full] Query `?full` to get more details
 *
 * @apiSuccess (200) String[] Array of files paths
 */
$this->get('', function ($req, $res) use ($fs) {
	///https://weierophinney.github.io/2015-10-20-PSR-7-and-Middleware/#/35
	//->getUploadedFiles
	try {
		// Detailed view
		if (isset($req->getQueryParams()['full'])) {
			$files = [];
			$fs->index('', function (SplFileInfo $file, $cdir) use (&$files) {
				$files[] = [
					'filename' => $file->getFileName(),
					'path' => (string) $cdir,
					'lastedit' => $file->getMTime(),
					'size' => $file->getSize(),
					'type' => $file->getType()
				];
			});

			return $this->api->json($res, $files);
		}

		return $this->api->json($res, $fs->index());
	} catch (IOException $e) {
		return $this->api->json($res, [
			'error' => 'IOException',
			'message' => $e->getMessage()
		], 500);
	} catch (Exception $e) {
		return $this->api->json($res, [
			'error' => 'Exception',
			'message' => 'Server error'
		], 500);
	}
});

/**
 * @api {post} /files/upload Upload a file
 * @apiName Upload File
 * @apiGroup Files
 * @apiDescription Upload file(s) via multipart data upload
 *
 * @apiParam {mixed} file File to upload
 * @apiParam {string} [path] Query `?path` to upload in a specific sub folder
 *
 * @apiSuccess (201) FileUploaded File uploaded
 * @apiError (400) NoFileSent No file was sent
 * @apiError (400) FileSizeExceeded Exceeded file size limit
 * @apiError (400) FileNameError Exceeded file name limit
 * @apiError (500) InternalError
 * @apiError (500) UploadException
 *
 * @apiSuccessExample Success-Response:
 *     HTTP/1.1 201 OK
 *     {
 *       "filename": "supercat.png",
 *       "directory": "static\/files"
 *     }
 */
$this->post('/upload', function ($req, $res) use ($app, $fs) {
	$config = $app['config'];

	$files = $req->getUploadedFiles();

	try {
		if (empty($files['file'])) {
			return $this->api->json($res, [
				'error' => 'NoFileSent',
				'message' => 'No file was sent'
			], 400);
		}

		if (!$fs->isWritable()) {
			return $this->api->json($res, [
				'error' => 'InternalError',
				'message' => 'Upload folder is not writable'
			], 500);
		}

		$file = $files['file'];

		$path = '';
		if (isset($_GET['path']) && !empty($_GET['path'])) {
			$path = $_GET['path'];
			$path = str_replace(['.', "\0"], '', $path);
			$path = trim($path, '/\\') . '/';
		}

		// Check file name length
		if (strlen($file->getClientFilename()) > 128) {
			return $this->api->json($res, [
				'error' => 'FileNameError',
				'message' => 'Exceeded file name limit'
			], 400);
		}

		// Filesize check
		$maxSize = $config->get('upload.maxSize') * 1000 * 1000;
		if ($maxSize >= 0 && $file->getSize() > $maxSize) {
			return $this->api->json($res, [
				'error' => 'FileSizeExceeded',
				'message' => 'Exceeded file size limit'
			], 400);
		}

		switch ($file->getError()) {
			case UPLOAD_ERR_OK:
				break;
			case UPLOAD_ERR_INI_SIZE:
			case UPLOAD_ERR_FORM_SIZE:
				throw new RuntimeException('Exceeded file size limit');
			default:
				throw new RuntimeException('Unknown errors');
		}

		$info = new \SplFileInfo($file->getClientFilename());
		$ext = $info->getExtension();
		$basename = $info->getBasename('.' . $ext);
		if (in_array($ext, $config->get('upload.evilExtensions'))) {
			$ext = 'txt';
		}

		// Name should be unique // TODO
		$filename =  $basename . '.' . $ext;

		if (!is_dir(_UPLOADS_ . $path)) {
			mkdir(_UPLOADS_ . $path, 0777, true);
		}

		$file->moveTo(_UPLOADS_ . $path . $filename);

	} catch (RuntimeException $e) {
		return $this->api->json($res, [
			'error' => 'UploadException',
			'message' => $e->getMessage()
		], 500);
	}

	return $this->api->json($res, [
		'filename' => $filename,
		'directory' => _UPLOADS_ . $path
	], 201);
});

/**
 * @api {delete} /files/:file Delete a file
 * @apiName Delete File
 * @apiGroup Files
 *
 * @apiParam {String} file File path to delete
 *
 * @apiSuccess (204) FileDeleted File deleted
 * @apiError (404) CannotBeDeleted File cannot be deleted
 */
$this->delete('/{file:.+}', function ($req, $res, $args) use ($fs) {
	try {
		$file = urldecode($args['file']);
		$result = $fs->delete($file);
	} catch (Exception $e) {
		return $this->api->json($res, [
			'error' => 'CannotBeDeleted',
			'message' => $e->getMessage()
		], 404);
	}
	return $res->withStatus(204);
});
