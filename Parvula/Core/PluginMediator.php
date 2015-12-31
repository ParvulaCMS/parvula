<?php

namespace Parvula\Core;

use Parvula\Core\Exception\BadObjectCallException;

/**
 * Plugin Mediator class to handle plugins
 *
 * @package Parvula
 * @version 0.5.0
 * @since 0.4.0
 * @author Fabien Sa
 * @license MIT License
 */
class PluginMediator
{
	/**
	 * @var array Plugins container
	 */
	protected $plugins = [];

	/**
	 * Attach new plugins
	 *
	 * @param array $plugins
	 * @throws BadObjectCallException
	 * @return
	 */
	public function attach(array $plugins) {
		foreach ($plugins as $plugin) {
			if (is_string($plugin)) {
				if (!class_exists($plugin)) {
					throw new BadObjectCallException('Plugin class `' . $plugin . '` not found');
				}
				$plugin = new $plugin;
			}

			if (is_object($plugin)) {
				$id = get_class($plugin);
				if (!isset($this->plugins[$id])) {
					$this->plugins[$id] = $plugin;
				}
			}
		}

		return $this;
	}

	/**
	 * Trigger an event
	 *
	 * @param string $event Event name
	 * @param array ($args) Arguments
	 */
	public function trigger($event, array $args = []) {
		$event = 'on' . ucfirst($event);
		foreach ($this->plugins as $plugin) {
			if (method_exists($plugin, $event)) {
				$this->callFunctionArray($plugin, $event, $args);
			}
		}
	}

	/**
	 * Get specific plugin
	 *
	 * @param string $className
	 * @return Plugin Specific plugin
	 */
	public function &getPlugin($className) {
		return $this->plugins[$className];
	}

	/**
	 * Call function (alias for call_user_func_array)
	 *
	 * @param Object $obj Object
	 * @param callable|string $fun Function
	 * @param array $args Arguments
	 */
	private function callFunctionArray($obj, $fun, array $args) {
		switch (count($args)) {
			case 0: $obj->{$fun}(); break;
			case 1: $obj->{$fun}($args[0]); break;
			case 2: $obj->{$fun}($args[0], $args[1]); break;
			case 3: $obj->{$fun}($args[0], $args[1], $args[2]); break;
			default: call_user_func_array([$obj, $fun], $args);  break;
		}
	}
}
