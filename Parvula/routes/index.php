<?php

use Parvula\Core\Parvula;
use Parvula\Core\Model\PagesFlatFiles;

// Pages handler (slug must be `a-z0-9-_+/`)
$router->map('GET|POST', '/{slug:[a-z0-9\-_\+\/]*}', function ($req) use ($app) {
	$view = $app['view'];
	$pages = $app['pages'];
	$theme = $app['theme'];
	$config = $app['config'];
	$plugins = $app['plugins'];

	$slug = rtrim($req->params->slug, '/');
	$slug = urldecode($slug);

	$plugins->trigger('uri', [$req->params->slug]);
	$plugins->trigger('slug', [$slug]);

	if (empty($slug)) {
		// Default page
		$slug = $config->get('homePage');
	}

	$page = $pages->read($slug, true);

	// 404
	if (false === $page) {
		// header(' ', true, 404);
		header('HTTP/1.0 404 Not Found'); // Set header to 404
		$page = $pages->read($config->get('errorPage'));
		$plugins->trigger('404', [&$page]);

		if (false === $page) {
			// Juste print simple 404 if there is no 404 page
			die('404 - Page ' . htmlspecialchars($page) . ' not found');
		}
	}

	// Set the right timezone
	$page->date->setTimeZone(new DateTimeZone($config->get('timezone', 'UTC')));
	$plugins->trigger('page', [&$page]);

	try {
		// Page layout
		if ($theme->hasLayout($page->get('layout'))) {
			$layout = $theme->getLayout($page->layout);
		} else {
			$layout = $theme->getLayout(); // Default layout
		}

		// Assign some useful variables
		$view->addData([
			'baseUrl'  => Parvula::getRelativeURIToRoot(),
			'themeUrl' => Parvula::getRelativeURIToRoot() . $theme->getPath() . '/',
			'pages'    =>
				function ($listHidden = false, $pagesPath = null) use ($pages, $config) {
					return $pages->all($pagesPath)->visibility(!$listHidden)->
						order($config->get('typeOfSort'), $config->get('sortField'))->toArray();
				},
			'plugin'   =>
				function ($name) use ($plugins) {
					return $plugins->getPlugin($name);
				},
			'site'     => $config->toObject(),
			'page'     => $page,
			'config'   => $app['fileParser']->read(_CONFIG_ . 'user.yaml'), //TODO tests
			'__time__' => function () use ($config) {
				// useful to benchmark
				return sprintf('%.4f', $config->get('__time__') + microtime(true));
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
$router->get('/{file:.+\.[^.]{2,10}}', function ($req, $res) use ($app) {

	$filePath = str_replace(['..', "\0"], '', $req->params->file);
	$ext = pathinfo($filePath, PATHINFO_EXTENSION);

	// if (in_array($ext, $app['config']->get('mediaExtensions'))) {
	$filePath = _UPLOADS_ . $filePath;
	// }

	if (false === $res->sendFile($filePath)) {
		$res->sendStatus(404);
	}
});
