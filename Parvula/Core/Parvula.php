<?php

namespace Parvula\Core;

use Parvula\Core\Router\Request;
use Parvula\Core\FilesSystem as Files;
use Parvula\Core\Exception\IOException;

/**
 * Parvula
 *
 * @package Parvula
 * @version 0.5.0
 * @since 0.1.0
 * @author Fabien Sa
 * @license MIT License
 */
class Parvula extends Container
{

	private static $URL_REWRITING = true;

	private static $request;

	/**
	 * Set Request
	 *
	 * @param Request $req
	 */
	public static function setRequest(Request $req) {
		self::$request = $req;
	}

	/**
	 * Get current URI (without /index.php)
	 *
	 * @return string
	 */
	public static function getURI() {
		//TODO stock URI in field (same for relativeURI)
		$scriptName = self::$request->scriptName;

		$uri = parse_url(self::$request->uri, PHP_URL_PATH);

		if(static::$URL_REWRITING) {
			$scriptName = dirname($scriptName);
		}

		return implode(explode($scriptName, $uri, 2));
	}

	/**
	 * Get relative URI from the root
	 *
	 * @return string
	 */
	public static function getRelativeURIToRoot() {
		$postUrl = static::getURI();

		$postUrl = str_replace(['//', '\\'], '/', $postUrl);
		$slashNb = substr_count($postUrl, '/');

		// Add a '../' to URL if there is not URL rewriting
		if (!static::$URL_REWRITING) {
			++$slashNb;
		}

		return str_repeat('../', max($slashNb - 1, 0));
	}

	public static function redirectIfTrailingSlash() {
		$postUrl = static::getURI();

		$lastChar = substr($postUrl, -1);

		$newUrl = substr($postUrl, 1);

		if($lastChar === '/') {
			header('Location: ../' . $newUrl, true, 303);
		}
		// echo $postUrl;
	}

	/**
	 * Get request method
	 *
	 * @return string
	 */
	public static function getMethod() {
		return self::$request->method;
	}

	/**
	 * PSR-0 autoloader to run Parvula without composer
	 *
	 * @param string $className
	 * @return
	 */
	public static function autoload($className) {
		$className = ltrim($className, '\\');
		$fileName  = '';
		$namespace = '';
		if ($lastNsPos = strrpos($className, '\\')) {
			$namespace = substr($className, 0, $lastNsPos);
			$className = substr($className, $lastNsPos + 1);
			$fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
		}
		$fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

		if (file_exists($fileName) || file_exists($fileName = VENDOR . $fileName)) {
			require $fileName;
		}
	}

	/**
	 * Register Parvula autoloader
	 *
	 * @return
	 */
	public static function registerAutoloader() {
		spl_autoload_register(__NAMESPACE__ . "\\Parvula::autoload");
	}
}
