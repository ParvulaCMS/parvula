<?php

use Parvula\Parvula;
use Parvula\Parvula\Core\Config;
use Parvula\Parvula\Core\Models\Pages;

$templates = new League\Plates\Engine(__DIR__ . '/view', 'html');

$templates->addData([
	'baseUrl' => url(),
	'pluginUrl' => url($that->getPluginPath()),
	'templateUrl' => url(themesPath(app('config')->get('theme')))
]);

$pages = app('pages');
$pagesList = $pages->index(true);
$templates->addData([
	'pagesList' => $pagesList,
	'_page' => 'admin'
]);

return $templates->render('base');
