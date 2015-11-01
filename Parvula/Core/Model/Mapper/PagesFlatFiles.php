<?php

namespace Parvula\Core\Model\Mapper;

use Parvula\Core\Page;
use Parvula\Core\FilesSystem as Files;
use Parvula\Core\Exception\IOException;
use Parvula\Core\Exception\PageException;
use Parvula\Core\ContentParser\ContentParserInterface;
use Parvula\Core\PageSerializer\PageSerializerInterface;

/**
 * Flat file pages
 *
 * @package Parvula
 * @version 0.5.0
 * @since 0.5.0
 * @author Fabien Sa
 * @license MIT License
 */
class PagesFlatFiles extends Pages
{
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
	 *
	 * @param ContentParserInterface $contentParser
	 * @param PageSerializerInterface $pageSerializer
	 * @param string $fileExtension
	 */
	function __construct(ContentParserInterface $contentParser,
		PageSerializerInterface $pageSerializer, $fileExtension) {
		parent::__construct($contentParser);

		$this->fileExtension =  '.' . ltrim($fileExtension, '.');

		$this->setSerializer(new $pageSerializer);
	}

	/**
	 * Get a page object in html string
	 *
	 * @param string $pageUID Page unique ID
	 * @param boolean ($eval) Evaluate PHP
	 * @throws IOException If the page does not exists
	 * @return Page|bool Return the selected page if exists, false if not
	 */
	public function read($pageUID, $parseContent = true, $eval = false) {

		// If page was already loaded, return page
		if (isset($this->pages[$pageUID])) {
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
			$parser = null;
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
	 * @param string $pageUID Page unique ID
	 * @param Page $page Page object
	 * @throws IOException If the page does not exists
	 * @return string|bool Return true if ok, string if error
	 */
	public function create($page) {

		$pageFullPath = $page->slug . $this->fileExtension;

		try {
			$fs = new Files(PAGES);

			if ($fs->exists($pageFullPath)) {
				// TODO create page
				return false;
			}

			// if (!$fs->isWritable()) {
				// TODO
				// throw new PageException('Page is not writable');
			// }

			$data = $this->serializer->serialize($page);

			$fs->write($pageFullPath, $data);

		} catch(IOException $e) {
			throw new PageException('Error Processing Request');
		}

		$this->pages[$page->slug] = $page;

		// return true;
	}

	/**
	 * Update page object
	 *
	 * @param Page $page Page object
	 * @param string $pageUID Page unique ID
	 * @throws IOException If the page does not exists
	 * @return string|bool Return true if ok, string if error
	 */
	public function update($pageUID, $page) {

		$fs = new Files(PAGES);
		$pageFile = $pageUID . $this->fileExtension;
		if (!$fs->exists($pageFile)) {
			throw new PageException('Page `' . $pageUID . '` does not exists');
		}

		if (!isset($page->title, $page->slug)) {
			throw new PageException('Page is not valid. Must have at lease a `title` and a `slug`');
		}

		// New slug, need to rename
		if ($pageUID !== $page->slug) {
			$pageFileNew = $page->slug . $this->fileExtension;

			if ($fs->exists($pageFileNew)) {
				throw new PageException('Cannot rename, page ' . $page->slug . ' already exists');
			}

			$fs->rename($pageFile, $pageFileNew);
			$pageFile = $pageFileNew;
		}

		$data = $this->serializer->serialize($page);

		$fs->write($pageFile, $data);

		$this->pages[$page->slug] = $page;
	}

	public function patch($pageUID, array $page) {

		$fs = new Files(PAGES);
		$pageFile = $pageUID . $this->fileExtension;
		if (!$fs->exists($pageFile)) {
			throw new PageException('Page `' . $pageUID . '` does not exists');
		}

		$pageOld = $this->read($pageUID, false);

		foreach ($page as $key => $value) {
			if ($value === null) {
				if (isset($pageOld->{$key})) {
					unset($pageOld->{$key});
				}
			}
			else if (!empty($value)) {
				$pageOld->{$key} = $value;
			}
		}

		$page = Page::pageFactory((array) $pageOld);

		return $this->update($pageUID, $page);
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
	 * Index pages and get an array of pages slug
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
			if ($pagesPath === null) {
				$pagesPath = PAGES;
			}

			$fs = new Files($pagesPath);
			$fs->index('', false, function($file, $dir = '') use (&$pages, &$that, $listHidden)
			{
				// If files have the right extension and file not secret
				// (does not begin with '_')
				$len = - strlen($that->fileExtension);
				if (($listHidden || $file[0] !== '_') && substr($file, $len) === $that->fileExtension) {
					if ($dir !== '') {
						$dir = trim($dir, '/\\') . '/';
					}

					// If directory is not secret (or root)
					if ($listHidden || $dir === '' || $dir[0] !== '_') {
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

		if ($path !== null) {
			$path = PAGES . $path;
		}

		$pagesIndex = $this->index(true, $path);

		foreach ($pagesIndex as $pageUID) {
			$that->pages[] = $this->read($pageUID);
		}

		return $that;
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

}
