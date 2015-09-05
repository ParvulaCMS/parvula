<?php

namespace Parvula;

use Parvula\Core\Page;
use Parvula\Core\Router;
use Parvula\Core\Config;
use Parvula\Core\Parvula;

if(!defined('ROOT')) exit;

$defaultPageSerializer = Config::defaultPageSerializer();

$parvula = new Parvula(new $defaultPageSerializer);

// @TODO
function apiSerializer($data) {
	return json_encode($data);
}

function apiMessage($res, $message = '') {
	if($res) {
		return '{"status": "ok", "message": "'.$message.'"}';
	} else {
		return '{"status": "error", "message": "'.$message.'"}';
	}
}

//
// Public API
//

// Page object (`?raw` to not parse the content)
$router->get('/pages/::name', function($req) use ($parvula) {
	echo $parvula->getPage($req->params->name, $req->query !== 'raw');
});

// Array<Page> of Pages
$router->get('/pages', function($req) use ($parvula) {
	echo apiSerializer($parvula->getPages());
});

//
// Admin API
//
if(true === isParvulaAdmin()) {

	// List of pages. Array<string> of pages paths
	$router->get('/pageslist', function($req) use ($parvula) {
		echo apiSerializer($parvula->listPages());
	});

	// Delete page
	$router->delete('/pages/::name', function($req) use ($parvula) {
		$res = $parvula->deletePage($req->params->name);
		echo apiMessage($res);
	});

	//https://stackoverflow.com/questions/630453/put-vs-post-in-rest

	// Save page
	$router->put('/pages/::name', function($req) use ($parvula, $defaultPageSerializer) {
		if(!isset($req->params->name) || trim($req->params->name) === '') {
			return false;
		}

		$page = Page::pageFactory($req->body);
		$res = $parvula->setPage($page, $req->params->name, new $defaultPageSerializer);
		echo apiMessage($res);
	});

	// Update page @TODO TEST
	$router->post('/pages/::name', function($req) use ($parvula, $defaultPageSerializer) {
		if(!isset($req->params->name) || trim($req->params->name) === '') {
			return false;
		}

		// Get old page and update new fields
		$page = $parvula->getPage($req->params->name);
		foreach ($req->body as $key => $value) {
			$page->{$key} = $value;
		}

		$res = $parvula->updatePage($page, $req->params->name, new $defaultPageSerializer);
		echo apiMessage($res);
	});

	// Upload file
	//TODO test - Security, etc.
	$router->post('/upload/images', function() {
		$uploadfile = IMAGES . basename($_FILES['file']['name']);

		// echo $uploadfile;
		// print_r($_FILES);

		if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {
			echo apiMessage(true, 'image was successfully uploaded');
		} else {
			echo apiMessage(false);
		}
	});

	// Logout
	$router->any('/logout', function() {
		session_destroy();
		echo apiMessage(session_unset());
	});

} else {
	// @TODO
	// echo '{"message": "Not found or not logged"}';
}
