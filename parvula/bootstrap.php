<?php
// ----------------------------- //
// Starting point for the magic
// ----------------------------- //

use Parvula\Core\Parvula;
use Parvula\Core\Config;
use Parvula\Core\View;

// Try to load composer autoloader
if(is_readable($autoload = ROOT . 'vendor/autoload.php')) {
	require $autoload;
} else {
	die('We can\'t find composer autoloader. Run <code>composer install</code>
		or read the manual.');
}

require APP . 'helpers.php';

// Populate Config wrapper
Config::populate(require APP . 'config.php');

// Display or not errors
ini_set('display_errors', (bool) Config::get('debug'));

// Load class aliases
loadAliases(Config::get('aliases'));

// Load user config
$config = Parvula::getUserConfig();

// Append user config to Config wrapper (override if exists)
Config::append((array) $config);

// Check if template exists (must have index.html)
$baseTemplate = TMPL . Config::get('template');
if(!is_readable($baseTemplate . '/index.html')) {
	die("Error - Template is not readable");
}

Asset::setBasePath(Parvula::getRelativeURIToRoot() . $baseTemplate);

$parvula = new Parvula();
$page = $parvula();

if(false === $page) {
	// Juste print simple 404 if there is no 404 page
	die('404 - Page ' . htmlspecialchars($page) . ' not found');
}

$pages = $parvula->getPages();


try {
	$view = new View(TMPL . Config::get('template'));

	// Assign some variables
	$view->assign('baseUrl', Parvula::getRelativeURIToRoot());
	$view->assign('templateUrl', Asset::getBasePath());

	// Register alias for secure echo
	$view->assign(array(
		'_e' => function(&$str) {
			return HTML::sEcho($str);
		},
		'_et' => function(&$str, $str2) {
			return HTML::sEchoThen($str, $str2);
		}
	));

	$view->assign(array(
		'site' => $config,
		'pages' => $pages,
		'meta' => $page,
		'content' => $page->content
	));

	// Show index template
	echo $view('index');

} catch(Exception $e) {
	exceptionHandler($e);
}
