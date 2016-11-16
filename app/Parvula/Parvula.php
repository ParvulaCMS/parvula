<?php

namespace Parvula;

use Pimple\Container;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Parvula
 *
 * @package Parvula
 * @version 0.8.0
 * @since 0.1.0
 * @author Fabien Sa
 * @license MIT License
 */
class Parvula {
	private static $request;
	private static $container = null;

	protected function __construct() {
	}

	protected function __clone() {
	}

	public static function getContainer() {
		if (self::$container === null) {
			self::$container = new Container;
		}
		return self::$container;
	}

	/**
	 * Set Request
	 *
	 * @param ServerRequestInterface $req
	 */
	public static function setRequest(ServerRequestInterface $req) {
		static::$request = $req;
	}

	/**
	 * Get relative URI from the root
	 *
	 * @param string $path Append a path to the URI
	 * @return string
	 */
	public static function getRelativeURIToRoot($path = '') {
		$postUrl = static::$request->getUri()->getPath();
		$basePath = static::$request->getUri()->getBasePath();

		// Be sure to have a clean path
		$postUrl = str_replace(['//', '///'], '/', $postUrl);

		$slashNb = 0;
		if ($postUrl !== '/') {
			$slashNb = substr_count($postUrl, '/');
		}

		// TODO tests
		// Add a '../' to URL if there is no URL rewriting
		if (substr($basePath, -9) === 'index.php') {
			++$slashNb;
		}

		return str_repeat('../', max($slashNb, 0)) . $path;
	}

	/**
	 * Get request method
	 *
	 * @return string
	 */
	public static function getMethod() {
		return static::$request->getMethod();
	}
}
