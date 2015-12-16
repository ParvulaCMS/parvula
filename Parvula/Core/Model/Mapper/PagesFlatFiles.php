<?php

namespace Parvula\Core\Model\Mapper;

use Parvula\Core\Model\Page;
use Parvula\Core\FilesSystem as Files;
use Parvula\Core\Exception\IOException;
use Parvula\Core\Exception\PageException;
use Parvula\Core\PageRenderer\PageRendererInterface;

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
	 * @var string Pages folder
	 */
	private $folder;

	/**
	 * Constructor
	 *
	 * @param PageRendererInterface $pageRenderer Page renderer
	 * @param string $folder Pages folder
	 * @param string $fileExtension File extension
	 */
	function __construct(PageRendererInterface $pageRenderer, $folder, $fileExtension) {
		parent::__construct($pageRenderer);

		$this->folder = $folder;
		$this->fileExtension =  '.' . ltrim($fileExtension, '.');
	}

	/**
	 * Get a page object in html string
	 *
	 * @param string $pageUID Page unique ID
	 * @param boolean ($eval) Evaluate PHP
	 * @throws IOException If the page does not exists
	 * @return Page|bool Return the selected page if exists, false if not
	 */
	public function read($pageUID, $eval = false) {

		// If page was already loaded, return page
		if (isset($this->pages[$pageUID])) {
			return $this->pages[$pageUID];
		}

		$pageFullPath = $pageUID . $this->fileExtension;

		try {
			$fs = new Files($this->folder);

			if (!$fs->exists($pageFullPath)) {
				return false;
			}

			// Anonymous function to use renderer engine
			$renderer = $this->renderer;
			$fn = function($data) use ($pageUID, $renderer) {
				$pageUID = trim($pageUID, '/');

				// Create the title from the filename
				if (strpos($pageUID, '/') !== false) {
					$pageTitle = explode('/', $pageUID);
					$pageTitle = end($pageTitle);
				} else {
					$pageTitle = $pageUID;
				}

				$opt = [
					'slug' => $pageUID,
					'title' => ucfirst(strtr($pageTitle, '-', ' ')) // lisp-case to Normal case
				];

				$pageUID[0] === '_' ? $opt += ['hidden' => true] : null;
				$pageUID[0] === '.' ? $opt += ['secret' => true] : null;

				return $renderer->parse($data, $opt);
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
	 * @return bool
	 */
	public function create($page) {

		$pageFullPath = $page->slug . $this->fileExtension;

		try {
			$fs = new Files($this->folder);

			if ($fs->exists($pageFullPath)) {
				// TODO create page
				return false;
			}

			//TODO
			// if (!$fs->isWritable()) { // folder ! not file
			// 	throw new IOException('Page destination folder is not writable');
			// 	// throw new IOException('Page `' . strip_tags($page->slug) . '` is not writable');
			// }

			$data = $this->renderer->render($page);

			if (!$fs->write($pageFullPath, $data)) {
				return false;
			}

		} catch(IOException $e) {
			throw new PageException('Error Processing Request');
		}

		$this->pages[$page->slug] = $page;

		return true;
	}

	/**
	 * Update page object
	 *
	 * @param Page $page Page object
	 * @param string $pageUID Page unique ID
	 * @throws PageException If the page is not valid
	 * @throws PageException If the page already exists
	 * @throws PageException If the page does not exists
	 * @return bool Return true if page updated
	 */
	public function update($pageUID, $page) {

		$fs = new Files($this->folder);
		$pageFile = $pageUID . $this->fileExtension;
		if (!$fs->exists($pageFile)) {
			throw new PageException('Page `' . $pageUID . '` does not exists');
		}

		if (!isset($page->title, $page->slug)) {
			throw new PageException('Page not valid. Must have at lease a `title` and a `slug`');
		}

		// New slug, need to rename
		if ($pageUID !== $page->slug) {
			$pageFileNew = $page->slug . $this->fileExtension;

			if ($fs->exists($pageFileNew)) {
				throw new PageException('Cannot rename, page `' . $page->slug . '` already exists');
			}

			$fs->rename($pageFile, $pageFileNew);
			$pageFile = $pageFileNew;
		}

		$data = $this->renderer->render($page);

		$fs->write($pageFile, $data);

		$this->pages[$page->slug] = $page;

		return true;
	}

	/**
	 * Patch page
	 *
	 * @param string $pageUID
	 * @param array $page
	 * @return boolean True if the page was correctly patched
	 */
	public function patch($pageUID, array $page) {

		$fs = new Files($this->folder);
		$pageFile = $pageUID . $this->fileExtension;
		if (!$fs->exists($pageFile)) {
			throw new PageException('Page `' . $pageUID . '` does not exists');
		}

		$pageOld = (array) $this->read($pageUID, false);

		$pageOld = array_replace_recursive($pageOld, $page);

		$page = Page::pageFactory($pageOld);

		return $this->update($pageUID, $page);
	}

	// TODO ?
	public function head($pageUID) {

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

		$fs = new Files($this->folder);
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
				$pagesPath = $this->folder;
			}

			$fs = new Files($pagesPath);
			$fs->index('', false, function($file, $dir = '') use (&$pages, &$that, $listHidden)
			{
				// If files have the right extension are not hidden (does not begin with '_')
				$len = - strlen($that->fileExtension);
				if (($listHidden || $file[0] !== '_') && substr($file, $len) === $that->fileExtension) {
					if ($dir !== '') {
						$dir = trim($dir, '/\\') . '/';
					}

					// If directory is not hidden (or root)
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
			$path = $this->folder . $path;
		}

		$pagesIndex = $this->index(true, $path);

		foreach ($pagesIndex as $pageUID) {
			$that->pages[] = $this->read($pageUID);
		}

		return $that;
	}

}
