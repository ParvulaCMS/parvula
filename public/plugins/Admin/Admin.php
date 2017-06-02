<?php

namespace Plugins\Admin;

use Conf;
use Parvula\Plugin;

if (!defined('_APP_')) exit;

class Admin extends Plugin
{
	function onRouter(&$router) {
		$configAdmin = new Conf(require 'config.php');

		$that = $this;
		$router->map(['GET', 'POST'], $configAdmin->get('adminRoute'), function () use ($that, $configAdmin) {
			return require_once 'main.php';
		});
	}
}
