<?php

namespace Parvula\Core;

use Parvula\Core\Exception\IOException;

/**
 * Files System
 *
 * @package Parvula
 * @version 0.5.0
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
	 *
	 * @param string $prefixPath
	 */
	function __construct($prefixPath = '.') {
		$this->prefixPath = rtrim($prefixPath, '/') . '/';
	}

	/**
	 * Check if file exists
	 *
	 * @param string $filename File name
	 * @return boolean If file exists
	 */
	public function exists($filename) {
		return file_exists($this->prefixPath . $filename);
	}

	/**
	 * Check if it is a directory
	 *
	 * @param string $dirname Directory name
	 * @return boolean If direcorty exists
	 */
	public function isDir($dirname) {
		return is_dir($this->prefixPath . $dirname);
	}

	/**
	 * Read data from file
	 *
	 * @param string $filename File name
	 * @param callable ($fn) Apply function to file data
	 * @param boolean ($eval) Evaluate PHP
	 * @throws IOException If the file does not exists
	 * @return mixed File data
	 */
	public function read($filename, callable $fn = null, $eval = false) {
		if (!$this->exists($filename)) {
			throw new IOException("File '$filename' does not exist.");
		}

		if ($eval) {
			ob_start();
			include $this->prefixPath . $filename;
			$data = ob_get_clean();
		} else {
			$data = file_get_contents($this->prefixPath . $filename);
		}

		if ($fn !== null) {
			return $fn($data);
		} else {
			return $data;
		}
	}

	/**
	 * Write data to file
	 *
	 * @param string $filename File name
	 * @param mixed $data
	 * @param callable ($fn) Apply function to data
	 * @throws IOException if file is not writable
	 */
	public function write($filename, $data, callable $fn = null) {
		if ($fn !== null) {
			$data = $fn($data);
		}

		if (false === @file_put_contents($this->prefixPath . $filename, $data)) {
			throw new IOException("File '$filename' is not writable.");
		}
	}

	/**
	 * Delete file
	 *
	 * @param string $filename File to delete
	 * @throws IOException If the file does not exists
	 * @return boolean If filename is deleted
	 */
	public function delete($filename) {
		if (!$this->exists($filename)) {
			throw new IOException("File '$filename' not found", 1);
		}

		return unlink($this->prefixPath . $filename);
	}

	/**
	 * Check if file is writable
	 *
	 * @param string $filename File name
	 * @return boolean If file is writable
	 */
	public function isWritable($filename) {
		return is_writable($this->prefixPath . $filename);
	}

	/**
	 * Rename file
	 *
	 * @param string $oldName Old file name
	 * @param string $newName New file name
	 * @return boolean
	 */
	public function rename($oldName, $newName) {
		return rename($this->prefixPath . $oldName, $this->prefixPath . $newName);
	}

	/**
	 * List files recursively in a directory
	 *
	 * @param string $directory Directory to list recursively
	 * @param boolean $showHiddenFiles True to list hidden files
	 * @param callable $fn Callback function for each item $fn($key, $val)
	 * @return array Return array of files
	 */
	public function index($dir = '', $showHiddenFiles = false, callable $fn = null) {

		if (!$this->isDir($dir)) {
			throw new IOException("Directory '$dir' not found", 1);
		}

		$fnName = __FUNCTION__;
		$dirFull = $this->prefixPath . $dir;

		$items = [];
		if ($handle = opendir($dirFull)) {
			while (false !== ($file = readdir($handle))) {
				if (($showHiddenFiles || $file[0] !== '.') && ($file !== '.' && $file !== '..')) {
					if (is_dir($dirFull . '/' . $file)) {
						$items[$file] = $this->$fnName($dir . '/' . $file, $showHiddenFiles, $fn);
					} else {
						if ($fn !== null) {
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
