<?php

use Parvula\Core\Config;
use Parvula\Core\Parvula;
use Parvula\Core\Model\Pages;

if($configAdmin->get('password') === "_Your_Password_") {
	die('You MUST change the default password in `' . __DIR__ . '/conf.php`.');
}

$templates = new League\Plates\Engine(__DIR__ . '/view', 'html');

$templates->addData([
	'baseUrl' => Parvula::getRelativeURIToRoot(),
	'pluginUrl' => Parvula::getRelativeURIToRoot() . $this->getPluginPath(),
	'templateUrl' => Parvula::getRelativeURIToRoot() . _THEMES_ . $this->app['config']->get('theme')
]);

// Check password
if (isset($_POST, $_POST['username'], $_POST['password'])) {

	if (!($user = $this->app['users']->read($_POST['username'])) || !$user->login($_POST['password'])) {
		$templates->addData(['notice', true]);
	}
	else {
		// Create a session
		$this->app['auth']->log($user->username);

		// Post/Redirect/Get pattern
		header(
			'Location: ./' . Parvula::getRelativeURIToRoot() . trim($configAdmin->get('adminRoute'), '/'),
			true, 303);
	}
}

if ($this->app['usersession'] && $this->app['usersession']->hasRole('admin')) {
	$pages = $this->app['pages'];
	$pagesList = $pages->index(true);
	$templates->addData([
		'pagesList' => $pagesList,
		'_page' => 'admin'
	]);
} else {
	$templates->addData(['_page' => 'login']);
}

echo $templates->render('base');
