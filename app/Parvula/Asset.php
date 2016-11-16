<?php

namespace Parvula;

/**
 * Assets manager
 *
 * @package Parvula
 * @version 0.5.0
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
	 * Auto render the ressource file
	 * Read the extension and automatically load the right type
	 * @param string|array $ressource Ressource filename
	 * @param string ($pattern)
	 * @return string Html output
	 */
	public static function auto($ressource, $pattern = null) {
		$ext = pathinfo($ressource, PATHINFO_EXTENSION);

		if ($ext === 'js') {
			return Asset::js($name, $pattern);
		} elseif ($ext === 'css') {
			return Asset::css($name, $pattern);
		} else {
			return false;
		}
	}

	/**
	 * Render CSS
	 * @param string|array $css Css filename
	 * @param string ($pattern)
	 * @return string Html output
	 */
	public static function css($css, $pattern = null) {
		if (!$css) {
			return;
		}

		if (!is_string($pattern)) {
			$pattern = '<link href="{{file}}" rel="stylesheet" />' . PHP_EOL;
		}

		return static::renderTag($css, $pattern);
	}

	/**
	 * Render Javascript
	 * @param string|array $js Js filename
	 * @param string ($pattern)
	 * @return string Html output
	 */
	public static function js($js, $pattern = null) {
		if (!$js) {
			return;
		}

		if (!is_string($pattern)) {
			$pattern = '<script src="{{file}}"></script>' . PHP_EOL;
		}

		return static::renderTag($js, $pattern);
	}

	/**
	 * Render tag from pattern
	 * Use {{file}} to replace this with real file path in pattern
	 * @param string|array $files
	 * @param string $pattern Pattern to use
	 * @return string Html output
	 */
	private static function renderTag($files, $pattern) {
		if (is_string($files)) {
			$files = [$files];
		}

		$output = '';
		foreach ($files as $file) {
			if ($file) {
				$output .= str_replace('{{file}}', $file, $pattern);
			}
			if (!preg_match('/^https?:\/\//', $file)) {
				$file = static::$basePath . $file;
			}
		}

		return $output;
	}
}
