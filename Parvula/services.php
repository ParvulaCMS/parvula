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
	$fp = $this['fileParser'];

	// Populate Config wrapper
	$config = new Parvula\Core\Config($fp->read(_CONFIG_ . 'system.yaml'));

	// Load user config
	// Append user config to Config wrapper (override if exists)
	$userConfig = $fp->read(_CONFIG_ . $config->get('userConfig'));
	$config->append((array) $userConfig);

	return $config;
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

$app->share('session', function ($this) {
	$session = new Parvula\Core\Session($this['config']->get('sessionName'));
	$session->start();
	return $session;
});

$app->share('auth', function (Container $this) {
	return new Parvula\Core\Authentication($this['session'], hash('sha1', $this['request']->ip . $this['request']->userAgent));
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
	return new Parvula\Core\Model\Mapper\Users($this['fileParser'], _USERS_ . '/users.php');
});

$app->share('pageRenderer', function (Container $this) {
	$headParser = $this['config']->get('headParser');
	$contentParser = $this['config']->get('contentParser');
	$pageRenderer = $this['config']->get('pageRenderer');
	$options = [
		'delimiterMatcher' => '/\s[-=]{3,}\s+/',
		'sectionMatcher' => '/-{3}\s+(\w[\w- ]*?)\s+-{3}/',
		'delimiterRender' => '---'
	];
	return new $pageRenderer(new $headParser, new $contentParser, $options);
});

$app->share('pageRendererRAW', function (Container $this) {
	$headParser = $this['config']->get('headParser');
	$pageRenderer = $this['config']->get('pageRenderer');
	return new $pageRenderer(new $headParser, new Parvula\Core\ContentParser\None);
});

$app->add('pages', function (Container $this) {
	$fileExtension =  '.' . $this['config']->get('fileExtension');

	return new Parvula\Core\Model\Mapper\PagesFlatFiles($this['pageRenderer'], _PAGES_, $fileExtension);
});

$app->add('themes', function (Container $this) {
	return new Parvula\Core\Model\Mapper\Themes(_THEMES_, $this['fileParser']);
});

$app->share('theme', function (Container $this) {
	if ($this['themes']->has($themeName = $this['config']->get('theme'))) {
		return $this['themes']->read($themeName);
	} else {
		throw new Exception('Theme `' . $themeName . '` does not exists');
	}
});

$app->add('view', function (Container $this) {
	$theme = $this['theme'];

	// Create new Plates instance to render theme files
	$path = $theme->getPath();
	$view = new League\Plates\Engine($path, $theme->getExtension());

	// Register folder begining with a '_' as Plates folder
	// (Plates will resolve `this->fetch('myFolder::file')` as `_myFolder/file.html`)
	$filter = function ($current) {
		// Must be a dir begining with _
		return $current->isDir() && $current->getFilename()[0] === '_';
	};

	$fs = new Files($path);
	$flattened = $fs->index('', function ($file, $dir) use ($path, $view) {
		$view->addFolder(substr($file, 1), $path . $dir . '/' . $file);
	}, $filter);

	return $view;
});
