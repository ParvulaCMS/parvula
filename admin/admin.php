<?php

namespace Admin;

use Parvula\Core\View;
use Parvula\Core\Config;
use Parvula\Core\Parvula;

if(!defined('ROOT')) exit;
$adminConf = require DATA . 'admin.conf.php';

if($adminConf['password'] === "_Your_Password_") {
	die('You MUST change the default password in `' . DATA . 'admin.conf.php`.');
}

$view = new View(ADMIN . 'view');

$view->assign('baseUrl', Parvula::getRelativeURIToRoot());
$view->assign('templateUrl', Parvula::getRelativeURIToRoot() . TMPL . Config::get('template'));


// Check password
if(isset($_POST, $_POST['password'])) {

	if($_POST['password'] === $adminConf['password']) {
		if(session_id() === '') {
			session_start();
		}
		$_SESSION['id'] = uidSession();
		$_SESSION['login'] = true;

		// Post/Redirect/Get pattern
		header("Location: ./", true, 303);

	} else {
		$view->assign('notice', true);
	}
}

if(true === isParvulaAdmin()) {
	$parvula = new Parvula;
	$pagesList = $parvula->listPages();
	$view->assign('pagesList', $pagesList);

	$view->assign('_page', 'admin');
} else {
	$view->assign('_page', 'login');
}

echo $view('base');
