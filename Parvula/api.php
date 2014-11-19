<?php

namespace Parvula;

use Parvula\Core\Page;
use Parvula\Core\Router;
use Parvula\Core\Parvula;
use Parvula\Core\MarkdownPageSerializer;

if(!defined('ROOT')) exit;

$parvula = new Parvula(new MarkdownPageSerializer);


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
	$router->put('/pages/:name', function($req) use ($parvula) {
		if(!isset($req->params->name) ||  trim($req->params->name) === '') {
			return false;
		}

		$page = Page::pageFactory($req->body);
		echo json_encode($parvula->setPage($page, $req->params->name, new MarkdownPageSerializer));
	});

	// Logout
	$router->any('/logout', function() {
		session_destroy();
		echo json_encode(session_unset());
	});

}
