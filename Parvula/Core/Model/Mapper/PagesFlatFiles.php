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
	 * @var string Pages folder
	 */
	private $folder;

	/**
	 * @var string Default file name if the slug point to a folder
	 */
	private $folderDefaultFile = '/index';

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
	public function read($pageUID, $parse = true, $eval = false) {
		// If page was already loaded, return page
		if (isset($this->pages[$pageUID])) {
			return $this->pages[$pageUID];
		}

		$pageFullPath = $pageUID . $this->fileExtension;

		try {
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
			$fn = function (\SplFileInfo $fileInfo, $data) use ($pageUID, $renderer, $parse) {
				$pageUID = trim($pageUID, '/');

				// Create the title from the filename
				if (strpos($pageUID, '/') !== false) {
					$pageTitle = explode('/', $pageUID);
					$pageTitle = end($pageTitle);
				} else {
					$pageTitle = $pageUID;
				}

				$opts = [
					'slug' => $pageUID,
					'title' => ucfirst(strtr($pageTitle, '-', ' ')), // lisp-case to Normal case
					'date' => '@' . $fileInfo->getMTime()
				];

				$pageUID[0] === '_' ? $opts += ['hidden' => true] : null;
				$pageUID[0] === '.' ? $opts += ['secret' => true] : null;

				return $renderer->parse($data, $opts, $parse);
			};

			$page = $fs->read($pageFullPath, $fn, $eval);
			$this->pages[$pageUID] = $page;

			return $page;

		} catch (IOException $e) {
			exceptionHandler($e);
		}
	}

	/**
	 * Create page object in "pageUID" file
	 *
	 * @param Page $page Page object
	 * @throws PageException If the page does not exists
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

		} catch (IOException $e) {
			throw new PageException('Error Processing Request');
		}

		$this->pages[$page->slug] = $page;

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

		$this->pages[$page->slug] = $page;

		return true;
	}

	/**
	 * Patch page
	 *
	 * @param string $pageUID
	 * @param array $infos Patch infos
	 * @return boolean True if the page was correctly patched
	 */
	public function patch ($pageUID, array $infos) {
		$fs = new Files($this->folder);
		$pageFile = $pageUID . $this->fileExtension;
		if (!$fs->exists($pageFile)) {
			throw new PageException('Page `' . $pageUID . '` does not exists');
		}

		/**
		 * Patch helper
		 * @param  array $struct Array to patch
		 * @param  array $patch Patch to apply
		 * @return array Patched array
		 */
		function patchHelper($struct, $patch) {
			foreach ($patch as $key => $value) {
				if (is_array($value)) {
					// current value is an array, nothing to replace, use recursion
					if ((object) $struct === $struct) {
						$value = patchHelper($struct->$key, $value);
					}
					else if ((array) $struct === $struct) {
						$value = patchHelper($struct[$key], $value);
					}
				}

				if ((array) $struct === $struct) {
					if ($value === null || $value === '') {
						unset($struct[$key]);
					} else {
						$struct[$key] = $value;
					}
				}
				else if ((object) $struct === $struct) {
					if ($value === null || $value === '') {
						unset($struct->$key);
					} else {
						$struct->$key = $value;
					}
				}
			}

			return $struct;
		}

		$page = $this->read($pageUID, false);
		$pagePatched = patchHelper((array) $page, $infos);

		$infos = Page::pageFactory($pagePatched);

		return $this->update($pageUID, $infos);
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

		try {
			if ($pagesPath === null) {
				$pagesPath = $this->folder;
			}

			// Filter secret (.*) and hiddent files (_*)
			$filter = function ($current) use ($listHidden) {
				return ($listHidden || $current->getFilename()[0] !== '_')
					&& $current->getFilename()[0] !== '.';
			};

			$ext = $this->fileExtension;
			(new Files($pagesPath))->index('',
				function (\SplFileInfo $file, $dir) use (&$pages, $ext) {
				$currExt = '.' . $file->getExtension();

				// If files have the right extension
				if ($currExt === $ext) {
					if ($dir !== '') {
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
