<?php

namespace Parvula\Core;

use Parvula\Core\FilesSystem as Files;
use Parvula\Core\Exception\IOException;

/**
 * Parvula
 *
 * @package Parvula
 * @version 0.2.2
 * @since 0.1.0
 * @author Fabien Sa
 * @license MIT License
 */
class Parvula {

	/**
	 * @var array<Page>
	 */
	private $pages;

	/**
	 * @var string
	 */
	private $fileExtention;

	/**
	 * @var PageSerializerInterface
	 */
	private $serializer;

	/**
	 * Constructor
	 * @param Parvula\Core\PageSerializerInterface $customSerializer (optional)
	 */
	function __construct(PageSerializerInterface $customSerializer = null) {
		$this->fileExtension =  '.' . Config::fileExtension();

		$this->setSerializer($customSerializer);
	}

	/**
	 * Get a page object in html string
	 * @param string $pagePath Page path
	 * @throws IOException If the page does not exists
	 * @return Parvula\Core\Page Return the selected page
	 */
	public function getPage($pagePath) {

		// If page was already loaded, return page
		if(isset($this->pages[$pagePath])) {
			return $this->pages[$pagePath];
		}

		$pageFullPath = $pagePath . $this->fileExtension;

		try {
			$fs = new Files(PAGES);

			if(!$fs->exists($pageFullPath)) {
				return false;
			}


			// Anonymous function to use serializer engine
			$serializer = $this->serializer;
			$fn = function($data) use ($pagePath, $serializer) {
				return $serializer->unserialize($pagePath, $data);
			};

			$page = $fs->read($pageFullPath, $fn);
			$this->pages[$pagePath] = $page;

			return $page;

		} catch(IOException $e) {
			exceptionHandler($e);
		}
	}

	/**
	 * Save page object in "pagePath" file
	 * @param Page $page Page object
	 * @param string $pagePath Page filename
	 * @throws IOException If the page does not exists
	 * @return int|bool Return false if failed
	 */
	public function setPage(Page $page, $pagePath) {

		$pageFullPath = $pagePath . $this->fileExtension;

		try {
			$fs = new Files(PAGES);

			if(!$fs->exists($pageFullPath)) {
				// TODO create page
			}

			$data = $this->serializer->serialize($page);

			$res = $fs->write($pageFullPath, $data);

			if($res !== false) {
				$this->pages[$pagePath] = $page;
			}

			return $res;

		} catch(IOException $e) {
			exceptionHandler($e);
		}
	}

	/**
	 * Delete a page
	 * @param string $pagePath
	 * @throws IOException If the page does not exists
	 * @return boolean If page is deleted
	 */
	public function deletePage($pagePath) {
		$pageFullPath = $pagePath . $this->fileExtension;

		try {
			$fs = new Files(PAGES);
			return $fs->delete($pageFullPath);
		} catch(IOException $e) {
			exceptionHandler($e);
		}
	}

	/**
	 * Get all pages
	 * @return array<Page> Return an array of 'Page'
	 */
	public function getPages() {
		$pages = array();

		$pagesArr = $this->listPages();

		foreach ($pagesArr as $pagePath) {
			$pages[] = $this->getPage($pagePath);
		}

		// Sort pages
		$sortType = Config::typeOfSort();
		$sortField = Config::sortField();

		if(is_integer($sortType)) {
			$sortType = SORT_ASC;
		}

		$this->arraySortByField($pages, $sortField, $sortType);

		return $pages;
	}

	/**
	 * Sort array of objects from a specific field
	 * @param array<?> &$arr An array of objects
	 * @param string $field Field name to sort
	 * @param integer $sortType Sorting type (flag)
	 * @return boolean
	 */
	private function arraySortByField(array &$arr, $field, $sortType = SORT_ASC) {
		$sortFields = array();
		foreach ($arr as $key => $obj) {
			if(isset($obj->$field)) {
				$sortFields[$key] = $obj->$field;
			} else {
				$sortFields[$key] = array();
			}
		}

		return array_multisort($sortFields, $sortType, $arr);
	}

	/**
	 * List pages and get an array of pages paths
	 * @throws IOException If the pages directory does not exists
	 * @return array Array of pages paths
	 */
	public function listPages() {
		$pages = array();
		$that = &$this;

		try {
			$fs = new Files(PAGES);

			$fs->getFilesList('', false, function($file, $dir = '') use (&$pages, &$that)
			{
				// If files have the right extension and file not secret
				// (does not begin with '_')
				$ext = '.' . Config::fileExtension();
				$len = - strlen($ext);
				if($file[0] !== '_' && substr($file, $len) === $ext) {
					if($dir !== '') {
						$dir = trim($dir, '/\\') . '/';
					}

					// If directory is not secret
					if(strlen($dir) > 0 && $dir[0] !== '_') {
						$pagePath = $dir . basename($file, $ext);
						$pages[] = $pagePath;
					}

				}
			});

			return $pages;
		} catch(IOException $e) {
			exceptionHandler($e);
		}
	}

	/**
	 * Get current URI (without /index.php)
	 * @return string
	 */
	public static function getURI() {
		//TODO stock URI in field (same for relativeURI)
		$scriptName = $_SERVER['SCRIPT_NAME'];

		if(Config::get('URLRewriting')) {
			$scriptName = dirname($scriptName);
		}

		return implode(explode($scriptName, $_SERVER['REQUEST_URI'], 2));
	}

	/**
	 * Get relative URI from the root
	 * @return string
	 */
	public static function getRelativeURIToRoot() {
		$postUrl = static::getURI();
		$postUrl = str_replace(array('//', '\\'), '/', $postUrl);
		$slashNb = substr_count($postUrl, '/');

		// Add a '../' to URL if there is not URL rewriting
		if(!Config::get('URLRewriting')) {
			++$slashNb;
		}

		return str_repeat('../', max($slashNb - 1, 0));
	}

	/**
	 * Get request method
	 * @return string
	 */
	public static function getMethod() {
		return $_SERVER['REQUEST_METHOD'];
	}

	/**
	 * Set Parvula pages serializer
	 * @param PageSerializerInterface $customSerializer
	 * @return void
	 */
	public function setSerializer(PageSerializerInterface $customSerializer = null) {
		if($customSerializer === null) {
			$defaultSer = Config::defaultPageSerializer();
			$customSerializer = new $defaultSer;
		}

		$this->serializer = $customSerializer;
	}

	/**
	 * Get user config
	 * @return array
	 */
	public static function getUserConfig() {
		try {
			$confFs = new Files(DATA);
			$config = $confFs->read(Config::get('userConfig') . '.' . Config::get('fileExtension'), 'parseConfigData');
		} catch(IOException $e) {
			exceptionHandler($e);
		}

		return $config;
	}

	/**
	 * PSR-0 autoloader to run Parvula without composer
	 * @param string $className
	 * @return
	 */
	public static function autoload($className) {
		$className = ltrim($className, '\\');
		$fileName  = '';
		$namespace = '';
		if ($lastNsPos = strrpos($className, '\\')) {
			$namespace = substr($className, 0, $lastNsPos);
			$className = substr($className, $lastNsPos + 1);
			$fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
		}
		$fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

		if (file_exists($fileName) || file_exists($fileName = VENDOR . $fileName)) {
			require $fileName;
		}
	}

	/**
	 * Register Parvula autoloader
	 * @return
	 */
	public static function registerAutoloader() {
		spl_autoload_register(__NAMESPACE__ . "\\Parvula::autoload");
	}

}
