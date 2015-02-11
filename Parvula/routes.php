<?php
// ----------------------------- //
// Routes (controller)
// ----------------------------- //

use Parvula\Core\View;
use Parvula\Core\Config;
use Parvula\Core\Parvula;


$adminURL = trim(Config::get('adminURL'), '/');
if($adminURL . '/' === ADMIN) {
	// Avoid redirection loop
	$adminURL .= '_';
}

// Admin pages
$router->any('/' . $adminURL . '/', function() {
	return require ADMIN . 'admin.php';
})
// redirection, need the trailing slash
->get('/' . $adminURL, function() use($adminURL) {
	header('Location: ./' . $adminURL . '/', true, 303);
});


// Api namespace
$router->space('/_api', function($router) {
	return require APP . 'api.php';
});


// Front - Pages
$router->get('*', function($req) use($config) {

	$pagename = rtrim($req->path, '/');
	$pagename = urldecode($pagename);

	if($pagename === '') {
		$pagename = Config::homePage();
	}

	// Check if template exists (must have index.html)
	$baseTemplate = TMPL . Config::get('template');
	if(!is_readable($baseTemplate . '/index.html')) {
		die("Error - Template is not readable");
	}

	Asset::setBasePath(Parvula::getRelativeURIToRoot() . $baseTemplate);

	$parvula = new Parvula;
	$page = $parvula->getPage($pagename);

	// 404
	if(false === $page) {
		header(' ', true, 404); // Set header to 404
		$page = $parvula->getPage(Config::errorPage());

		if(false === $page) {
			// Juste print simple 404 if there is no 404 page
			die('404 - Page ' . htmlspecialchars($page) . ' not found');
		}
	}

	try {
		$view = new View(TMPL . Config::get('template'));

		// Assign some variables
		$view->assign(array(
			'baseUrl' => Parvula::getRelativeURIToRoot(),
			'templateUrl' => Asset::getBasePath(),
			'parvula' => $parvula,
			'pages' => function() use($parvula) { return $parvula->getPages(); },
			'site' => $config,
			'meta' => $page,
			'content' => $page->content
		));

		// Show index template
		echo $view('index');

	} catch(Exception $e) {
		exceptionHandler($e);
	}
});
