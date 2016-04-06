<?php

namespace Parvula;

use DOMDocument;

/**
 * Plugin class @TODO
 * Abstract class, need to be inherited to create a new plugin
 *
 * Minimal exemple :
 * <pre>
 * namespace Plugin\Slider;
 *
 * class Slider extends \Parvula\Plugin { ... }
 * </pre>
 *
 * @package Parvula
 * @version 0.5.0
 * @since 0.5.0
 * @author Fabien Sa
 * @license MIT License
 */
abstract class Plugin
{
	/**
	 * @var string Plugin path
	 */
	protected $pluginPath;

	/**
	 * @var string Plugin URI
	 */
	protected $pluginUri;

	/**
	 * @var Parvula Application to avoid global variables or static class
	 */
	protected $app;

	function __construct() {
		$this->pluginPath = $this->getPluginPath();
		$this->pluginUri = $this->getPluginUri();
	}

	/**
	 * Bootstrap plugin to pass the $app
	 */
	public function onBootstrap(Parvula $app) {
		$this->app = $app;
	}

	/**
	 * Get the current plugin path, useful for the backend part
	 * @return string the current plugin path
	 */
	protected function getPluginPath() {
		$class = get_called_class();
		$class = str_replace('\\', '/', $class);
		$class = dirname($class);
		$class = str_replace('Plugin/', '', $class);
		return _PLUGINS_ . $class . '/';
	}

	/**
	 * Get the current plugin URI, useful for the client part
	 * @return string the current URI path
	 */
	protected function getPluginUri() {
		return Parvula::getRelativeURIToRoot() . $this->getPluginPath();
	}

	/**
	 * Append string to the given element
	 *
	 * @param  string $html
	 * @param  string $append
	 * @return string Html ouput
	 */
	private function appendToElement($element, $html, $append) {
		// @TODO a bit hacky, need to clean and find correctly the `</head>`

		if (strlen($html) < 10) {
			return false;
		}

		libxml_use_internal_errors(true); // html5 ok
		$dom = new DOMDocument();
		$dom->loadHTML($html);
		$node = $dom->getElementsByTagName($element)->item(0);

		$lineNo = 0;
		if ($node) {
			$lineNo = $node->lastChild->getLineNo();
		}

		$outArr = explode("\n", $html);

		// Lines to skip to go to the end of the node
		$linesToSkip = substr_count($node->nodeValue, "\n");
		$outArr[($lineNo - 1) + $linesToSkip] .= PHP_EOL . $append;

		return implode($outArr, "\n");
	}

	/**
	 * Append string to the header element (<head>)
	 *
	 * @param  string $html
	 * @param  string $append
	 * @return string Html ouput
	 */
	protected function appendToHeader($html, $append) {
		return $this->appendToElement('head', $html, $append);
	}

	/**
	 * Append string to the body element (<body>)
	 *
	 * @param  string $html
	 * @param  string $append
	 * @return string Html ouput
	 */
	protected function appendToBody($html, $append) {
		return preg_replace("/(< ?\/ ?body)/", $append . "$1", $html);
	}
}
