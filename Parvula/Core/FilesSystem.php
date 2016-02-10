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
class FilesSystem implements IOInterface {

	/**
	 * @var string
	 */
	private $workingDirectory;

	/**
	 * Constructor
	 *
	 * @param string $workingDirectory
	 */
	function __construct($workingDirectory = '.') {
		$this->workingDirectory = rtrim($workingDirectory, '/') . '/';
	}

	/**
	 * Check if file exists
	 *
	 * @param string $filename File name
	 * @return boolean If file exists
	 */
	public function exists($filename) {
		return file_exists($this->workingDirectory . $filename);
	}

	/**
	 * Check if it is a directory
	 *
	 * @param string $dirname Directory name
	 * @return boolean If direcorty exists
	 */
	public function isDir($dirname) {
		return is_dir($this->workingDirectory . $dirname);
	}

	/**
	 * Read data from file
	 *
	 * @param string $filename File name
	 * @param callable ($fn) Apply function to file data (\SplFileInfo $file, string $data)
	 * @param boolean ($eval) Evaluate PHP
	 * @throws IOException If the file does not exists
	 * @return mixed File data
	 */
	public function read($filename, callable $fn = null, $eval = false) {
		if (!$this->exists($filename)) {
			throw new IOException("File `{$filename}` does not exist");
		}

		$fileInfo = new \SplFileInfo($this->workingDirectory . $filename);

		if ($fileInfo->isReadable()) {
			if ($eval) {
				ob_start();
				include $this->workingDirectory . $filename;
				$data = ob_get_clean();
			} else {
				$file = $fileInfo->openFile('r');
				$data = $file->fread($file->getSize());
			}
		}

		if ($fn !== null) {
			return $fn($fileInfo, $data);
		}

		return $data;
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

		$res = @file_put_contents($this->workingDirectory . $filename, $data);

		if (false === $res) {
			throw new IOException("File `{$filename}` is not writable");
		}

		return $res;
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
			throw new IOException("File `{$filename}` not found");
		}

		return unlink($this->workingDirectory . $filename);
	}

	/**
	 * Check if file is writable
	 *
	 * @param string $filename File name
	 * @return boolean If file is writable
	 */
	public function isWritable($filename = '') {
		return is_writable($this->workingDirectory . $filename);
	}

	/**
	 * Rename file
	 *
	 * @param string $oldName Old file name
	 * @param string $newName New file name
	 * @return boolean
	 */
	public function rename($oldName, $newName) {
		return rename($this->workingDirectory . $oldName, $this->workingDirectory . $newName);
	}

	/**
	 * Try to change the mode of the specified file to that given in mode
	 *
	 * @param  string $filename
	 * @param  int $mode Mode should be an *octal value* (prefixed with a 0)
	 * @return bool
	 */
	public function chmod($filename = '', $mode) {
		return chmod($this->workingDirectory . $filename, $mode);
	}

	/**
	 * Index files recursively in a directory
	 *
	 * @param  string $dir
	 * @param  callable $fn callback for each file `(\SplFileInfo $file, $dir)`
	 * @param  callable $filter callback filter for each file
	 * @return array Files
	 */
	// TODO flags maxDepth
	public function indexAll($dir = '', callable $fn = null, callable $filter = null) {
		$path = $this->workingDirectory . $dir;

		$iterator = new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS);

		if ($filter !== null) {
			$iterator = new \RecursiveCallbackFilterIterator($iterator, $filter);
		}

		$iterator = new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::SELF_FIRST);
		$iterator->setMaxDepth(8);

		$files = [];
		foreach ($iterator as $file) {
			$cdir = substr($file->getPathInfo(), strlen($this->workingDirectory));

			if ($fn) {
				$fn($file, $cdir);
			}
			$files[] = $file->getFileName();
		}

		return $files;
	}

	/**
	 * Index files recursively in a directory (without hidden files)
	 *
	 * @param  string $dir
	 * @param  callable $fn callback for each file `(\SplFileInfo $file, $dir)`
	 * @param  callable $filter callback filter for each file
	 * @return array Files
	 */
	public function index($dir = '', callable $fn = null, $filter = null) {
		if ($filter === null) {
			$filter = function ($current) {
				return $current->getFilename()[0] !== '.';
			};
		}

		return $this->indexAll($dir, $fn, $filter);
	}

	/**
	 * Get working directory
	 *
	 * @return string Current working directory
	 */
	public function getCurrentWorkingDirectory() {
		return $this->workingDirectory;
	}

	/*
	 * Alias for {getCurrentWorkingDirectory}
	 */
	public function getCWD() {
		return $this->workingDirectory;
	}

	/**
	 * Set working directory
	 *
	 * @param string $workingDirectory
	 */
	public function setCurrentWorkingDirectory($workingDirectory) {
		$this->workingDirectory = $workingDirectory;
	}

}
