<?php

use Parvula\FilesSystem;

$path = _PLUGINS_ . '/_Components';

// list components
$this->get('', function ($req, $res) use ($path) {
	$acc = [];
	foreach(glob($path . '/*.php', GLOB_NOSORT) as $file) {
		$file = basename($file);
		if ($file[0] !== '_') {
			$acc[] = $file;
		}
	}

	return $this->api->json($res, $acc);
});

// Get components infos
$this->get('/{name}', function ($req, $res, $args) use ($path, $modules) {
	$filepath = $path . '/' . basename($args['name']) . '.php';

	if (!is_readable($filepath)) {
		return $this->api->json($res, [
			'error' => 'Component does not exists.'
		]);
	}

	ob_start();
	$infos = require $filepath;
	ob_clean();

	$data = [
		'name' => basename($args['name']),
		'props' => null,
	];

	if (isset($infos['name'])) {
		$data['name'] = $infos['name'];
	}

	if (isset($infos['props'])) {
		$data['props'] = $infos['props'];
	}

	return $this->api->json($res, $infos);
});
