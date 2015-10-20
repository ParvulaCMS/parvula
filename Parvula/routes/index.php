<?php

use Parvula\Core\Parvula;
use Parvula\Core\Model\PagesFlatFiles;

// Front - Pages
$router->map('GET|POST', '/{slug:.*}', function($req) use($app) {

	$slug = rtrim($req->params->slug, '/');
	$slug = urldecode($slug);

	$plugins = $app['plugins'];
	$plugins->trigger('uri', [$req->params->slug]);
	$plugins->trigger('slug', [$slug]);

	if($slug === '') {
		$slug = $app['config']->get('homePage');
	}

	$themes = $app['themes'];

	if ($themes->has($themeName = $app['config']->get('theme'))) {
		$theme = $themes->read($themeName);
	} else {
		throw new Exception('Theme does not exists');
	}

	$pages = $app['pages'];

	$page = $pages->read($slug, true);
	$plugins->trigger('page', [&$page]);

	// 404
	if(false === $page) {
		// header(' ', true, 404); // Set header to 404
		$page = $pages->read($app['config']->get('errorPage'));
		$plugins->trigger('404', [&$page]);

		if(false === $page) {
			// Juste print simple 404 if there is no 404 page
			die('404 - Page ' . htmlspecialchars($page) . ' not found');
		}
	}

	try {
		// Create new Plates instance to render theme html files
		$view = new League\Plates\Engine($theme->getPath(), 'html');

		// Assign some useful variables
		$view->addData([
			'baseUrl' => Parvula::getRelativeURIToRoot(),
			'themeUrl' => Parvula::getRelativeURIToRoot() . $theme->getPath() . '/',
			'pages' =>
				function($listHidden = false, $pagesPath = null) use($pages) {
					return $pages->all($pagesPath)->visible()->order(SORT_ASC)->toArray();
				},
			'plugin' =>
				function($name) use($plugins) {
					return $plugins->getPlugin($name);
				},
			'site' => $app['config']->toObject(),
			'self' => $page,
			'__time__' => function() use($app) {
				// useful to benchmark
				return sprintf('%.4f', $app['config']->get('__time__') + microtime(true));
			}
		]);

		if(isset($page->layout)) {
			$layout = $page->layout;
		} else {
			$layout = 'index';
		}

		$plugins->trigger('BeforeRender', [&$layout]);
		$out = $view->render($layout);
		$plugins->trigger('AfterRender', [&$out]);
		echo $out;

	} catch(Exception $e) {
		exceptionHandler($e);
	}
});
