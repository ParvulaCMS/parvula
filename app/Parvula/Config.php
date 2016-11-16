<?php

namespace Parvula;

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
		$pieces = explode('.', $key);
		$ptr = &$this->config;

		foreach ($pieces as $step) {
			if (!isset($ptr[$step])) {
				return $default;
			}
			$ptr = &$ptr[$step];
		}

		return $ptr;
	}

	/**
	 * Set configuration value from key (create a new key if needed)
	 *
	 * @param mixed $key
	 * @param mixed $value
	 * @param bool True if value is set
	 */
	public function set($key, $value) {
		$pieces = explode('.', $key);
		$ptr = &$this->config;

		foreach($pieces as $step) {
			$ptr = &$ptr[$step];
			if (!isset($ptr[$step])) {
				$ptr = [];
			}
		}

		return $ptr = $value;
	}

	/**
	 * Edit configuration value from key (without creating a new key)
	 *
	 * @param mixed $key
	 * @param mixed $value
	 * @param bool False if the value does not exists
	 */
	public function edit($key, $value) {
		$pieces = explode('.', $key);
		$ptr = &$this->config;

		foreach($pieces as $step) {
			if (!isset($ptr[$step])) {
				return false;
			}
			$ptr = &$ptr[$step];
		}

		$ptr = $value;
		return true;
	}

	/**
	 * Check if key exists
	 *
	 * @return bool
	 */
	public function has($key) {
		$pieces = explode('.', $key);
		$ptr = &$this->config;

		foreach($pieces as $step) {
			if (!isset($ptr[$step])) {
				return false;
			}
			$ptr = &$ptr[$step];
		}

		return true;
	}

	/**
	 * Get the configuration as an array
	 *
	 * @return array
	 */
	public function toArray() {
		return $this->config;
	}

	/**
	 * Get the configuration as an object
	 *
	 * @return object
	 */
	public function toObject() {
		return (object) $this->config;
	}

}
