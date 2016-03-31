<?php

namespace Parvula\Core\Model\Mapper;

use Parvula\Core\Model\Page;
use Parvula\Core\Exception\IOException;
use Parvula\Core\Exception\PageException;
use Parvula\Core\PageRenderer\PageRendererInterface;

/**
 * Mongo pages mapper
 *
 * @package Parvula
 * @version 0.7.0
 * @since 0.7.0
 * @author psych0pat
 * @license MIT License
 */
class PagesMongo extends Pages
{

	/**
	 * Constructor
	 *
	 * @param PageRendererInterface $pageRenderer Page renderer
	 * @param string $folder Pages folder
	 * @param string $fileExtension File extension
	 * @param
	 */
	function __construct(PageRendererInterface $pageRenderer, $collection) {
		parent::__construct($pageRenderer);
		$this->collection = $collection;
	}

	/**
	 * Get a page object in html string
	 *
	 * @param string $pageUID Page unique ID
	 * @throws IOException If the page does not exists
	 * @return Page|bool Return the selected page if exists, false if not
	 */
	public function read($pageUID) {
		$page = $this->collection->findOne(['meta.slug' => $pageUID]);

		if (empty($page)) {
			return false;
		}

		return $this->renderer->parse($page);
	}

	/**
	 *
	 * @param string $pageUID Page unique ID
	 *
	 */
	private function exists($slug) {
		if ($this->read($slug)) {
			return true;
		}

		return false;
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

		if ($this->exists($page->slug)) {
			return false;
		}

		$page = [
			'meta' => $page->getMeta(),
			'content' => $page->content
		];

		try {
			return $this->collection->insertOne($page)->getInsertedCount() > 0 ? true : false;
		} catch (BadMethodCallException $e) {
			throw new IOException('Page cannot be created');
		}
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
		if (!$this->exists($pageUID)) {
			throw new PageException('Page `' . $pageUID . '` does not exists');
		}

		if (!isset($page->title, $page->slug)) {
			throw new PageException('Page not valid. Must have at least a `title` and a `slug`');
		}

		try {
			$res = $this->collection->replaceOne(
				['meta.slug' => $pageUID],
				[
					'content' => $page->content,
					'sections' => $page->sections,
					'meta' => $page->getMeta()
				]
			);

			if ($res->getModifiedCount()) {
				return true;
			}
			return false;

		} catch (Exception $e) {
			return false;
		}
	}

	/**
	 * Patch page
	 *
	 * @param string $pageUID
	 * @param array $infos Patch infos
	 * @return boolean True if the page was correctly patched
	 */
	public function patch($pageUID, array $infos) {
		if (!$this->exists($pageUID)) {
			throw new PageException('Page `' . $pageUID . '` does not exists');
		}

		$prototype = [];
		foreach ($infos as $key => $value) {
			if (in_array($key, ['content', 'sections'])) {
				$prototype[$key] = $value;
			} else {
				$prototype['meta.'.$key] = $value;
			}
		}

		try {
			$res = $this->collection->updateOne(
				['meta.slug' => $pageUID],
				['$set' => $prototype]
			);

			if ($res->getModifiedCount()) {
				return true;
			}
			return false;

		} catch (Exception $e) {
			return false;
		}
	}

	/**
	 * Delete a page
	 *
	 * @param string $pageUID
	 * @throws IOException If the page does not exists
	 * @return boolean If page is deleted
	 */
	public function delete($pageUID) {
		if (!is_null($this->collection->findOneAndDelete(['meta.slug' => $pageUID]))) {
			return true;
		}
		return false;
	}

	/**
	 * Index pages and get an array of pages slug
	 *
	 * @param boolean ($listHidden) List hidden files & folders
	 * @throws IOException If the pages directory does not exists
	 * @return array Array of pages paths
	 */
	public function index($listHidden = false) {
		$exceptions = [true];
		if ($listHidden) {
			$exceptions = [];
		}
		return $this->collection->distinct('meta.slug', ['meta.hidden' => ['$nin' => $exceptions]]);
	}
}
