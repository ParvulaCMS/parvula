<?php

use Parvula\Core\Parvula;
use Parvula\Core\Model\PagesFlatFiles;

// Pages handler (slug must be `a-z0-9-_+/`)
$router->map('GET|POST', '/{slug:[a-z0-9\-_\+\/]*}', function($req) use($app) {

	$view = $app['view'];
	$pages = $app['pages'];
	$theme = $app['theme'];
	$plugins = $app['plugins'];

	$slug = rtrim($req->params->slug, '/');
	$slug = urldecode($slug);

	$plugins->trigger('uri', [$req->params->slug]);
	$plugins->trigger('slug', [$slug]);

	if ($slug === '') {
		$slug = $app['config']->get('homePage');
	}

	$page = $pages->read($slug, true);
	$plugins->trigger('page', [&$page]);

	// 404
	if (false === $page) {
		// header(' ', true, 404);
		header('HTTP/1.0 404 Not Found'); // Set header to 404
		$page = $pages->read($app['config']->get('errorPage'));
		$plugins->trigger('404', [&$page]);

		if(false === $page) {
			// Juste print simple 404 if there is no 404 page
			die('404 - Page ' . htmlspecialchars($page) . ' not found');
		}
	}

	try {
		// Page layout
		if (isset($page->layout) && $theme->hasLayout($page->layout)) {
			$layout = $theme->getLayout($page->layout);
		} else {
			$layout = $theme->getLayout(); // Default layout
		}

		// Assign some useful variables
		$view->addData([
			'baseUrl'  => Parvula::getRelativeURIToRoot(),
			'themeUrl' => Parvula::getRelativeURIToRoot() . $theme->getPath() . '/',
			'pages'    =>
				function($listHidden = false, $pagesPath = null) use ($pages) {
					return $pages->all($pagesPath)->visible()->order(SORT_ASC)->toArray();
				},
			'plugin'   =>
				function($name) use ($plugins) {
					return $plugins->getPlugin($name);
				},
			'site'     => $app['config']->toObject(),
			'page'     => $page,
			'__time__' => function () use ($app) {
				// useful to benchmark
				return sprintf('%.4f', $app['config']->get('__time__') + microtime(true));
			},
			'content'  => $page->content
		]);

		$plugins->trigger('preRender', [&$layout]);
		$out = $view->render($layout);
		$plugins->trigger('postRender', [&$out]);
		return $out;

	} catch (Exception $e) {
		exceptionHandler($e); // TODO
	}
});

// Files handler (media or uploads) (must have an extension)
$router->get('/{file:.+\.[^.]{2,8}}', function ($req) use ($app) {

	$filePath = str_replace('..', '', $req->params->file);
	$ext  = pathinfo($filePath, PATHINFO_EXTENSION);

	if (in_array($ext, $app['config']->get('mediaExtensions'))) {
		$filePath = IMAGES . $filePath;
	} else {
		$filePath = UPLOADS . $filePath;
	}

	if (!is_file($filePath)) {
		header('HTTP/1.0 404 Not Found');
		die('404');
	}

	$info = new finfo(FILEINFO_MIME_TYPE);
	$contentType = $info->file($filePath);

	header('Content-Type: ' . $contentType);
	readfile($filePath);
});
