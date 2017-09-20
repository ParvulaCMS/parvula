<?php
// ----------------------------- //
// Register services
// ----------------------------- //

namespace Parvula;

use DateTime;
use Exception;
use RuntimeException;
use Pimple\Container;
use Parvula\Models\Config;
use Parvula\FilesSystem as Files;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$app['config'] = function (Container $c) {
	// Populate Config wrapper
	if (!is_file(_CONFIG_ . 'system.yml')) {
		throw new Exception('Configuration `system.yml` does not exists in `' . _CONFIG_ . '`');
	}

	// Core configuration
	$confArr = $c['fileParser']->read(_CONFIG_ . 'system.yml');
	if (is_file(_CUSTOM_CONFIG_ . 'system.yml')) {
		// Custom configuration to extends core
		$custom = $c['fileParser']->read(_CUSTOM_CONFIG_ . 'system.yml');
		$confArr = $custom + $confArr;
	}

	return new Config($confArr);
};

$app['config:site'] = function (Container $c) {
	$name = $c['config']->get('siteConfig');
	$conf = $c['configs']->find($name);

	if (!$conf) {
		throw new Exception('Configuration `' . $name . '` does not exists');
	}

	return $conf;
};

$app['config:globals:user'] = function (Container $c) {
	$name = $c['config']->get('globalUserConfig');

	// Global user config is optional
	if ($name === '' || $name === null) {
		return;
	}

	$conf = $c['configs']->find($name);

	// Configuration cannot be find
	if (!$conf) {
		return;
	}

	return $conf;
};

$app['router'] = function (Container $c) {
	$cacheFile = $c['config']->get('routerCacheFile', false);
	if ($cacheFile) {
		$cacheFile = _CACHE_ . 'routes.php';
	}

	$slimConf = [
		'settings' => [
			'routerCacheFile' => $cacheFile,
			'displayErrorDetails' => $c['config']->get('debug', false)
		],
		'api' => new Http\APIResponse(),
		'logger' => $c['loggerHandler']
	];

	$router = new \Slim\App($slimConf);

	$container = $router->getContainer();
	$router->add(new \Slim\Middleware\JwtAuthentication([
		'path' => '/api',
		// true: If the middleware detects insecure usage over HTTP it will throw a RuntimeException
		'secure' => !$c['config']->get('debug', false),
		// 'cookie' => 'parvula_token',
		'passthrough' => [
			'/api/0/auth',
			'/api/0/public'
		],
		'rules' => [
			// GET /api/0/pages
			function ($arr) {
				$path = trim($arr->getUri()->getPath(), '/');
				if ($arr->isGet() && preg_match('~^api/0/pages~', $path)) {
					return false;
				}
			}
		],
		'secret' => $c['config']->get('secretToken'),
		'callback' => function ($request, $response, $arguments) use ($container) {
			$container['token'] = $arguments['decoded'];
		}
	]));

	// Remove Slim handler, we want to use our own
	unset($router->getContainer()['errorHandler']);

	return $router;
};

// Log errors in _LOG_ folder
$app['loggerHandler'] = function ($c) {
	if (class_exists('\\Monolog\\Logger')) {
		$logger = new Logger('parvula');
		$file = (new DateTime('now'))->format('Y-m-d') . '.log';

		$logger->pushProcessor(new \Monolog\Processor\UidProcessor());
		$logger->pushHandler(new StreamHandler(_LOGS_ . $file, Logger::WARNING));

		\Monolog\ErrorHandler::register($logger);

		return $logger;
	}

	return false;
};

