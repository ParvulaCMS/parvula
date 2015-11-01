<?php

namespace Parvula\Core\Model;

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

	/**
	 * @var
	 */
	private $extension = 'html';

	/**
	 * @var string[] Theme layouts
	 */
	public $layouts;

	/**
	 * @var string Theme name
	 */
	public $name;

	/**
	 * @var string Theme infos (author, desc, ...)
	 */
	public $infos;

	/**
	 * @var string Theme config // TODO
	 */
	private static $THEME_INFO_FILE = 'theme.json';

	/**
	 * Constructor
	 *
	 * @param string $themesPath
	 */
	public function __construct($themePath) {
		$this->path = rtrim($themePath, '/') . '/';

		if(!file_exists($this->path . self::$THEME_INFO_FILE)) {
			throw new NotFoundException(
				'Invalid theme: `' . self::$THEME_INFO_FILE . '` does not exists for theme ` ' . $this->path . '`');
		}

		// Read theme config
		$data = file_get_contents($this->path . self::$THEME_INFO_FILE);
		$infos = json_decode($data);
		$this->infos = new \StdClass;

		foreach ($infos as $key => $value) {
			if (property_exists($this, $key)) {
				// echo $key;
				// if (in_array($key, ['name', 'layouts'])) {
				$this->{$key} = $value;
			} else {
				$this->infos->{$key} = $value;
			}
		}

		// if (empty($this->layouts)) {
			// $this->layouts = ['index' => 'index.' . ]
		// }

		// 	$files = glob($this->path . '*.html');
		// 	print_r($files);
		// if (empty($this->layouts)) {
		// 	// $this->layouts = ['index' => 'index.html'];
		// }


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
		return $this->layouts;
	}

	/**
	 * Check if given layout is available
	 *
	 * @param string $layoutName
	 * @return bool If layout is available
	 */
	public function hasLayout($layoutName) {
		return isset($this->layouts->{$layoutName});
	}
}
