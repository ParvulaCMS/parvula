<?php
namespace Plugins\ComponentsLoader;

use Parvula\Plugin;

// Dev
// TODO normalize paths
class ComponentsLoader extends Plugin {

	private $components = [];

	const COMPONENTS_PATH = '_components';

	function onPage(&$page) {
		foreach ($page->sections as $id => $section) {
			if ($section->name[0] === ':') {
				$section->component = ltrim($section->name, ':');
			}
			if (isset($section->component)) {
				$componentName = basename($section->component);

				// Class component
				if (is_readable($filePath = $this->getPath('../' . $componentName . '/Component.php'))) {
					$class = 'Plugins\\' . $componentName . '\\Component';
					$obj = new $class;
					$this->components[$section->name] = [
						'section' => $section,
						'instance' => $obj
					];
					if (method_exists($obj, 'render')) {
						$page->sections[$id]->content = $obj->render($filePath, $this->getUri('../' . $componentName . '/'), $section);
					}
				}
				// Simple file
				else if (is_readable($filePath = $this->getPath('../' . self::COMPONENTS_PATH . '/' . $componentName . '.php'))) {
					$arr = $this->renderComponent($componentName, $section);
					$page->sections[$id]->content = $arr['render'];
					$this->components[$section->name] = [
						'section' => $section,
						'instance' => $arr
					];
				}
			}
		}
	}

	function onPostRender(&$out) {
		foreach ($this->components as $component) {
			$obj = $component['instance'];
			$componentName = $component['section']->component;

			if (method_exists($obj, 'header')) {
				$out = $this->appendToHeader($out, $obj->header($this->getUri('../' . $componentName . '/')));
			}
			else if (isset($obj['header'])) {
				$header = $obj['header'];
				$out = $this->appendToHeader($out, $header($this->getUri('../' . $componentName . '/')));
			}

			if (method_exists($obj, 'body')) {
				$out = $this->appendToBody($out, $obj->body($this->getUri('../' . $componentName . '/')));
			}
			else if (isset($obj['body'])) {
				$body = $obj['body'];
				$out = $this->appendToBody($out, $body($this->getUri('../' . $componentName . '/')));
			}
		}
	}

	private function renderComponent($componentName, $section) {
		if (is_readable($filePath = $this->getPath('../' . self::COMPONENTS_PATH . '/' . $componentName . '.php'))) {
			return $this->render($filePath, $this->getUri('../' . $componentName . '/'), $section);
		}
	}

	private function render($path, $uri, $section) {
		ob_start();
		// TODO check if array
		$arr = require $path;
		// Check if props are OK
		$out = ob_get_clean();
		if (!isset($arr['render'])) {
			$arr['render'] = $out;
		} else {
			$arr['render'] = $arr['render']($uri, $section, $out);
		}
		return $arr;
	}

	function onRouterAPI(&$router) {
		$components = $this->components;
		$render = function ($componentName, $section) {
			return $this->renderComponent($componentName, $section);
		};

		$router->group('/components', function () use ($components, $render) {
			require 'api.php';
		});
	}
}
