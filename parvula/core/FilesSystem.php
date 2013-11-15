<?php

namespace Parvula\Core;

use Parvula\Core\Exception\IOException;

/**
 * Files System
 *
 * @package Parvula
 * @version 0.1.0
 * @since 0.1.0
 * @author Fabien Sa
 * @license MIT License
 */
class FilesSystem {

	/**
	 * @var string
	 */
	private $prefixPath;

	/**
	 * Constructor
	 * @param string $prefixPath
	 */
	function __construct($prefixPath = '.') {
		$this->prefixPath = rtrim($prefixPath) . '/';
	}

	/**
	 * Check if file exists
	 * @param string $filename File name
	 * @return boolean If file exists
	 */
	public function exists($filename) {
		return is_file($this->prefixPath . $filename);
	}

	/**
	 * Check if directory exists
	 * @param string $dirname Directory name
	 * @return boolean If direcorty exists
	 */
	public function existsDir($dirname) {
		return is_dir($this->prefixPath . $dirname);
	}

	/**
	 * Read data from file
	 * @param string $filename File name
	 * @param callable ($fn) Apply function to file data
	 * @return mixed File data
	 */
	public function read($filename, $fn = null) {
		if(!$this->exists($filename)) {
			throw new IOException("File '$filename' not found", 1);
		}

		$data = file_get_contents($this->prefixPath . $filename);

		if($fn !== null) {
			return $fn($data);
		} else {
			return $data;
		}
	}

	/**
	 * Write data to file
	 * @param string $filename File name
	 * @param mixed $data
	 * @param callable ($fn) Apply function to data
	 * @return boolean
	 */
	public function write($filename, $data, $fn = null) {
		if($fn !== null) {
			$data = $fn($data);
		}

		return file_put_contents($this->prefixPath . $filename, $data);
	}

	/**
	 * List files recursively in a directory
	 * @param string $directory Directory to list recursively
	 * @param boolean $showHiddenFiles True to list hidden files
	 * @param callable $fn Callback function for each item $fn($key, $val)
	 * @return array Return array of files
	 */
	public function getFilesList($dir = '', $showHiddenFiles = false, $fn = null) {
		$fnName = __FUNCTION__;

		$items = array();
		if($handle = opendir($this->prefixPath . $dir)) {
			while(false !== ($file = readdir($handle))) {
				if(($showHiddenFiles || $file[0] !== '.') && ($file !== '.' && $file !== '..')) {
					if(is_dir($this->prefixPath . $dir . '/' . $file)) {
						$items[$file] = $this->$fnName($dir . '/' . $file, $showHiddenFiles, $fn);
					} else {
						if($fn !== null) {
							$fn($file, $dir);
						}
						$items[] = $file;
					}
				}
			}
			closedir($handle);
		}
		return $items;
	}

}
