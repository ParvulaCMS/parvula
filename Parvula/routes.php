<?php
// ----------------------------- //
// Routes (controller)
// ----------------------------- //

use Parvula\Core\Config;
use Parvula\Core\Parvula;
use Parvula\Core\Model\Pages;

$med->trigger('router', [&$router]);
$med->trigger('route', [$router->getMethod(), $router->getUri()]);


// Api namespace
$router->space('/_api', function($router) {
	return require APP . 'routes/api.php';
});


// Front - Pages
$router->any('*', function($req) use($config, $med) {
	$med->trigger('path', [$req->path]);

	$pagename = rtrim($req->path, '/');
	$pagename = urldecode($pagename);

	if($pagename === '') {
		$pagename = Config::homePage();
	}

	// Check if template exists (must have index.html)
	$baseTemplate = htmlspecialchars(THEMES . Config::get('template'));
	if(!is_readable($baseTemplate . '/index.html')) {
		die("Error - Template `{$baseTemplate}` is not readable");
	}

	// Asset::setBasePath(Parvula::getRelativeURIToRoot() . $baseTemplate);

	$pages = new Pages;
	$page = $pages->get($pagename, true);
	$med->trigger('Page', [&$page]);

	// 404
	if(false === $page) {
		header(' ', true, 404); // Set header to 404
		$page = $pages->get(Config::errorPage());
		$med->trigger('404', [&$page]);

		if(false === $page) {
			// Juste print simple 404 if there is no 404 page
			die('404 - Page ' . htmlspecialchars($page) . ' not found');
		}
	}

	try {
		// Create new Plates instance to render template html files
		$templates = new League\Plates\Engine($baseTemplate, 'html');

		// Assign some useful variables
		$templates->addData([
			'baseUrl' => Parvula::getRelativeURIToRoot(),
			'templateUrl' => Parvula::getRelativeURIToRoot() . $baseTemplate . '/',
			'pages' =>
				function($listHidden = false, $pagesPath = null) use($pages) {
					return $pages->getAll($listHidden, $pagesPath);
				},
			'plugin' =>
				function($name) use($med) {
					return $med->getPlugin($name);
				},
			'site' => $config,
			'self' => $page
		]);

		if(isset($page->layout)) {
			$layout = $page->layout;
		} else {
			$layout = 'index';
		}

		$med->trigger('BeforeRender', [&$layout]);
		$out = $templates->render($layout);
		$med->trigger('AfterRender', [&$out]);
		echo $out;

	} catch(Exception $e) {
		exceptionHandler($e);
	}
});
