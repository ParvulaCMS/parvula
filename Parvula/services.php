<?php
// ----------------------------- //
// Register services
// ----------------------------- //

use Parvula\Core\Container;

$app->share('errorHandler', function () {
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

$app->share('config', function () {
	// Populate Config wrapper
	return new Parvula\Core\Config(require APP . 'config.php');
});

$app->add('fileParser', function () {
	$parsers = [
		'json' => new \Parvula\Core\Parser\Json,
		'yaml' => new \Parvula\Core\Parser\Yaml,
		'php' => new \Parvula\Core\Parser\Php
	];

	return new Parvula\Core\Model\FileParser($parsers);
});

$app->share('plugins', function (Container $this) {
	$pluginMediator = new Parvula\Core\PluginMediator;
	$pluginMediator->attach(getPluginList($this['config']->get('disabledPlugins')));
	return $pluginMediator;
});

$app->share('request', function () {
	parse_str(file_get_contents("php://input"), $post_vars);

	return new Parvula\Core\Router\Request(
		$_SERVER,
		$_GET,
		$post_vars,
		$_COOKIE,
		$_FILES
	);
});

$app->share('session', function () {
	$session = new Parvula\Core\Session('parvula.');
	$session->start();
	return $session;
});

//-- ModelMapper --

$app->add('users', function (Container $this) {
	return new Parvula\Core\Model\Mapper\Users($this['fileParser'], DATA . 'users/users.php');
});

$app->add('pages', function (Container $this) {
	$fileExtension =  '.' . $this['config']->get('fileExtension');
	$pageSerializer = $this['config']->get('pageSerializer');
	$contentParser = $this['config']->get('contentParser');

	return new Parvula\Core\Model\Mapper\PagesFlatFiles(
		new $contentParser, new $pageSerializer, $fileExtension);
});

$app->add('themes', function () {
	return new Parvula\Core\Model\Mapper\Themes(THEMES);
});
