<?php

namespace Plugin\Admin;

use Parvula\Core\Plugin;

class Admin extends Plugin {
	function onRouter(&$router) {

		$conf = require 'conf.php';
		$pluginPath = $this->getPluginPath();

		$router->any($conf['adminRoute'], function() use ($pluginPath) {
			require 'main.php';
		});
	}
}
