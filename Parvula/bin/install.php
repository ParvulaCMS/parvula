<?php
/**
 * Install remote or local plugins and themes
 * Usage: `install <plugin or theme> <URL or Path (*.zip)>`
 * @author Fabien Sa
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (count($argv) < 3) {
	echo 'Usage: install <plugin or theme> <URL or Path (*.zip)>' . PHP_EOL;
	return false;
}

$type = strtolower(trim($argv[1]));
$path = trim($argv[2], " \n\r\0<>'");

if (!in_array($type, ['plugin', 'theme'])) {
	echo 'Type invalid. You must choose between `plugin` or `theme`' . PHP_EOL;
	return false;
}

if ($type === 'plugin') {
	$basePath = _PLUGINS_;
} else {
	$basePath = _THEMES_;
}

if (!preg_match('/.zip$/', $path)) {
	echo 'Invalid URL or path. It must be a `*.zip`' . PHP_EOL;
	return false;
}

if (!is_writable($basePath)) {
	echo 'Error, ' . $basePath . ' is not writable' . PHP_EOL;
}

echo 'Download...';
$zipData = file_get_contents($path);
echo ' OK' . PHP_EOL;

$tmpFile = $basePath . '/tmpAsset.zip';
file_put_contents($tmpFile, $zipData);

$zip = new \ZipArchive;
if ($zip->open($tmpFile) === true) {
	// $numFiles =
	$numRootFiles = 0;
	echo 'Extracting (' . $zip->numFiles . ' files)...';

	// Check if files are in a folder or not
	for($i = 0; $i < $zip->numFiles; ++$i) {
		$filename = ltrim(($zip->getNameIndex($i)), '/\\');
		if (!preg_match('/[\\/\\\]/', $filename)) {
			++$numRootFiles;
		}
	}

	// If no root files, we create a folder
	if ($numRootFiles !== 0) {
		$basePath .= basename($path, '.zip') . '/';
	}

	$zip->extractTo($basePath);
	$zip->close();
	echo ' OK' . PHP_EOL;

	echo 'Extracted correctly to ' . $basePath . PHP_EOL;
} else {
	echo 'Error. Could not extract the file' . PHP_EOL;
}

echo 'Remove residual file...';
unlink($tmpFile);
echo ' OK' . PHP_EOL;

return false;
