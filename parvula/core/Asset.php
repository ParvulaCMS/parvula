<?php

namespace Parvula\Core;

/**
 * Assets manager
 *
 * @package Parvula
 * @version 0.1.0
 * @since 0.1.0
 * @author Fabien Sa
 * @license MIT License
 */
class Asset {

	/**
	 * @var string
	 */
	private static $basePath = '';

	/**
	 * Set base path to prefix all assets
	 * @param type $basePath
	 * @return type
	 */
	public static function setBasePath($basePath) {
		static::$basePath = rtrim($basePath, '/') . '/';
	}

	/**
	 * Get assets base path
	 * @return string Base path
	 */
	public static function getBasePath() {
		return static::$basePath;
	}

	/**
	 * Render CSS
	 * @param string|array $css Css filename
	 * @param string ($pattern)
	 * @return string Html output
	 */
	public static function css($css, $pattern = null) {

		if(!is_string($pattern)) {
			$pattern = '<link href="{{file}}" rel="stylesheet" />' . PHP_EOL;
		}

		$out = static::renderTag($css, $pattern);

		return $out;
	}

	/**
	 * Render Javascript
	 * @param string|array $js Js filename
	 * @param string ($pattern)
	 * @return string Html output
	 */
	public static function js($js, $pattern = null) {
		if(!is_string($pattern)) {
			$pattern = '<script src="{{file}}"></script>' . PHP_EOL;
		}

		$out = static::renderTag($js, $pattern);

		return $out;
	}

	/**
	 * Render tag from pattern
	 * Use {{file}} to replace this with real file path in pattern
	 * @param string|array $files
	 * @param string $pattern Pattern to use
	 * @return string Html output
	 */
	private static function renderTag($files, $pattern) {
		if(is_string($files)) {
			$files = array($files);
		}

		$output = '';
		foreach ($files as $file) {
			if(!preg_match('/^https?:\/\//', $file)) {
				$file = static::$basePath . $file;
			}
			$output .= str_replace('{{file}}', $file, $pattern);
		}

		return $output;
	}

}
