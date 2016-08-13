<?php
namespace Plugins\ComponentsLoader;

use Exception;
use Parvula\Plugin;

if (!defined('_APP_')) exit;

// Dev.2
class ComponentsLoader extends Plugin {
	// Components in the page
	private $components = [];

	const COMPONENTS_DIR = '_components';

	function onPage(&$page) {
		foreach ($page->sections as $id => $section) {
			if ($section->name[0] === ':') {
				$section->component = ltrim($section->name, ':');
			}

			if (isset($section->component)) {
				$componentName = str_replace('../', '', $section->component);
				$path = $this->getComponentPath($componentName);

				// Class component
				// if (is_readable($filePath = $this->getPath('../' . $componentName . '/component.php'))) {
				// 	$class = 'Plugins\\' . $componentName . '\\Component';
				// 	$obj = new $class;
				// 	$this->components[$section->name] = [
				// 		'section' => $section,
				// 		'instance' => $obj
				// 	];
				// 	if (method_exists($obj, 'render')) {
				// 		$page->sections[$id]->content = $obj->render($filePath, $this->getUri('../' . $componentName . '/'), $section);
				// 	}
				// }
				//

				// Simple file component
				if (is_readable($path)) {
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

			// TODO check
			// if (method_exists($obj, 'header')) {
			// 	$out = $this->appendToHeader($out, $obj->header($this->getUri('../' . $componentName . '/')));
			// }
			// else
			if (isset($obj['header'])) {
				$header = $obj['header'];
				$out = $this->appendToHeader($out, $header($this->getUri('../' . $componentName . '/')));
			}

			// if (method_exists($obj, 'body')) {
			// 	$out = $this->appendToBody($out, $obj->body($this->getUri('../' . $componentName . '/')));
			// }
			// else
			if (isset($obj['body'])) {
				$body = $obj['body'];
				$out = $this->appendToBody($out, $body($this->getUri('../' . $componentName . '/')));
			}
		}
	}

	private function renderComponent($componentName, $section) {
		if (is_readable($filePath = $this->getComponentPath($componentName))) {

			$plugin = null;
			if (($pluginName = dirname($componentName)) !== '.') {
				$pluginsMediator = $this->app['plugins'];
				$plugin = $pluginsMediator->getPlugin(getPluginClassname($pluginName));
			}

			return $this->render($filePath, $this->getUri('../' . $componentName . '/'), $section, $plugin);
		}
	}

	/**
	 * Render a given component
	 *
	 * @param  string  $path Component file path
	 * @param  string  $uri
	 * @param  Section $section
	 * @param  mixed   $bind To bind $this to the given class
	 * @return array
	 */
	private function render($path, $uri, $section, $bind = null) {
		ob_start();
		$arr = (function () use ($path, $uri, $section) {
			return require $path;
		})->bindTo($bind)();
		$out = ob_get_clean();

		if ($arr !== (array) $arr) {
			$arr = [];
		}

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

		$getComponentPath = function ($path) {
			return $this->getComponentPath($path);
		};

		$router->group('/components', function () use ($getComponentPath, $components, $render) {
			require 'api.php';
		});
	}

	/**
	 * Get component path
	 *
	 * @param  string $component
	 * @return string
	 */
	protected function getComponentPath($component) {
		$file = basename($component) . '.php';
		$path = rtrim(dirname($component), '/') . '/';

		return _PLUGINS_ . $path . self::COMPONENTS_DIR . '/' . $file;
	}
}
