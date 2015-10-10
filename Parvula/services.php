<?php

// Register services

$app->add('errorHandler', function() {
	if (class_exists('\\Whoops\\Run')) {
		$whoops = new Whoops\Run();
		$whoops->pushHandler(new Whoops\Handler\PrettyPageHandler());
		$jsonHandler = new Whoops\Handler\JsonResponseHandler();
		$jsonHandler->onlyForAjaxRequests(true);
		$whoops->pushHandler($jsonHandler);

		$whoops->register();
	} else {
		// Use custom exception handler
		set_exception_handler('exceptionHandler');
	}
});

$app->share('config', function() {
	// Populate Config wrapper
	return new Parvula\Core\Config(require APP . 'config.php');
});

$app->add('pages', function() use ($app) {
	$fileExtension =  '.' . $app['config']->get('fileExtension');
	$pageSerializer = $app['config']->get('pageSerializer');
	$contentParser = $app['config']->get('contentParser');

	return new Parvula\Core\Model\PagesFlatFiles(
		new $contentParser, new $pageSerializer, $fileExtension);
});
