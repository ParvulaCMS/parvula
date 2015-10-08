<?php

namespace Parvula\Core\Model;

use Parvula\Core\Config;
use Parvula\Core\FilesSystem as Files;
use Parvula\Core\Exception\IOException;
use Parvula\Core\Parser\ContentParserInterface;
use Parvula\Core\Serializer\PageSerializerInterface;

/**
 * Page Manager
 *
 * @package Parvula
 * @version 0.5.0
 * @since 0.5.0
 * @author Fabien Sa
 * @license MIT License
 */
class Pages {

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

	private $parser;

	private $config;

	/**
	 * Constructor
	 * @param Config $config
	 */
	 function __construct(\Parvula\Core\Config $config) {
		$this->config = $config;
		$this->fileExtension =  '.' . $config->get('fileExtension');

		$pageSerializer = $config->get('pageSerializer');
		$this->setSerializer(new $pageSerializer);

		$contentParser = $config->get('contentParser');
		$this->setParser(new $contentParser);
	}

	/**
	 * Get a page object in html string
	 *
	 * @param string $pageUID Page unique ID
	 * @param boolean ($eval) Evaluate PHP
	 * @throws IOException If the page does not exists
	 * @return Page Return the selected page
	 */
	public function get($pageUID, $parseContent = true, $eval = false) {

		// If page was already loaded, return page
		if(isset($this->pages[$pageUID])) {
			return $this->pages[$pageUID];
		}

		$pageFullPath = $pageUID . $this->fileExtension;

		try {
			$fs = new Files(PAGES);

			if (!$fs->exists($pageFullPath)) {
				return false;
			}

			// Anonymous function to use serializer engine
			$serializer = $this->serializer;
			if ($parseContent) {
				$parser = $this->parser;
			}
			$fn = function($data) use ($pageUID, $serializer, $parser) {
				$page = $serializer->unserialize($data, ['slug' => trim($pageUID, '/')]);
				if ($parser !== null) {
					$page->content = $parser->parse($page->content);
				}
				return $page;
			};

			$page = $fs->read($pageFullPath, $fn, $eval);
			$this->pages[$pageUID] = $page;

			return $page;

		} catch(IOException $e) {
			exceptionHandler($e);
		}
	}

	/**
	 * Create page object in "pageUID" file
	 *
	 * @param Page $page Page object
	 * @param string $pageUID Page unique ID
	 * @throws IOException If the page does not exists
	 * @return string|bool Return true if ok, string if error
	 */
	public function set(Page $page, $pageUID) {

		$pageFullPath = $pageUID . $this->fileExtension;

		// try {
		$fs = new Files(PAGES);

		if(!$fs->exists($pageFullPath)) {
			// TODO create page
		}

		$data = $this->serializer->serialize($page);

		$fs->write($pageFullPath, $data);

		// } catch(IOException $e) {
			// exceptionHandler($e);
			// return $e->getMessage();
		// }

		$this->pages[$pageUID] = $page;

		// return true;
	}

	// TODO
	public function update(Page $page, $pageUID) {

		$pageOld = $this->get($pageUID);

		foreach ($page as $key => $value) {
			//TODO bug si on veut supprimer un variable...
			if(!empty($value)) {
				$pageOld->{$key} = $value;
			}
		}

		return $this->set($page, $pageUID);
	}

	/**
	 * Delete a page
	 *
	 * @param string $pageUID
	 * @throws IOException If the page does not exists
	 * @return boolean If page is deleted
	 */
	public function delete($pageUID) {
		$pageFullPath = $pageUID . $this->fileExtension;

		$fs = new Files(PAGES);
		return $fs->delete($pageFullPath);
	}

	/**
	 * Fetch all pages
	 * This method will read each pages
	 * If you want an array of Page use `toArray()` method
	 * Exemple: `$pages->all()->toArray();`
	 *
	 * @param string ($path) Pages in a specific sub path
	 * @return Pages
	 */
	public function all($path = null) {
		$that = clone $this;
		$that->pages = [];

		if($path !== null) {
			$path = PAGES . $path;
		}

		$pagesIndex = $this->index(true, $path);

		foreach ($pagesIndex as $pageUID) {
			$that->pages[] = $this->get($pageUID);
		}

		return $that;
	}

