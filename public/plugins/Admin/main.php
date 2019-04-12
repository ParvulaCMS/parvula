<?php

use function Parvula\app;
use function Parvula\url;
use function Parvula\themesPath;

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
