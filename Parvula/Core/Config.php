<?php

namespace Parvula\Core;

/**
 * Configuration wrapper for config array
 *
 * @package Parvula
 * @version 0.5.0
 * @since 0.1.0
 * @author Fabien Sa
 * @license MIT License
 */
class Config {

	/**
	 * @var array
	 */
	private $config = [];

	/**
	 * Populate class with config array
	 *
	 * @param array $config
	 */
	public function __construct(array $config) {
		$this->config = $config;
	}

	/**
	 * Append config to Config class
	 *
	 * @param array $config
	 * @return
	 */
	public function append(array $config) {
		$this->config = $config + $this->config;
	}

	/**
	 * Get configuration value from key
	 *
	 * @param mixed $key
	 * @param mixed $default optional Default if value if nothing
	 * @return mixed Value from config
	 */
	public function get($key, $default = null) {
		if (isset($this->config[$key])) {
			return $this->config[$key];
		}

		return $default;
	}

	/**
	 * Set configuration value from key
	 *
	 * @param mixed $key
	 * @param mixed $value
	 * @return
	 */
	public function set($key, $value) {
		if (!empty($key)) {
			$this->config[$key] = $value;
		}
	}

}
