<?php

use Parvula\Core\Config;
use Parvula\Core\Parvula;
use Parvula\Core\Models\Pages;

if ($configAdmin->get('password') === 'yourPassword') {
	die('You MUST change the default password in `' . __DIR__ . '/conf.php`.');
}

$templates = new League\Plates\Engine(__DIR__ . '/view', 'html');

$templates->addData([
	'baseUrl' => Parvula::getRelativeURIToRoot(),
	'pluginUrl' => Parvula::getRelativeURIToRoot() . $that->getPluginPath(),
	'templateUrl' => Parvula::getRelativeURIToRoot() . _THEMES_ . $that->app['config']->get('theme')
]);

// Check password
if (isset($_POST, $_POST['password'])) {
	if ($_POST['password'] !== $configAdmin->get('password')) {
		$templates->addData(['notice', true]);
	}
	else {
		// Create a session
		$that->app['auth']->log('admin');

		// Post/Redirect/Get pattern
		header(
			'Location: ./' . Parvula::getRelativeURIToRoot() . trim($configAdmin->get('adminRoute'), '/'),
			true, 303);
	}
}

if ($that->app['usersession'] && $that->app['usersession']->hasRole('admin')) {
	$pages = $that->app['pages'];
	$pagesList = $pages->index(true);
	$templates->addData([
		'pagesList' => $pagesList,
		'_page' => 'admin'
	]);
} else {
	$templates->addData(['_page' => 'login']);
}

return $templates->render('base');