$app['errorHandler'] = function (Container $c) {
	if (version_compare(phpversion(), '7.0.0', '<') && class_exists('League\\BooBoo\\Runner')) {
		$runner = new \League\BooBoo\Runner();

		$accept = $c['router']->getContainer()['request']->getHeader('Accept');
		$accept = join(' ', $accept);

		// If we accept html, show html, else show json
		if (strpos($accept, 'html') !== false) {
			$runner->pushFormatter(new \League\BooBoo\Formatter\HtmlTableFormatter);
		} else {
			$runner->pushFormatter(new \League\BooBoo\Formatter\JsonFormatter);
		}

		$runner->register();
	} elseif (class_exists('Whoops\\Run')) {
		$run = new \Whoops\Run;
		$handler = new \Whoops\Handler\PrettyPageHandler;

		$handler->setPageTitle('Parvula Error');
		$handler->addDataTable('Parvula', [
			'Version'=> _VERSION_
		]);

		$run->pushHandler($handler);

		if (\Whoops\Util\Misc::isAjaxRequest()) {
			$run->pushHandler(new \Whoops\Handler\JsonResponseHandler);
		}

		$run->register();

		if (class_exists('Monolog\\Logger')) {
			// Be sure that Monolog is still register
			\Monolog\ErrorHandler::register($c['loggerHandler']);
		}

		return $run;
	}
};

// To parse serialized files in multiple formats
$app['fileParser'] = function () {
	$parsers = [
		'json' => new Parsers\Json,
		'yaml' => new Parsers\Yaml,
		'yml' => new Parsers\Yaml,
		'php' => new Parsers\Php
	];

	return new FileParser($parsers);
};

$app['plugins'] = function (Container $c) {
	return (new PluginMediator)
		->attach(getPluginList($c['config']->get('disabledPlugins')));
};

$app['session'] = function (Container $c) {
	$session = new Session($c['config']->get('sessionName'));
	$session->start();
	return $session;
};

$app['auth'] = function (Container $c) {
	return new Service\AuthenticationService($c['session'], hash('sha1', '@TODO'));
	// Service\AuthenticationService($c['session'], hash('sha1', $c['request']->ip . $c['request']->userAgent));
};

// Get current logged User if available
$app['usersession'] = function (Container $c) {
	$sess = $c['session'];
	if ($username = $sess->get('username')) {
		if ($c['auth']->isLogged($username)) {
			return $c['users']->find($username);
		}
	}

	return false;
};

$app['pageRenderer'] = function (Container $c) {
	$contentParser = $c['config']->get('contentParser');
	$pageRenderer = $c['config:database']->get('pageRenderer');

	$headParser = $c['config:database']->get('headParser');
	$options = $c['config:database']->get('pageRendererOptions');

	if ($headParser === null) {
		return new $pageRenderer(new $contentParser, $options);
	}

	return new $pageRenderer(new $headParser, new $contentParser, $options);
};

$app['pageRendererRAW'] = function (Container $c) {
	$headParser = $c['config:database']->get('headParser');
	$pageRenderer = $c['config:database']->get('pageRenderer');

	if ($headParser === null) {
		return new $pageRenderer(new ContentParser\None);
	}

	return new $pageRenderer(new $headParser, new ContentParser\None);
};

//-- Databases --

$app['mongodb'] = function (Container $c) {
	if (!class_exists('MongoDB\\Client')) {
		throw new RuntimeException('MongoDB client not found, please install the package `mongodb/mongodb`');
	}

	$fp = $c['fileParser'];
	$options = (new Config($fp->read(_CONFIG_ . 'database.yml')))->get('mongodb');
	$uri = 'mongodb://';

	if (isset($options['username'], $options['password'])) {
		$uri .= $options['username'] . ':' . $options['password'] . '@';
	}

	if (isset($options['address'])) {
		$uri .= $options['address'];
	} else {
		$uri .= 'localhost';
	}

	if (isset($options['port'])) {
		$uri .= ':' . $options['port'];
	}

	return (new \MongoDB\Client($uri))->{$options['name']};
};

