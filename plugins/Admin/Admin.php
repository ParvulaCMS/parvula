<?php

namespace Plugins\Admin;

use Parvula\Plugin;
use Parvula\Config;

if (!defined('_APP_')) exit;

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
