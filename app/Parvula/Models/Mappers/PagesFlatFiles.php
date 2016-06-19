<?php

namespace Parvula\Models\Mappers;

use Parvula\Models\Page;
use Parvula\FilesSystem as Files;
use Parvula\Exceptions\IOException;
use Parvula\Exceptions\PageException;
use Parvula\PageRenderers\PageRendererInterface;

/**
 * Flat file pages
 *
 * @package Parvula
 * @version 0.7.0
 * @since 0.5.0
 * @author Fabien Sa
 * @license MIT License
 */
class PagesFlatFiles extends Pages
{
	/**
	 * @var string Pages folder
	 */
	private $folder;

	/**
	 * @var string Default file name if the slug point to a folder
	 */
	private $folderDefaultFile = '/index';

	private $arePagesFetched = false;

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
		$this->arePagesFetched = false;
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
		$this->fetchPages();
		$pageUID = trim($pageUID, '/');

		// If page was already loaded, return page
		if (isset($this->data[$pageUID])) {
			return $this->data[$pageUID];
		}

		$pageFullPath = $pageUID . $this->fileExtension;

		$fs = new Files($this->folder);

		if (!$fs->exists($pageFullPath)) {
			// Check if it can fallback to a default file in the folder
			$pageUID = $pageUID . $this->folderDefaultFile;
			if (!$fs->exists($pageFullPath = $pageUID . $this->fileExtension)) {
				return false;
			}
		}

		// Anonymous function to use renderer engine
		$renderer = $this->renderer;
		$fn = function (\SplFileInfo $fileInfo, $data) use ($pageUID, $renderer) {
			// Create the title from the filename
			if (strpos($pageUID, '/') !== false) {
				$pageUIDToken = explode('/', $pageUID);
				$pageTitle = array_pop($pageUIDToken);
				$parent = implode('/', $pageUIDToken);
			} else {
				$pageTitle = $pageUID;
			}

			$opts = [
				'slug' => $pageUID,
				'title' => ucfirst(strtr($pageTitle, '-', ' ')), // lisp-case to Normal case
				'date' => '@' . $fileInfo->getMTime()
			];

			isset($parent) ? $opts += ['parent' => $parent] : null;
			$pageUID[0] === '_' ? $opts += ['hidden' => true] : null;
			$pageUID[0] === '.' ? $opts += ['secret' => true] : null;

			return $renderer->parse($data, $opts);
		};

		$page = $fs->read($pageFullPath, $fn, $eval);
		$this->data[$pageUID] = $page;

		return $page;
	}

	/**
	 * Create page object in "pageUID" file
	 *
	 * @param Page $page Page object
	 * @throws IOException If the destination folder is not writable
	 * @throws PageException If the page does not exists
	 * @return bool
	 */
	public function create($page) {
		if (!isset($page->slug)) {
			throw new IOException('Page cannot be created. It must have a slug');
		}

		$fs = new Files($this->folder);

		if (!$fs->isWritable()) {
			throw new IOException('Page destination folder is not writable');
		}

		$slug = $page->slug;
		$pagePath = $slug . $this->fileExtension;

		try {
			if ($fs->exists($pagePath)) {
				return false;
			}

			$data = $this->renderer->render($page);

			if (!$fs->write($pagePath, $data)) {
				return false;
			}
		} catch (IOException $e) {
			throw new PageException('Page cannot be created');
		}

		$this->data[$slug] = $page;

		return true;
	}

	/**
	 * Update page object
	 *
	 * @param string $pageUID Page unique ID
	 * @param Page $page Page object
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
			throw new PageException('Page not valid. Must have at least a `title` and a `slug`');
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

		$this->data[$page->slug] = $page;

		return true;
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
	 * Index pages recursively and get an array (list) of pages slug
	 *
	 * @param boolean ($listHidden) List hidden files & folders
	 * @param string ($pagesPath) Pages path
	 * @throws IOException If the pages directory does not exists
	 * @return array Array of pages paths
	 */
	public function index($listHidden = false, $pagesPath = '') {
		$pages = [];

		try {
			// Filter secret (.*) and hiddent files (_*)
			$filter = function ($current) use ($listHidden) {
				return ($listHidden || $current->getFilename()[0] !== '_')
					&& $current->getFilename()[0] !== '.';
			};

			$ext = $this->fileExtension;
			(new Files($this->folder))->index($pagesPath,
				function (\SplFileInfo $file, $dir) use (&$pages, $ext) {
				$currExt = '.' . $file->getExtension();

				// If files have the right extension
				if ($currExt === $ext) {
					if ($dir) {
						$dir = trim($dir, '/\\') . '/';
					}
					$pages[] = $dir . $file->getBasename($currExt); // page path
				}
			}, $filter);

			return $pages;
		} catch (IOException $e) {
			exceptionHandler($e);
		}
	}

	/**
	 * Fetch pages
	 *
	 * @return array Array of all Page
	 */
	private function fetchPages() {
		if ($this->arePagesFetched) {
			return;
		}

		$pagesTmp = [];
		$pagesChildrenTmp = [];

		$pagesIndex = $this->index(true);

		$this->arePagesFetched = true;

		foreach ($pagesIndex as $pageUID) {
			$page = $this->read($pageUID);

			if (isset($page->parent)) {
				$parent = $page->parent;

				// Add lazy function to resolve parent when function is called
				$page->addLazy('parent', function () use ($parent) {
					return $this->read($parent);
				});

				if (!isset($pagesChildrenTmp[$parent])) {
					$pagesChildrenTmp[$parent] = [];
				}
				$pagesChildrenTmp[$parent][] = $page;
			}

			$pagesTmp[] = $page;
		}

		foreach ($pagesTmp as $page) {
			if (isset($pagesChildrenTmp[$page->slug])) {
				$pagesChildren = clone $this;
				$pagesChildren->data = $pagesChildrenTmp[$page->slug];
				$page->setChildren($pagesChildren);
			}
		}

		return $pagesTmp;
	}

}
