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

	return new Parvula\Core\FileParser($parsers);
});

$app->share('config', function (Container $this) {
	// Populate Config wrapper
	return new Parvula\Core\Config($this['fileParser']->read(APP . 'config.yaml'));
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
	$session->start(true);
	return $session;
});

$app->share('auth', function (Container $this) {
	return new Parvula\Core\Authentication('parvula.', hash('sha1', $this['request']->ip . $this['request']->userAgent));
});

// Get current logged User if available
$app->share('usersession', function (Container $this) {
	$sess = $this['session'];
	if ($username = $sess->get('username')) {
		if ($this['auth']->isLogged($username)) {
			return $this['users']->read($username);
		}
	}

	return false;
});

//-- ModelMapper --

$app->add('users', function (Container $this) {
	return new Parvula\Core\Model\Mapper\Users($this['fileParser'], DATA . 'users/users.php');
});

$app->share('pageRenderer', function (Container $this) {
	$headParser = $this['config']->get('headParser');
	$contentParser = $this['config']->get('contentParser');
	$pageRenderer = $this['config']->get('pageRenderer');
	return new $pageRenderer(new $headParser, new $contentParser);
});

$app->share('pageRendererRAW', function (Container $this) {
	$headParser = $this['config']->get('headParser');
	$pageRenderer = $this['config']->get('pageRenderer');
	return new $pageRenderer(new $headParser, new Parvula\Core\ContentParser\Null);
});

$app->add('pages', function (Container $this) {
	$fileExtension =  '.' . $this['config']->get('fileExtension');

	return new Parvula\Core\Model\Mapper\PagesFlatFiles($this['pageRenderer'], PAGES, $fileExtension);
});

$app->add('themes', function (Container $this) {
	return new Parvula\Core\Model\Mapper\Themes(THEMES, $this['fileParser']);
});

$app->add('theme', function (Container $this) {
	if ($this['themes']->has($themeName = $this['config']->get('theme'))) {
		return $this['themes']->read($themeName);
	} else {
		throw new Exception('Theme `' . $themeName . '` does not exists');
	}
});

$app->add('view', function (Container $this) {
	$theme = $this['theme'];

	// Create new Plates instance to render theme files
	$view = new League\Plates\Engine($theme->getPath(), $theme->getExtension());

	// Register theme folders
	$iter = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($theme->getPath(), RecursiveDirectoryIterator::SKIP_DOTS),
			RecursiveIteratorIterator::SELF_FIRST, RecursiveIteratorIterator::CATCH_GET_CHILD
	);
	$baseLen = strlen($theme->getPath());
	foreach ($iter as $path => $dir) {
		if ($dir->isDir()){
			$name = str_replace([DIRECTORY_SEPARATOR, '/', '\\'], '|', substr($path, $baseLen));
			// Register '_*' folders exept '_layouts' (registered the 'layout' theme config)
			if ($name[0] === '_' && $name !== '_layouts') {
				$view->addFolder(substr($name, 1), $path);
			}
		}
	}

	// Register 'layouts' with the layouts folder (in the theme config)
	$view->addFolder('layouts', $theme->getPath() . $theme->getLayoutFolder());

	return $view;
});