$app['repositories'] = function (Container $c) {
	$dbType = $c['config']->get('database');

	$databases = [
		'mongodb' => [
			'pages' => function () use ($c) {
				return new Repositories\Mongo\PageRepositoryMongo($c['pageRenderer'], $c['mongodb']->pages);
			},
			'users' => function () use ($c) {
				return new Repositories\Mongo\UserRepositoryMongo($c['mongodb']->users);
			},
			'configs' => function () use ($c) {
				return new Repositories\Mongo\ConfigRepositoryMongo($c['fileParser'], $c['mongodb']->configs);
			}
		],
		'flatfiles' => [
			'pages' => function () use ($c) {
				return new Repositories\Flatfiles\PageRepositoryFlatfiles(
					$c['pageRenderer'],
					_PAGES_,
					$c['config:database']->get('fileExtension')
				);
			},
			'users' => function () use ($c) {
				return new Repositories\Flatfiles\UserRepositoryFlatfiles($c['fileParser'], _USERS_ . '/users.php');
			},
			'configs' => function () use ($c) {
				return new Repositories\Flatfiles\ConfigRepositoryFlatfiles(
					$c['fileParser'],
					_CONFIG_,
					$c['config']->get('configExtension')
				);
			}
		]
	];

	if (!isset($databases[$dbType])) {
		throw new RuntimeException(
			'Repository `' . htmlspecialchars($dbType) . '` does not exists, please edit your settings.'
		);
	}

	return $databases[$dbType];
};

$app['config:database'] = function (Container $c) {
	$dbType = $c['config']->get('database');

	$fp = $c['fileParser'];
	$databaseConfig = new Config($fp->read(_CONFIG_ . 'database.yml'));

	$conf = new Config($databaseConfig->get($dbType));
	$conf->set('dbType', $dbType);

	return $conf;
};

// Aliases
$app['pages'] = $app['repositories']['pages'];

$app['users'] = $app['repositories']['users'];

$app['configs'] = $app['repositories']['configs'];

$app['themes'] = function (Container $c) {
	return new Services\ThemesService(_THEMES_, $c['fileParser']);
};

$app['theme'] = function (Container $c) {
	if ($c['themes']->has($themeName = $c['config:site']->get('theme'))) {
		return $c['themes']->get($themeName);
	} else {
		throw new Exception('Theme `' . $themeName . '` does not exists');
	}
};

$app['view'] = function (Container $c) {
	$theme = $c['theme'];
	$config = $c['config'];

	// Create new Plates instance to render theme files
	$path = $theme->getPath();
	$view = new \League\Plates\Engine($path, $theme->getExtension());

	// Helper function
	// List pages
	$view->registerFunction('listPages', function ($pages, $options) {
		return listPagesAndChildren(listPagesRoot($pages), $options);
	});

	// System date format
	$view->registerFunction('dateFormat', function (DateTime $date) use ($config) {
		return $date->format($config->get('dateFormat'));
	});

	// System date format
	$view->registerFunction('pageDateFormat', function (Models\Page $page) use ($config) {
		return $page->getDateTime()->format($config->get('dateFormat'));
	});

	// Excerpt strings
	$view->registerFunction('excerpt', function ($text, $length = 275) {
		$text = strip_tags($text);
		$excerpt = substr($text, 0, $length);
		if ($excerpt !== $text) {
			$lastDot = strrpos($excerpt, '. ');
			if ($lastDot === false) {
				$lastDot = strrpos($excerpt, ' ');
			}
			$excerpt = substr($excerpt, 0, $lastDot);
		}
		return $excerpt;
	});

	// Register folder start with a '_' as Plates folder
	// (Plates will resolve `this->fetch('myFolder::file')` as `_myFolder/file.html`)
	$filter = function ($current) {
		// Must be a dir start with _
		return $current->isDir() && $current->getFilename()[0] === '_';
	};

	(new Files($path))->index('', function (\SplFileInfo $file, $dir) use ($path, $view) {
		$view->addFolder(substr($file->getFileName(), 1), $path . $dir . '/' . $file->getFileName());
	}, $filter);

	return $view;
};
