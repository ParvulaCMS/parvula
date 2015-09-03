<?php

namespace Parvula\Core;

/**
 * Plugin class @TODO
 *
 * @package Parvula
 * @version 0.5.0
 * @since 0.5.0
 * @author Fabien Sa
 * @license MIT License
 */
abstract class Plugin {

	protected $asset;

	function __construct() {
		// $this->asset = Asset;
		// $this->asset::setBasePath('qweqwe');
		// // facade
		// $class = get_called_class();
		// // $a = static::$asset;
		// // $a::setBasePath("qweqwe");
		// // static::$asset = $a;
		// // echo $a::css('as');

		// Asset::setBasePath($this->getPluginPath());

		// // $as = Asset;
		// // self::$asset = $as;
	}

	protected function getPluginPath() {
		$class = get_called_class();
		$class = str_replace('\\', '/', $class);
		$class = dirname($class);
		$class = str_replace('Plugin/', '', $class);
		return PLUGINS . $class . '/';
	}

	protected function getRelativePluginPath() {
		return Parvula::getRelativeURIToRoot() . $this->getPluginPath();
	}

	private function appendToElement($element, $html, $append) {
		// @TODO a bit hacky, need to clean and find correctly the `</head>`

		libxml_use_internal_errors(true); // html5 ok
		$dom = new \DOMDocument();
		$dom->loadHTML($html);
		$node = $dom->getElementsByTagName($element)->item(0);

		if ($node) {
			$lineNo = $node->lastChild->getLineNo();
		} else {
			$lineNo = 0;
		}

		$outArr = explode("\n", $html);

		// Lines to skip to go to the end of the node
		$n = substr_count($node->nodeValue, "\n");

		$outArr[($lineNo - 1) + $n] .= PHP_EOL . $append;

		return implode($outArr, "\n");
	}

	protected function appendToHeader($html, $append) {
		return $this->appendToElement('head', $html, $append);
	}

	protected function appendToBody($html, $append) {
		return preg_replace("/(< ?\/body)/", $append . "$1", $html);
	}
}