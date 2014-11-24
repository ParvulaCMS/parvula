<?php
chdir('./../.');
require './index.php';
ob_clean();

$config = require APP . 'config.php';

$adminURL = trim($config['adminURL'], '/');

if($adminURL . '/' === ADMIN) {
	// Avoid redirection loop
	$adminURL .= '_';
	$config['adminAliasFolder'] = true;
}

if($config['adminAliasFolder']) {
	header("location: ./../" . $adminURL, true, 303);
} else {
	header(" ", true, 404);
	exit;
}
