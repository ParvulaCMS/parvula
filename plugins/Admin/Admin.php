<?php

namespace Plugin\Admin;

use Parvula\Core\Plugin;
use Parvula\Core\Config;

class Admin extends Plugin
{
	function onRouter(&$router) {
		$configAdmin = new Config(require 'config.php');

		$that = $this;
		$router->map(['GET', 'POST'], $configAdmin->get('adminRoute'), function () use ($that, $configAdmin) {
			return require_once 'main.php';
		});
	}
}
