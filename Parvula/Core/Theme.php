<?php

namespace Parvula\Core;

use Parvula\Core\Exception\NotFoundException;

/**
 * This class represents a Theme
 *
 * @package Parvula
 * @version 0.5.0
 * @since 0.5.0
 * @author Fabien Sa
 * @license MIT License
 */
class Theme {

	/**
	 * @var string The theme path
	 */
	private $path;

	private $info;

	private static $THEME_INFO_FILE = 'theme.json';

	/**
	 * Constructor
	 *
	 * @param string $themesPath
	 */
	public function __construct($themePath) {
		if(!file_exists($themePath . '/' . self::$THEME_INFO_FILE)) {
			throw new NotFoundException(
				'`' . self::$THEME_INFO_FILE . '` does not exists for theme ` ' . $themePath . '`');
		}

		$this->path = $themePath;

		$data = file_get_contents($themePath);
		$info = json_decode($data);

		$this->info = $info;
	}

	/**
	 * Returns the absolute theme path
	 *
	 * @return string
	 */
	public function getPath() {
		return $this->path;
	}

	/**
	 * Return theme layouts
	 *
	 * @return array Array of avalible layouts
	 */
	public function getLayouts() {
		return $this->info->layouts;
	}
}
