<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function test($str, $fn) {
	println($str, 1);
	if (0 === ($res = $fn(0))) {
		echo '> OK' . PHP_EOL;
	}
	echo PHP_EOL;
	return $res;
}

function println($str, $level = 0) {
	if ($level) {
		echo str_repeat('#', $level) . ' ';
	}
	echo $str . PHP_EOL;
}

$errors = 0;
$checkAPI = true;
if (isset($argv[1])) {
	$checkAPI = (bool) $argv[1];
}

$mustExists = [
	_CONFIG_ . 'site.yml',
	_CONFIG_ . 'system.yml',
	_CONFIG_ . 'mappers.yml',
	_ROOT_ . 'composer.json',
	_ROOT_ . 'index.php'
];

$shouldBeWritable = [
	_DATA_ . 'config',
	_DATA_ . 'pages',
	_DATA_ . 'users'
];

$modulesNeeded = [
	'Core',
	'SPL',
	'json',
	'mbstring'
];

println('PHP DOCTOR', 1);
println('');
println('PHP Version: ' . phpversion());
println('Zend Version: ' . zend_version());
println('Parvula Version: ' . _VERSION_);
println('System Info: ' . php_uname());
println('DIRECTORY_SEPARATOR: ' . DIRECTORY_SEPARATOR);
println('PHP_SHLIB_SUFFIX: ' . PHP_SHLIB_SUFFIX);
println('PATH_SEPARATOR: ' . PATH_SEPARATOR);
println('');

$errors += test('Check PHP version', function ($errors) {
	if (!version_compare(phpversion(), '5.5.9', '>=')) {
		println('You PHP version is not compatible with Parvula');
		return 1;
	}
	return 0;
});

$errors += test('Check Composer configuration', function ($errors) {
	if (!is_dir(_VENDOR_)) {
		++$errors;
		println('You must install the dependencies with `composer install` to run Parvula');
	}
	return $errors;
});

$errors += test('Check if files exist', function ($errors) use ($mustExists) {
	return array_reduce($mustExists, function($errors, $path) {
		if (!is_readable(_ROOT_ . $path)) {
			++$errors;
			println(_ROOT_ . $path . ' must exists');
		}
		return $errors;
	}, 0);
});

$errors += test('Check if needed modules are loaded', function () use ($modulesNeeded) {
	$modules = array_flip(get_loaded_extensions());
	return array_reduce($modulesNeeded, function ($errors, $module) use ($modules) {
		if (!isset($modules[$module])) {
			++$errors;
			println('Module ' . $module . ' not found !');
		}
		return $errors;
	}, 0);
});

if ($checkAPI) {

	// Flatfiles
	$errors += test('Check if folders are writable [API]', function() use ($shouldBeWritable) {
		return array_reduce($shouldBeWritable, function ($errors, $path) {
			if (!is_writable(_ROOT_ . $path)) {
				++$errors;
				println(_ROOT_ . $path . ' should be writable');
			}
			return $errors;
		}, 0);
	});
}

test('Check Parvula stability', function () {
	if (preg_match('/dev|alpha|beta|RC/', _VERSION_)) {
		println('> You use a non stable version of Parvula');
		return 1;
	}
	return 0;
});

test('Check Parvula production mode', function () use ($config) {
	if ((bool) $config->get('debug', false)) {
		println('> Parvula is in debug mode');
		return 1;
	}
	return 0;
});


if (!$errors) {
	println('>> Everything is OK ! <<');
}

return false;
