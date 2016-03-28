<?php
// Basic command line queries

if (count($argv) === 2) {
	$method = 'GET';
	$uri = $argv[1];
} else {
	$method = trim(strtoupper($argv[1]));
	$uri = trim($argv[2]);

	if (count($argv) > 3) {
		$_POST = json_decode($argv[3], true);
	}
}

// Override global $_SERVER
$_SERVER['DOCUMENT_ROOT'] = '/Users/fabiens/Sites/parvula';
$_SERVER['REQUEST_METHOD'] = $method;
$_SERVER['REQUEST_URI'] = $uri;
$_SERVER['PATH_INFO'] = $uri;
$_SERVER['PHP_SELF'] = '/index.php' . $uri;
$_SERVER['SCRIPT_NAME'] = '/index.php';
