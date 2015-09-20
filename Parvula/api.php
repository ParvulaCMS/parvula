<?php

namespace Parvula;

use Parvula\Core\Page;
use Parvula\Core\Router;
use Parvula\Core\Config;
use Parvula\Core\Parvula;
use Parvula\Core\PageManager;
use Parvula\Core\Exception\IOException;

if(!defined('ROOT')) exit;

$defaultPageSerializer = Config::defaultPageSerializer();

$pages = new PageManager(new $defaultPageSerializer);

// @TODO
function apiSerializer($data) {
	return json_encode($data);
}

// Send API message @TODO Temp
function apiMessage($responseCode = 200, $message = '') {
	if($responseCode === true) {
		$responseCode = 200;
	} else if($responseCode == false) {
		$responseCode = 400;
	}

	header('Content-Type: application/json');
	http_response_code($responseCode);
	if($responseCode < 300) {
		return '{"status": "ok", "message": "'.$message.'"}';
	} else {
		return '{"status": "error", "message": "'.$message.'"}';
	}
}

//
// Public API
//

// Page object (`?raw` to not parse the content)
$router->get('/pages/::name', function($req) use ($pages) {
	echo $pages->get($req->params->name, $req->query !== 'raw');
});

// Array<Page> of Pages
$router->get('/pages', function($req) use ($pages) {
	echo apiSerializer($pages->getAll());
});
});

//
// Admin API
//
if(true === isParvulaAdmin()) {

	// List of pages. Array<string> of pages paths
	$router->get('/pageslist', function($req) use ($pages) {
		return apiSerializer($pages->listPages());
	});

	// Delete page
	$router->delete('/pages/::name', function($req) use ($pages) {
		try {
			$res = $pages->delete($req->params->name);
		} catch(\Exception $e) {
			return apiMessage(404, $e->getMessage());
		}

		return apiMessage($res);
	});

	// Create page @TODO TEST
	$router->post('/pages/::name', function($req) use ($pages) {
		if(!isset($req->params->name) || trim($req->params->name) === '') {
			return false;
		}

		$page = Page::pageFactory($req->body);

		try {
			$res = $pages->set($page, $req->params->name);
		} catch(IOException $e) {
			return apiMessage(404, $e->getMessage());
		}

		return apiMessage(201);
	});

	// Update page
	$router->put('/pages/::name', function($req) use ($pages) {
		if(!isset($req->params->name) || trim($req->params->name) === '') {
			return false;
		}

		// Get old page and update new fields
		$page = $pages->get($req->params->name);
		foreach ($req->body as $key => $value) {
			$page->{$key} = $value;
		}

		$res = $pages->update($page, $req->params->name, new $defaultPageSerializer);
		return apiMessage($res);
	});

	// Upload file
	//TODO test - Security, etc.
	$router->post('/upload/images', function() {
		$uploadfile = IMAGES . basename($_FILES['file']['name']);

		// echo $uploadfile;
		// print_r($_FILES);

		if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {
			return apiMessage(true, 'image was successfully uploaded');
		} else {
			return apiMessage(false);
		}
	});

	// ANTHO
	// return images
	$router->get('/upload/images', function() {

		$files = [];
		foreach (glob(DATA . 'images' . '/*') as $file) {

			// !! absolute URLs for src attr.
			$name = $_SERVER['SERVER_NAME'];
			if($name === 'localhost') $name = 'http://' . $name;

			$files[] = $name . dirname($_SERVER['PHP_SELF']) . '/' . $file;
		}

		echo json_encode(['status' => 'ok', 'files' => $files]);
	});

	// ANTHO
	// delete image
	$router->delete('/upload/images', function($req) {

		$filename = basename($req->body['filename']);
		$res = ['status' => 'error', 'message' => 'fail'];

		foreach (glob(DATA . 'images' . '/*') as $file) {

			if(basename($file) === $filename) {

				unlink($file);
				$res = ['status' => 'ok', 'message' => 'file deleted'];
				break;
			}
		}

		echo json_encode($res);
	});

	// ANTHO get template
	// @todo get preferences? Template, etc?
	$router->get('/template', function() {

		$siteConf = Parvula::getUserConfig();
		if($siteConf) {
			return json_encode(['status' => 'ok', 'template' => $siteConf->template]);
		} else {
			return apiMessage(false, 'system error');
		}
	});

	// Logout
	$router->any('/logout', function() {
		session_destroy();
		return apiMessage(session_unset());
	});

} else {
	// @TODO
	// echo '{"message": "Not found or not logged"}';
}
