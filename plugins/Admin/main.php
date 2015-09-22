<?php

use Parvula\Core\Config;
use Parvula\Core\Parvula;
use Parvula\Core\PageManager;

$adminConf = require __DIR__ . '/conf.php';

if($adminConf['password'] === "_Your_Password_") {
	die('You MUST change the default password in `' . __DIR__ . '/conf.php`.');
}

$templates = new League\Plates\Engine(__DIR__ . '/view', 'html');

$templates->addData([
	'baseUrl' => Parvula::getRelativeURIToRoot(),
	'pluginUrl' => Parvula::getRelativeURIToRoot() . $pluginPath,
	'templateUrl' => Parvula::getRelativeURIToRoot() . TMPL . Config::get('template')
]);

// Check password
if(isset($_POST, $_POST['password'])) {

	if($_POST['password'] === $adminConf['password']) {
		if(session_id() === '') {
			session_id(uniqid());
			session_start();
		}
		$_SESSION['id'] = uidSession();
		$_SESSION['login'] = true;

		// Post/Redirect/Get pattern
		header(
			'Location: ./' . Parvula::getRelativeURIToRoot() . trim($adminConf['adminRoute'], '/'),
			true, 303);

	} else {
		$templates->addData('notice', true);
	}
}

if(true === isParvulaAdmin()) {
	$pages = new PageManager;
	$pagesList = $pages->index(true);
	$templates->addData([
		'pagesList' => $pagesList,
		'_page' => 'admin'
	]);
} else {
	$templates->addData(['_page' => 'login']);
}

echo $templates->render('base');
