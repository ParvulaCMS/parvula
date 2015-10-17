<?php

namespace Parvula\Core\Model;

use Parvula\Core\FilesSystem as Files;
use Parvula\Core\Theme;

/**
 * Themes Manager
 *
 * @package Parvula
 * @version 0.5.0
 * @since 0.5.0
 * @author Fabien Sa
 * @license MIT License
 */
class Themes implements CRUDInterface
{
	private $fs;

	/**
	 * Constructor
	 *
	 * @param string $themesPath
	 */
	public function __construct($themesPath) {
		$this->fs = new Files($themesPath);
	}

	/**
	 * Get a page object with parsed content
	 *
	 * @param string $pageUID Page unique ID
	 * @throws IOException If the page does not exists
	 * @return Page Return the selected page
	 */
	public function read($themeName) {
		if (!$this->has($themeName)) {
			return false;
		}

		return new Theme($this->fs->getCWD() . $themeName);
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
	 * @return array
	 */
	public function index() {
		return $this->fs->index(); // TODO no recusrion
	}

	public function update($theme, $data) {
	}

	public function create($data) {
	}

	public function delete($data) {
	}

}
