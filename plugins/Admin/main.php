<?php

use Parvula\Core\View;
use Parvula\Core\Config;
use Parvula\Core\Parvula;

$adminConf = require __DIR__ . '/conf.php';

if($adminConf['password'] === "_Your_Password_") {
	die('You MUST change the default password in `' . __DIR__ . '/conf.php`.');
}

$view = new View(__DIR__ . '/view');

$view->assign('baseUrl', Parvula::getRelativeURIToRoot());
$view->assign('pluginUrl', Parvula::getRelativeURIToRoot() . $pluginPath);
$view->assign('templateUrl', Parvula::getRelativeURIToRoot() . TMPL . Config::get('template'));

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
		$view->assign('notice', true);
	}
}

if(true === isParvulaAdmin()) {
	$parvula = new Parvula;
	$pagesList = $parvula->listPages(true);
	$view->assign('pagesList', $pagesList);

	$view->assign('_page', 'admin');
} else {
	$view->assign('_page', 'login');
}

echo $view('base');
