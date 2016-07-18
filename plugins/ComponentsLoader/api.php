<?php

use Parvula\FilesSystem;
use Parvula\Models\Section;

$componentsDir = '/_components';

/**
 * List components from the given folder
 * @param string $path
 * @return array Components files
 */
function listComponents($path) {
	$acc = [];
	foreach (glob($path . '/*.php', GLOB_NOSORT) as $file) {
		$file = str_replace(_PLUGINS_, '', dirname($path) . '/') . basename($file, '.php');
		if ($file[0] !== '_') {
			$acc[] = $file;
		}
	}

	return $acc;
}

// List components
$this->get('', function ($req, $res) use ($componentsDir) {
	$acc = [];

	foreach (glob(_PLUGINS_ . '*', GLOB_NOSORT | GLOB_ONLYDIR) as $pluginDir) {
		if (is_dir($pluginDir . $componentsDir)) {
			$acc += listComponents($pluginDir . $componentsDir);
		}
	}

	if (is_dir(_PLUGINS_ . $componentsDir)) {
		$acc = array_merge(listComponents(_PLUGINS_ . $componentsDir), $acc);
	}

	return $this->api->json($res, $acc);
});

// Get components infos (rendered with default values)
$this->post('/{name}[/{sub}]', function ($req, $res, $args) use ($render, $getComponentPath) {
	$name = $args['name'] . (isset($args['sub']) ? '/' . $args['sub'] : '');

	$filepath = $getComponentPath($name);

	if (!is_readable($filepath)) {
		return $this->api->json($res, [
			'error' => 'Component does not exists.'
		]);
	}

	$section = new Section($req->getParsedBody());

	$out = $render($name, $section);

	return $this->api->json($res, $out);
});

/**
 * Get component info (will not call the render function)
 * 
 * @param  string $name Component name
 * @param  string $filepath File path
 * @return array Components info (props and name)
 */
function componentsInfo($name, $filepath) {
	if (!is_readable($filepath)) {
		return false;
	}

	ob_start();
	$infos = require $filepath;
	ob_clean();

	$data = [
		'name' => basename($name),
		'props' => null,
	];

	if (isset($infos['name'])) {
		$data['name'] = $infos['name'];
	}

	if (isset($infos['props'])) {
		$data['props'] = $infos['props'];
	}

	return $data;
}

// Get components info (in _components/{name}.php)
$this->get('/{name}/props', function ($req, $res, $args) use ($getComponentPath) {
	$filepath = $getComponentPath($args['name']);

	if ($filepath === false) {
		return $this->api->json($res, [
			'error' => 'Component does not exists.'
		]);
	}

	return $this->api->json(
		$res,
		componentsInfo($args['name'], $filepath)
	);
});

// Get components info (in {plugin}/_components/{name}.php)
$this->get('/{plugin}/{name}/props', function ($req, $res, $args) use ($getComponentPath) {
	$name = $args['plugin'] . '/' . $args['name'];
	$filepath = $getComponentPath($name);

	if ($filepath === false) {
		return $this->api->json($res, [
			'error' => 'Component does not exists.'
		]);
	}

	return $this->api->json(
		$res,
		componentsInfo($name, $filepath)
	);
});
