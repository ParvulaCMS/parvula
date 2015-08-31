<?php

namespace Parvula;

use Parvula\Core\Page;
use Parvula\Core\Router;
use Parvula\Core\Config;
use Parvula\Core\Parvula;

if(!defined('ROOT')) exit;

$defaultPageSerializer = Config::defaultPageSerializer();

$parvula = new Parvula(new $defaultPageSerializer);

$apiSerializer = 'json_encode'; //@TODO

//
// Public API
//

// Page object
$router->get('/pages/::name', function($req) use ($parvula) {
	echo $parvula->getPage($req->params->name);
});

// Array<Page> of Pages
$router->get('/pages', function($req) use ($parvula) {
	echo $apiSerializer($parvula->getPages());
});

//
// Admin API
//
if(true === isParvulaAdmin()) {

	// List of pages. Array<string> of pages paths
	$router->get('/pageslist', function($req) use ($parvula) {
		echo $apiSerializer($parvula->listPages());
	});

	// Delete page
	$router->delete('/pages/::name', function($req) use ($parvula) {
		echo $apiSerializer($parvula->deletePage($req->params->name));
	});

	// Save page
	$router->put('/pages/::name', function($req) use ($parvula, $defaultPageSerializer) {
		if(!isset($req->params->name) || trim($req->params->name) === '') {
			return false;
		}

		$page = Page::pageFactory($req->body);
		echo $apiSerializer($parvula->setPage($page, $req->params->name, new $defaultPageSerializer));
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

		echo $apiSerializer($parvula->updatePage($page, $req->params->name, new $defaultPageSerializer));
	});

	// Logout
	$router->any('/logout', function() {
		session_destroy();
		echo $apiSerializer(session_unset());
	});

} else {
	// @TODO
	// echo '{"message": "Not found or not logged"}';
}
