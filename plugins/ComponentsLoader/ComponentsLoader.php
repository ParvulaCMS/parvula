<?php
namespace Plugin\ComponentsLoader;

use Parvula\Plugin;

// Dev
// TODO normalize paths
class ComponentsLoader extends Plugin {

	private $modules = [];

	const MODULE_PATH = '_Modules';

	function onPage(&$page) {
		foreach ($page->sections as $k => $section) {
			if ($section->name[0] === ':') {
				$section->module = ltrim($section->name, ':');
			}
			if (isset($section->module)) {
				$moduleName = basename($section->module);

				// Class module
				if (is_readable($filePath = $this->getPath('../' . $moduleName . '/Module.php'))) {
					$class = 'Plugin\\' . $moduleName . '\\Module';
					$obj = new $class;
					$this->modules[$section->name] = [
						'section' => $section,
						'instance' => $obj
					];
					if (method_exists($obj, 'render')) {
						$page->sections[$k]->content = $obj->render($this->getUri('../' . $moduleName . '/'), $section);
					}
				}
				// Simple file
				else if (is_readable($filePath = $this->getPath('../' . self::MODULE_PATH . '/' . $moduleName . '.php'))) {
					$arr = $this->render($filePath, $this->getUri('../' . $moduleName . '/'), $section);
					$page->sections[$k]->content = $arr['render'];
					$this->modules[$section->name] = [
						'section' => $section,
						'instance' => $arr
					];
				}
			}
		}
	}

	function onPostRender(&$out) {
		foreach ($this->modules as $module) {
			$obj = $module['instance'];
			$moduleName = $module['section']->module;

			if (method_exists($obj, 'header')) {
				$out = $this->appendToHeader($out, $obj->header($this->getUri('../' . $moduleName . '/')));
			}
			else if (isset($obj['header'])) {
				$header = $obj['header'];
				$out = $this->appendToHeader($out, $header($this->getUri('../' . $moduleName . '/')));
			}

			if (method_exists($obj, 'body')) {
				$out = $this->appendToBody($out, $obj->body($this->getUri('../' . $moduleName . '/')));
			}
			else if (isset($obj['body'])) {
				$body = $obj['body'];
				$out = $this->appendToBody($out, $body($this->getUri('../' . $moduleName . '/')));
			}
		}
	}

	private function render($path, $uri, $section) {
		ob_start();
		// TODO check if array
		$arr = require $path;
		$out = ob_get_clean();
		if (!isset($arr['render'])) {
			$arr['render'] = $out;
		} else {
			$arr['render'] = $arr['render']($uri, $section, $out);
		}
		return $arr;
	}

	function onRouterAPI(&$router) {
		$modules = $this->modules;

		$router->group('/components', function () use ($modules) {
			require 'api.php';
		});
	}
}
