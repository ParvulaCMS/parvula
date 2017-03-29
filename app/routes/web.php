<?php

namespace Parvula;

// Pages handler (slug must be `a-z0-9-_+/` (will be rewrited to lowercase if needed))
$router->map(['GET', 'POST'], '/{slug:[a-zA-Z0-9\-_\+\/]*}', function ($req, $res, $args) use ($app) {
	$view = $app['view'];
	$pages = $app['pages'];
	$theme = $app['theme'];
	$config = $app['config'];
	$plugins = $app['plugins'];
	$body = $req->getParsedBody();

	$slug = strtolower(rtrim($args['slug'], '/'));
	$slug = urldecode($slug);

	$plugins->trigger('uri', [$args['slug']]);
	$plugins->trigger('slug', [$slug]);

	if (empty($slug)) {
		// Default page
		$slug = $config->get('homePage', 'index');
	}

	$page = $pages->find($slug, true);

	// 404
	if (!$page) {
		$res = $res->withStatus(404);
		$page = $pages->find($config->get('errorPage'));
		$plugins->trigger('404', [&$page, $body]);

		if (!$page) {
			// Default 'not found' message if 404 page does not exists
			return $res->write(
				'<h1>Page not found</h1>' .
				'Error 404, Page "' . htmlspecialchars($slug) . '" not found.'
			);
		}
	}

	$plugins->trigger('page', [&$page, $body]);

	// Page layout
	if ($theme->hasLayout($page->get('layout'))) {
		$layout = $theme->getLayout($page->layout);
	} else {
		$layout = $theme->getLayout(); // Default layout
	}

	// Assign some useful variables
	$view->addData([
		'baseUrl'  => url(),
		'themeUrl' => url($theme->getPath()),
		'pagesArr' =>
			function ($listHidden = false, $pagesPath = '') use ($pages, $config) {
				return $pages->all($pagesPath)->visibility(!$listHidden)->
					order($config->get('typeOfSort'), $config->get('sortField'))->toArray();
			},
		'pages'    => $pages->all()->sortBy($config->get('sortField'), $config->get('typeOfSort')),
		'plugin'   =>
			function ($name) use ($plugins) {
				return $plugins->getPlugin($name);
			},
		'site'     => $config->toObject(),
		'page'     => $page,
		'theme'    => $theme,
		'config'   => $app['fileParser']->read(_CONFIG_ . 'user.yml'), // TODO tests
		'__time__' => function () use ($config) {
			// useful to benchmark
			return sprintf('%.4f', $config->get('__time__') + microtime(true));
		},
		'content'  => $page->content
	]);

	$plugins->trigger('preRender', [&$layout, &$page]);
	$out = $view->render($layout);
	$plugins->trigger('postRender', [&$out, &$page]);

	return $res->write($out);
})->setName('show');

// Files handler (media or uploads) (must have an extension)
$router->get('/{file:.+\.[^.]{2,10}}', function ($req, $res, $args) use ($app) {

	$filePath = str_replace(['..', "\0"], '', $args['file']);
	$ext = pathinfo($filePath, PATHINFO_EXTENSION);

	// if (in_array($ext, $app['config']->get('mediaExtensions'))) {
	$filePath = _UPLOADS_ . $filePath;
	// }

	if (is_file($filePath)) {
		$info = new finfo(FILEINFO_MIME_TYPE);
		$contentType = $info->file($filePath);

		// ->set('Content-Type', $f->type);
		// ->set('Pragma', "public");
		// ->set('Content-disposition:', 'attachment; filename=' . $f->name);
		// ->set('Content-Transfer-Encoding', 'binary');
		// ->set('Content-Length', $f->size);
		$res = $res->withHeader('Content-type', $contentType);

		// stream_get_contents
		return $res->write(file_get_contents($filePath));
	}

	return $res->withStatus(404);
})->setName('file-handler');
