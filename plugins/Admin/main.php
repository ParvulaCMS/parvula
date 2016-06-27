<?php

use Parvula\Parvula;
use Parvula\Parvula\Core\Config;
use Parvula\Parvula\Core\Models\Pages;

$templates = new League\Plates\Engine(__DIR__ . '/view', 'html');

$templates->addData([
	'baseUrl' => Parvula::getRelativeURIToRoot(),
	'pluginUrl' => Parvula::getRelativeURIToRoot($that->getPluginPath()),
	'templateUrl' => Parvula::getRelativeURIToRoot(_THEMES_ . $that->app['config']->get('theme'))
]);

$pages = $that->app['pages'];
$pagesList = $pages->index(true);
$templates->addData([
	'pagesList' => $pagesList,
	'_page' => 'admin'
]);

return $templates->render('base');