	/**
	 * Order pages
	 *
	 * @param integer ($sortType) Sort order
	 * @param string ($sortField) Sorting field
	 * @return Pages
	 */
	public function order($sortType = SORT_ASC, $sortField = 'slug') {
		$that = clone $this;

		if (!is_integer($sortType)) {
			$sortType = SORT_ASC;
		}

		$this->arraySortByField($that->pages, $sortField, $sortType);

		return $that;
	}

	/**
	 * Show visible pages
	 *
	 * @return Pages
	 */
	public function visible() {
		return $this->filter(function($page) {
			return !isset($page->hidden) || $page->hidden === 'false';
		});
	}

	/**
	 * Show hidden pages
	 *
	 * @return Pages
	 */
	public function hidden() {
		return $this->filter(function($page) {
			return isset($page->hidden) && $page->hidden !== 'false';
		});
	}

	/**
	 * Filter pages
	 *
	 * Exemple:
	 * ```
	 * // Will just keep pages with a title < 10 characters
	 * $pages->filter(function ($page) {
	 *     return strlen($page->title) < 10;
	 * })
	 * ```
	 *
	 * @param callable $fn
	 * @return Pages A clone of current object with filtered pages
	 */
	public function filter(callable $fn) {
		$that = clone $this;
		$that->pages = [];

		foreach ($this->pages as $page) {
			if($fn($page) === true) {
				$that->pages[] = $page;
			}
		}

		return $that;
	}

	/**
	 * Get all pages to array
	 *
	 * @return array<Page> Return an array of 'Page'
	 */
	public function toArray() {
		return $this->pages;
	}

	/**
	 * Sort array of objects from a specific field
	 *
	 * @param array<?> &$arr An array of objects
	 * @param string $field Field name to sort
	 * @param integer $sortType Sorting type (flag)
	 * @return boolean
	 */
	private function arraySortByField(array &$arr, $field, $sortType) {
		$sortFields = [];
		foreach ($arr as $key => $obj) {
			$sortFields[$key] = [];

			if(isset($obj->$field)) {
				$sortFields[$key] = $obj->$field;
			}
		}

		return array_multisort($sortFields, $sortType, $arr);
	}

	/**
	 * Index pages and get an array of pages paths
	 *
	 * @param boolean ($listHidden) List hidden files & folders
	 * @param string ($pagesPath) Pages path
	 * @throws IOException If the pages directory does not exists
	 * @return array Array of pages paths
	 */
	public function index($listHidden = false, $pagesPath = null) {
		$pages = [];
		$that = &$this;

		try {
			if($pagesPath === null) {
				$pagesPath = PAGES;
			}

			$fs = new Files($pagesPath);
			$fs->getFilesList('', false, function($file, $dir = '') use (&$pages, &$that, $listHidden)
			{
				// If files have the right extension and file not secret
				// (does not begin with '_')
				$len = - strlen($that->fileExtension);
				if(($listHidden || $file[0] !== '_') && substr($file, $len) === $that->fileExtension) {
					if($dir !== '') {
						$dir = trim($dir, '/\\') . '/';
					}

					// If directory is not secret (or root)
					if($listHidden || $dir === '' || $dir[0] !== '_') {
						$pagePath = $dir . basename($file, $that->fileExtension);
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
	 * Set Parvula pages serializer
	 *
	 * @param PageSerializerInterface $customSerializer
	 * @return void
	 */
	public function setSerializer(PageSerializerInterface $customSerializer) {
		$this->serializer = $customSerializer;
	}

	/**
	 * Set Parvula pages parser
	 *
	 * @param ContentParserInterface $customParser
	 * @return void
	 */
	public function setParser(ContentParserInterface $customParser = null) {
		$this->parser = $customParser;
	}

}
