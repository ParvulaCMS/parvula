<?php

namespace Parvula\Models;

use Parvula\FilesSystem as FS;
use StdClass;

/**
 * This class represents a Theme.
 *
 * @version 0.5.0
 * @since 0.5.0
 * @author Fabien Sa
 * @license MIT License
 */
class Theme
{
	/**
	 * @var string The theme path
	 */
	private $path;

	/**
	 * @var
	 */
	private $extension;

	/**
	 * @var string[] Theme layouts
	 */
	public $layouts;

	/**
	 * @var string Folder of layouts
	 */
	private $layoutsFolder;

	/**
	 * @var string Default layout name
	 */
	private $defaultLayout;

	/**
	 * @var string Theme name
	 */
	public $name;

	/**
	 * @var string Theme infos (author, desc, ...)
	 */
	public $infos;

	/**
	 * Constructor.
	 *
	 * @param string       $path
	 * @param object|array $infos
	 */
	public function __construct($path, $infos) {
		$this->path = $path;

		$this->infos = new StdClass();

		$defaultInfos = [
			'layouts' => '',
			'extension' => 'html',
			'defaultLayout' => 'default',
		];
		$infos += $defaultInfos;

		foreach ($infos as $key => $value) {
			if (property_exists($this, $key)) {
				$this->{$key} = $value;
			} else {
				$this->infos->{$key} = $value;
			}
		}

		$this->layoutsFolder = rtrim($this->layouts, '/') . '/';

		if (!is_dir($this->path . $this->layoutsFolder)) {
			throw new \Exception('Layouts folder `' . $this->layoutsFolder . '` is not valid.');
		}

		$this->layouts = new StdClass();
		$filter = function ($file) {
			return $this->extension === $file->getExtension() && $file->getBasename()[0] !== '_';
		};

		(new FS($this->path))->index($this->layoutsFolder, function ($file, $dir): void {
			$file = $file->getBasename('.' . $file->getExtension());
			if ($dir) {
				$dir .= '/';
			}
			$this->layouts->{$file} = $dir . $file;
		}, $filter);
	}

	/**
	 * Returns the absolute theme path.
	 *
	 * @return string
	 */
	public function getPath() {
		return $this->path;
	}

	/**
	 * Get file extensions for template files.
	 *
	 * @return string Extension
	 */
	public function getExtension() {
		return $this->extension;
	}

	/**
	 * Return layout folder.
	 *
	 * @return string
	 */
	public function getLayoutFolder() {
		return $this->layoutsFolder;
	}

	/**
	 * Return theme layouts.
	 *
	 * @return array Array of avalible layouts
	 */
	public function getLayouts() {
		return $this->layouts;
	}

	/**
	 * Return the path of the given layout.
	 *
	 * @param string [$layoutName] Optional layout name. If nothing, use the default layout
	 * @return bool|string Return false if the layout does not exists
	 */
	public function getLayout($layoutName = false) {
		if (!$layoutName) {
			$layoutName = $this->defaultLayout;
		}

		if (!$this->hasLayout($layoutName)) {
			return false;
		}

		return $this->layouts->{$layoutName};
	}

	/**
	 * Check if given layout is available.
	 *
	 * @param  string $layoutName Layout name
	 * @return bool   If layout is available
	 */
	public function hasLayout($layoutName) {
		return isset($this->layouts->{$layoutName});
	}
}
