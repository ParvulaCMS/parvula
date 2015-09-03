<?php

namespace Parvula\Core;

/**
 * Component manager [@TODO]
 *
 * @package Parvula
 * @version 0.1.0
 * @since 0.5.0
 * @author Fabien Sa
 * @license MIT License
 */
class Component {

	/**
	 * @var string
	 */
	private static $basePath = 'components/';

    private static $components = [];

    private static $isLoaded = [];

	// private static $bowerPackages = 'https://bower.herokuapp.com/packages/';

	// Component::registerCDN('jquery', 'http://lala.com/jq.js');
	// Component::register('jquery', $plugin . "jq.js"); // no ../.. to avoid hack

	//TODO
	// If zip -> save in folder ?
	public static function register($name, $file) {
		$name = self::parseName($name);

		if(!file_exists(static::$basePath . $name)) {
			// echo ">> $basePath - $name";
            mkdir($pathname . $name);
			$data = file_get_contents($file);
			file_put_contents($pathname . $name, $data);
		}
	}

	public static function registerCDN($name, $url) {
		$name = self::parseName($name);

		self::$components[$name] = $url;
	}

    public static function loadCDN($name) {
		$name = self::parseName($name);

		if(!isset(self::$isLoaded[$name], self::$components[$name])) {
            $ext = pathinfo(self::$basePath . $name, PATHINFO_EXTENSION);

            self::$isLoaded[$name] = true;

            return $components[$name];
        }
	}

	private static function readBowerConf($packageName) {
		$filePath = static::$basePath . $packageName . '/bower.json';

		if(!is_file($filePath)) {
			return false;
		}

		$bowerJson = file_get_contents($filePath);
		return json_decode($bowerJson);
	}

	public static function load($packageName, $path = null) {
		$packageName = strtolower($packageName);

		if ($path === null) {
			$conf = static::readBowerConf($packageName);
			if (!$conf) {
				return false;
			}
			$path = '/' . $conf->main;
		}

		// $nameFolder = self::parseName($name);

		if (!isset(static::$isLoaded[$packageName])) {
			static::$isLoaded[$packageName] = true;

			return './' . Parvula::getRelativeURIToRoot() . self::$basePath . $packageName . $path;
		}

		return false;
	}

	private static function parseName($name) {
		$token = explode(':', $name, 2);
		$name = str_replace(' ', '_', strtolower($token[0]));
		$version = 'last';
		if(sizeof($token) === 2) {
			$version = $token[1];
		}

		return $name;
	}

	public static function exists($packageName) {
		return is_readable(static::$basePath . $packageName);
	}

	// public static function install($packageName, $source = true) {
	// }

    // unload

    // loadMultiple
}
