<?php

namespace Parvula;

use Parvula\Exceptions\BadObjectCallException;

/**
 * Plugin Mediator class to handle plugins
 *
 * @package Parvula
 * @version 0.5.0
 * @since 0.4.0
 * @author Fabien Sa
 * @license MIT License
 */
class PluginMediator {
	/**
	 * @var array Plugins container
	 */
	protected $plugins = [];

	/**
	 * Attach new plugins
	 *
	 * @param array $plugins
	 * @throws BadObjectCallException
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
	public function trigger($event, array $args = []): void {
		$event = 'on' . ucfirst($event);
		foreach ($this->plugins as $plugin) {
			if (method_exists($plugin, $event)) {
				$plugin->$event(...$args);
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
}
