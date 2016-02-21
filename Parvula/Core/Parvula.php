<?php

namespace Parvula\Core;

// use Parvula\Core\Router\Request;
use Parvula\Core\FilesSystem as Files;
use Parvula\Core\Exception\IOException;
use Psr\Http\Message\ServerRequestInterface;

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
	 * @param ServerRequestInterface $req
	 */
	public static function setRequest(ServerRequestInterface $req) {
		static::$request = $req;
	}

	/**
	 * Get current URI (without /index.php)
	 *
	 * @return string
	 */
	// public static function getURI() {
	// 	return self::$request->getUri();
	//
	// 	//TODO stock URI in field (same for relativeURI)
	// 	$scriptName = self::$request->scriptName;
	//
	// 	$uri = parse_url(self::$request->getUri(), PHP_URL_PATH);
	//
	// 	if (static::$URL_REWRITING) {
	// 		$scriptName = dirname($scriptName);
	// 	}
	//
	// 	$uri = implode(explode($scriptName, $uri, 2));
	//
	// 	return '/' . ltrim($uri, '/');
	// }

	/**
	 * Get relative URI from the root
	 *
	 * @return string
	 */
	public static function getRelativeURIToRoot() {
		$postUrl = static::$request->getUri()->getPath();

		echo "->[$postUrl]";
		echo "->[$basePath]";

		// Be sure to have a clean path
		$postUrl = str_replace(['//', '///'], '/', $postUrl);

		$slashNb = 0;
		if ($postUrl !== '/') {
			$slashNb = substr_count($postUrl, '/');
		}

		// TODO tests
		// Add a '../' to URL if there is not URL rewriting
		if (!static::$URL_REWRITING) {
			++$slashNb;
		}

		return str_repeat('../', max($slashNb, 0));
	}

	// public static function redirectIfTrailingSlash() {
	// 	$postUrl = static::getURI();
	//
	// 	$lastChar = substr($postUrl, -1);
	//
	// 	$newUrl = substr($postUrl, 1);
	//
	// 	if ($lastChar === '/') {
	// 		header('Location: ../' . $newUrl, true, 303);
	// 	}
	// 	// echo $postUrl;
	// }

	/**
	 * Get request method
	 *
	 * @return string
	 */
	public static function getMethod() {
		return static::$request->getMethod();
	}
}
