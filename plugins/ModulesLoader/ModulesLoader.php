<?php
namespace Plugin\ModulesLoader;

use Parvula\Plugin;

// Dev
// TODO normalize paths
class ModulesLoader extends Plugin {

	private $modules = [];

	const MODULE_PATH = '_Modules';

	function onPage(&$page) {
		foreach ($page->sections as $k => $section) {
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
					$page->sections[$k]->content = $this->render($filePath, [
						'section' => $section,
						'uri' => $this->getUri('../' . $moduleName . '/')
					]);
				}
			}
		}
	}

	function onPostRender(&$out) {
		foreach ($this->modules as $module) {
			$obj = $module['instance'];
			$module = $module['section']->module;
			if (method_exists($obj, 'header')) {
				$out = $this->appendToHeader($out, $obj->header($this->getUri('../' . $module . '/')));
			}

			if (method_exists($obj, 'body')) {
				$out = $this->appendToBody($out, $obj->body($this->getUri('../' . $module . '/')));
			}
		}
	}

	private function render($path, $data) {
		extract($data);
		ob_start();
		include $path;
		return ob_get_clean();
	}
}
