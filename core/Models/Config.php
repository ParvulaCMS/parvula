<?php

namespace Parvula\Models;

use ArrayAccess;
use Parvula\ArrayableInterface;
use Psr\Container\ContainerInterface;

/**
 * Configuration wrapper for config array.
 *
 * @version 0.8.0
 * @since 0.1.0
 * @author Fabien Sa
 * @license MIT License
 */
class Config extends Model implements ContainerInterface, ArrayableInterface, ArrayAccess
{
	/**
	 * @var array
	 */
	protected $config = [];

	/**
	 * @var array
	 */
	protected $invisible = [
		'_id'
	];

	/**
	 * Populate class with config array.
	 *
	 * @param array $config
	 */
	public function __construct(array $config) {
		$this->config = $config;
	}

	/**
	 * Append config to Config class.
	 *
	 * @param  array  $config
	 * @return Config
	 */
	public function append(array $config) {
		$this->config = $config + $this->config;

		return $this;
	}

	/**
	 * Get configuration value from key.
	 *
	 * @param  string $key
	 * @param  mixed  $default optional Default if value if nothing
	 * @return mixed  Value from config
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
	 * Set configuration value from key (create a new key if needed).
	 *
	 * @param string $key
	 * @param mixed  $value
	 * @param bool True if value is set
	 */
	public function set(string $key, $value) {
		$pieces = explode('.', $key);
		$ptr = &$this->config;

		foreach ($pieces as $step) {
			$ptr = &$ptr[$step];
			if (!isset($ptr[$step])) {
				$ptr = [];
			}
		}

		return $ptr = $value;
	}

	/**
	 * Edit configuration value from key (without creating a new key).
	 *
	 * @param string $key
	 * @param mixed  $value
	 * @param bool False if the value does not exists
	 */
	public function edit(string $key, $value) {
		$pieces = explode('.', $key);
		$ptr = &$this->config;

		foreach ($pieces as $step) {
			if (!isset($ptr[$step])) {
				return false;
			}
			$ptr = &$ptr[$step];
		}

		$ptr = $value;

		return true;
	}

	/**
	 * Check if key exists.
	 *
	 * @param  string $key
	 * @return bool
	 */
	public function has($key): bool {
		$pieces = explode('.', $key);
		$ptr = &$this->config;

		foreach ($pieces as $step) {
			if (!isset($ptr[$step])) {
				return false;
			}
			$ptr = &$ptr[$step];
		}

		return true;
	}

	/**
	 * Get the configuration as an array.
	 *
	 * @return array
	 */
	public function toArray(): array {
		return $this->config;
	}

	/**
	 * Get the configuration as an object.
	 *
	 * @return object
	 */
	public function toObject() {
		return (object) $this->config;
	}

	/**
	 * alias for `set`.
	 *
	 * @see Config::set
	 */
	public function offsetSet($key, $value): void {
		$this->set($key, $value);
	}

	/**
	 * alias for `has`.
	 *
	 * @see Config::has
	 */
	public function offsetExists($key) {
		return $this->has($key);
	}

	public function offsetUnset($offset): void {
	}

	/**
	 * alias for `get`.
	 *
	 * @see Config::get
	 */
	public function offsetGet($key) {
		return $this->get($key);
	}
}
