<?php

namespace Parvula\Core\Model;

use Parvula\Core\FilesSystem as Files;
use Parvula\Core\Exception\IOException;
use Parvula\Core\Parser\ContentParserInterface;
use Parvula\Core\Serializer\PageSerializerInterface;

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
	 * @param Config $config
	 */
	function __construct(ContentParserInterface $contentParser,
		PageSerializerInterface $pageSerializer, $fileExtension) {
		parent::__construct($contentParser);

		$this->fileExtension =  '.' . ltrim($fileExtension, '.');

		// $pageSerializer = $config->get('pageSerializer');
		$this->setSerializer(new $pageSerializer);
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

}
