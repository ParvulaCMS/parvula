<?php

// use Parvula\Core\Config;
use Parvula\Core\Model\Themes;
use Parvula\Core\Parvula;
use Parvula\Core\Model\Pages;

// Front - Pages
$router->any('*', function($req) use($config, $med) {
	$med->trigger('uri', [$req->uri]);

	$pagename = rtrim($req->uri, '/');
	$pagename = urldecode($pagename);

	if($pagename === '') {
		$pagename = $config->get('homePage');
	}

	$themes = new Themes(THEMES);
	$themes->read($config->get('theme'));

	// Check if theme exists (must have index.html)
	$baseTheme = htmlspecialchars(THEMES . $config->get('theme'));

	if(!is_readable($baseTheme . '/index.html')) {
		die("Error - Theme `{$baseTheme}` is not readable");
	}

	$pages = new Pages($config);
	$page = $pages->get($pagename, true);
	$med->trigger('page', [&$page]);

	// 404
	if(false === $page) {
		// header(' ', true, 404); // Set header to 404
		$page = $pages->get($config->get('errorPage'));
		$med->trigger('404', [&$page]);

		if(false === $page) {
			// Juste print simple 404 if there is no 404 page
			die('404 - Page ' . htmlspecialchars($page) . ' not found');
		}
	}

	try {
		// Create new Plates instance to render theme html files
		$templates = new League\Plates\Engine($baseTheme, 'html');

		// Assign some useful variables
		$templates->addData([
			'baseUrl' => Parvula::getRelativeURIToRoot(),
			'themeUrl' => Parvula::getRelativeURIToRoot() . $baseTheme . '/',
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
