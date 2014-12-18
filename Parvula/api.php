<?php

namespace Parvula;

use Parvula\Core\Page;
use Parvula\Core\Router;
use Parvula\Core\Config;
use Parvula\Core\Parvula;

if(!defined('ROOT')) exit;

$apiDefaultPageSerializer = Config::apiDefaultPageSerializer();

$parvula = new Parvula(new $apiDefaultPageSerializer);


//
// Public API
//

// Page object
$router->get('/pages/::name', function($req) use ($parvula) {
	echo $parvula->getPage($req->params->name);
});

// Array<Page> of Pages
$router->get('/pages', function($req) use ($parvula) {
	echo json_encode($parvula->getPages());
});


//
// Admin API
//
if(true === isParvulaAdmin()) {

	// List of pages. Array<string> of pages paths
	$router->get('/pageslist', function($req) use ($parvula) {
		echo json_encode($parvula->listPages());
	});

	// Delete page
	$router->delete('/pages/:name', function($req) use ($parvula) {
		echo json_encode($parvula->deletePage($req->params->name));
	});

	// Save page
	$router->put('/pages/:name', function($req) use ($parvula, $apiDefaultPageSerializer) {
		if(!isset($req->params->name) ||  trim($req->params->name) === '') {
			return false;
		}

		$page = Page::pageFactory($req->body);
		echo json_encode($parvula->setPage($page, $req->params->name, new $apiDefaultPageSerializer));
	});

	// Logout
	$router->any('/logout', function() {
		session_destroy();
		echo json_encode(session_unset());
	});

}
