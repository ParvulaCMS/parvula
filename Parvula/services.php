<?php
// ----------------------------- //
// Register services
// ----------------------------- //

use Pimple\Container;
use Parvula\Core\FilesSystem as Files;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$app['router'] = function ($cont) {
	$slimConf = [
		'settings' => [
			'displayErrorDetails' => false
		],
		'api' => new Parvula\Core\Router\APIRender(),
		'logger' => $cont['loggerHandler']
	];

	$router = new Slim\App($slimConf);

	// Remove Slim handler, we want to use our own
	unset($router->getContainer()['errorHandler']);

	return $router;
};

// Log errors in _LOG_ folder
$app['loggerHandler'] = function ($c) {
	if (class_exists('\\Monolog\\Logger')) {
		$logger = new Logger('parvula');
		$file = (new DateTime('now'))->format('Y-m-d') . '.log';

		$logger->pushProcessor(new Monolog\Processor\UidProcessor());
		$logger->pushHandler(new StreamHandler(_LOGS_ . $file, Logger::WARNING));

		Monolog\ErrorHandler::register($logger);

		return $logger;
	}

	return false;
};

$app['errorHandler'] = function ($that) {
	if (class_exists('\\Whoops\\Run')) {
		$run = new Whoops\Run;
		$handler = new Whoops\Handler\PrettyPageHandler;

		$handler->setPageTitle("Parvula Error");
		$handler->addDataTable('Parvula', [
			'Version'=> _VERSION_
		]);

		$run->pushHandler($handler);

		if (Whoops\Util\Misc::isAjaxRequest()) {
			$run->pushHandler(new Whoops\Handler\JsonResponseHandler);
		}

		$run->register();

		if (class_exists('\\Monolog\\Logger')) {
			// Be sure that Monolog is still register
			Monolog\ErrorHandler::register($that['loggerHandler']);
		}

		return $run;
	}
};

$app['fileParser'] = function () {
	$parsers = [
		'json' => new \Parvula\Core\Parser\Json,
		'yaml' => new \Parvula\Core\Parser\Yaml,
		'php' => new \Parvula\Core\Parser\Php
	];

	return new Parvula\Core\FileParser($parsers);
};

$app['config'] = function (Container $this) {
	$fp = $this['fileParser'];

	// Populate Config wrapper
	$config = new Parvula\Core\Config($fp->read(_CONFIG_ . 'system.yaml'));

	// Load user config
	// Append user config to Config wrapper (override if exists)
	$userConfig = $fp->read(_CONFIG_ . $config->get('userConfig'));
	$config->append((array) $userConfig);

	return $config;
};

$app['plugins'] = function (Container $this) {
	$pluginMediator = new Parvula\Core\PluginMediator;
	$pluginMediator->attach(getPluginList($this['config']->get('disabledPlugins')));
	return $pluginMediator;
};

$app['session'] = function (Container $this) {
	$session = new Parvula\Core\Session($this['config']->get('sessionName'));
	$session->start();
	return $session;
};

$app['auth'] = function (Container $this) {
	return new Parvula\Core\Authentication($this['session'], hash('sha1', '@TODO'));
	// return new Parvula\Core\Authentication($this['session'], hash('sha1', $this['request']->ip . $this['request']->userAgent));
};

// Get current logged User if available
$app['usersession'] = function (Container $this) {
	$sess = $this['session'];
	if ($username = $sess->get('username')) {
		if ($this['auth']->isLogged($username)) {
			return $this['users']->read($username);
		}
	}

	return false;
};

//-- ModelMapper --

$app['users'] = function (Container $this) {
	return new Parvula\Core\Model\Mapper\Users($this['fileParser'], _USERS_ . '/users.php');
};

$app['pageRenderer'] = function (Container $this) {
	$headParser = $this['config']->get('headParser');
	$contentParser = $this['config']->get('contentParser');
	$pageRenderer = $this['config']->get('pageRenderer');
	$options = [
		'delimiterMatcher' => '/\s[-=]{3,}\s+/',
		'sectionMatcher' => '/-{3}\s+(\w[\w- ]*?)\s+-{3}/',
		'delimiterRender' => '---'
	];
	return new $pageRenderer(new $headParser, new $contentParser, $options);
};

$app['pageRendererRAW'] = function (Container $this) {
	$headParser = $this['config']->get('headParser');
	$pageRenderer = $this['config']->get('pageRenderer');
	return new $pageRenderer(new $headParser, new Parvula\Core\ContentParser\None);
};

$app['pages'] = function (Container $this) {
	$fileExtension =  '.' . $this['config']->get('fileExtension');

	return new Parvula\Core\Model\Mapper\PagesFlatFiles($this['pageRenderer'], _PAGES_, $fileExtension);
};

$app['themes'] = function (Container $this) {
	return new Parvula\Core\Model\Mapper\Themes(_THEMES_, $this['fileParser']);
};

$app['theme'] = function (Container $this) {
	if ($this['themes']->has($themeName = $this['config']->get('theme'))) {
		return $this['themes']->read($themeName);
	} else {
		throw new Exception('Theme `' . $themeName . '` does not exists');
	}
};

$app['view'] = function (Container $this) {
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

	(new Files($path))->index('', function (\SplFileInfo $file, $dir) use ($path, $view) {
		$view->addFolder(substr($file->getFileName(), 1), $path . $dir . '/' . $file->getFileName());
	}, $filter);

	return $view;
};
