<?php

namespace Parvula\Services;

use Parvula\IOInterface;
use Parvula\FilesSystem as Files;
use Parvula\Models\Theme;
use Parvula\Exceptions\NotFoundException;

/**
 * Themes service
 *
 * @package Parvula
 * @version 0.8.0
 * @since 0.5.0
 * @author Fabien Sa
 * @license MIT License
 */
class ThemesService {

	/**
	 * @var FilesSystem
	 */
	private $fs;

	/**
	 * @var IOInterface
	 */
	private $configIO;

	/**
	 * @var string Theme config // TODO
	 */
	private static $THEME_INFO_FILE = 'theme.yml';

	/**
	 * Constructor
	 *
	 * @param string $themesPath
	 */
	public function __construct($themesPath, IOInterface $configSystem) {
		$this->fs = new Files($themesPath);
		$this->themesPath = $themesPath;
		$this->configIO = $configSystem;
	}

	/**
	 * Get a page object with parsed content
	 *
	 * @param string $pageUID Page unique ID
	 * @throws IOException If the page does not exists
	 * @return Page Return the selected page
	 */
	public function get($themeName) {
		if (!$this->has($themeName)) {
			return false;
		}

		$path = $this->fs->getCWD() . $themeName . '/';

		if (!file_exists($path . self::$THEME_INFO_FILE)) {
			throw new NotFoundException('Invalid theme: `' . self::$THEME_INFO_FILE .
				'` does not exists for theme ` ' . $path . '`');
		}

		// Read theme config
		$infos = $this->configIO->read($path . self::$THEME_INFO_FILE);

		if ($this->fs->isDir($themeName . '/_layouts')) {
			$infos['layouts'] = '_layouts';
		}

		return new Theme($path, $infos);
	}

	/**
	 * Check if a theme exists
	 *
	 * @param string $themeName
	 * @return bool If the theme exists
	 */
	public function has($themeName) {
		return $this->fs->exists($themeName);
	}

	/**
	 * List themes
	 *
	 * @return array Array of themes available
	 */
	public function index() {
		// @next -> use $fs
		$path = $this->themesPath;
		$dirs = array_diff(scandir($path), ['.', '..']);
		return array_values(array_filter($dirs, function ($val) use ($path) {
			return is_dir($path . '/' . $val);
		}));

		// return $this->fs->index(); // TODO no recusrion
	}
}
