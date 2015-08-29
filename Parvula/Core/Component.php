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

	// Component::registerCDN('jquery', 'http://lala.com/jq.js');
	// Component::register('jquery', $plugin . "jq.js"); // no ../.. to avoid hack

	//TODO
	// If zip -> save in folder ?
	public static function register($name, $file) {
		$name = self::parseName($name);

		if(!file_exists($basePath . $name)) {
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

    public static function loadCDN($name, $pattern = null) {
		$name = self::parseName($name);

		if(!isset(self::$isLoaded[$name], self::$components[$name])) {
            $ext = pathinfo(self::$basePath . $name, PATHINFO_EXTENSION);

            self::$isLoaded[$name] = true;
            if($ext === 'js') {
                return Asset::js(self::$components[$name], $pattern);
            } else if($ext === 'css') {
                return Asset::css(self::$components[$name], $pattern);
            }

            return $components[$name];
        }
	}

    public static function load($name, $pattern = null) {
		$name = self::parseName($name);

        if(!isset(self::$isLoaded[$name])) {
            $ext = pathinfo(self::$basePath . $name, PATHINFO_EXTENSION);

            Asset::setBasePath(Parvula::getRelativeURIToRoot() . self::$basePath);
            self::$isLoaded[$name] = true;
            if($ext === 'js') {
                return Asset::js($name, $pattern);
            } else if($ext === 'css') {
                return Asset::css($name, $pattern);
            }

            return Parvula::getRelativeURIToRoot() . self::$basePath . '/' . $name;
        }
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

    // unload

    // loadMultiple
}
