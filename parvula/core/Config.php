<?php

namespace Parvula\Core;

/**
 * Configuration wrapper for config array
 *
 * @package Parvula
 * @version 0.1.0
 * @since 0.1.0
 * @author Fabien Sa
 * @license MIT License
 */
class Config {

	/**
	 * @var array
	 */
	private static $config = array();

	/**
	 * Populate class with config array
	 * @param array $config
	 * @return
	 */
	public static function populate(array $config) {
		static::$config = $config;
	}

	/**
	 * Append config to Config class
	 * @param array $config
	 * @return
	 */
	public static function append(array $config) {
		static::$config = $config + static::$config;
	}

	/**
	 * Get configuration value from key
	 * @param mixed $key
	 * @return mixed Value from config
	 */
	public static function get($key) {
		if(isset(static::$config[$key])) {
			return static::$config[$key];
		}

		return;
	}

	/**
	 * Set configuration value from key
	 * @param mixed $key
	 * @param mixed $value
	 * @return
	 */
	public static function set($key, $value) {
		if(!empty($key)) {
			static::$config[$key] = $value;
		}
	}

	/**
	 * Shortcut to {@see Config::get} method
	 * @param mixed $key
	 * @return mixed Value from config
	 */
	public static function __callStatic($key, $_) {
		return static::get($key);
	}

}
