<?php

namespace Parvula\Repositories\Mongo;

use IteratorIterator;
use MongoDB\Collection as MongoCollectionBase;
use Parvula\BaseRepository;
use Parvula\Models\Page;
use Parvula\Exceptions\IOException;
use Parvula\Exceptions\PageException;
use Parvula\PageRenderers\PageRendererInterface;
use Parvula\Collections\Collection;
use Parvula\Collections\MongoCollection;

/**
 * Mongo pages mapper
 *
 * @package Parvula
 * @version 0.8.0
 * @since 0.7.0
 * @author psych0pat
 * @license MIT License
 */
class PageRepositoryMongo extends BaseRepositoryMongo {

	protected $iter;

	protected $renderer;

	/**
	 * Constructor
	 *
	 * @param PageRendererInterface $pageRenderer Page renderer
	 * @param string $folder Pages folder
	 * @param string $fileExtension File extension
	 */
	public function __construct(PageRendererInterface $pageRenderer, MongoCollectionBase $collection) {
		// parent::__construct($pageRenderer);
		$this->renderer = $pageRenderer;
		$this->collection = $collection;
		// $this->manager = $manager;

		// Filter pages by visibility (hidden or visible)
		$visibility = function ($col, $visible) {
			// return $this->collection->find(['hidden' => true]);
			return $col->filter(function ($page) use ($visible) {
				if ($visible) {
					return !isset($page->hidden) || !$page->hidden || $page->hidden === 'false';
				}
				return isset($page->hidden) && ($page->hidden || $page->hidden !== 'false');
			});
		};
	}

	/**
	 * {@inheritDoc}
	 */
	protected function model() {
		return Page::class;
	}

	public function all($fields = []) {
		$col = new MongoCollection($this->collection, Page::class);

		return $col;
	}

	/**
	 * Get a page object in html string
	 *
	 * @param string $slug Page unique ID
	 * @throws IOException If the page does not exists
	 * @return Page|bool Return the selected page if exists, false if not
	 */
	public function find($slug) {
		$page = $this->findBy('slug', $slug);

		if (!$page) {
			return false;
		}

		unset($page->_id);

		return $this->renderer->parse($page);
	}

	/**
	 *
	 * @param string $slug Page unique ID
	 *
	 */
	private function exists($slug) {
		if (empty($this->collection->findOne(['slug' => $slug]))) {
			return false;
		}

		return true;
	}

	/**
	 * Create page object in "slug" file
	 *
	 * @param array $pageData Page data
	 * @throws IOException If the destination folder is not writable
	 * @throws PageException If the page does not exists
	 * @return bool
	 */
	public function create(array $pageData) {
		if (!isset($pageData['slug'])) {
			throw new IOException('Page cannot be created. It must have a slug');
		}

		$page = new Page($pageData);

		if ($this->exists($page->slug)) {
			return false;
		}

		try {
			return $this->collection->insertOne($page)->getInsertedCount() > 0 ? true : false;
		} catch (Exception $e) {
			throw new IOException('Page cannot be created');
		}
	}

	/**
	 * Update page object
	 *
	 * @param string $slug Page unique ID
	 * @param Page $pageData array
	 * @throws PageException If the page is not valid
	 * @throws PageException If the page already exists
	 * @throws PageException If the page does not exists
	 * @return bool Return true if page updated
	 */
	public function update($slug, array $pageData) {
		if (!$this->exists($slug)) {
			throw new PageException('Page `' . $slug . '` does not exists');
		}

		if (!isset($pageData['title'], $pageData['slug'])) {
			throw new PageException('Page not valid. Must have at least a `title` and a `slug`');
		}

		$page = new Page($pageData);

		try {
			$res = $this->collection->replaceOne(
				['slug' => $slug],
				$page
			);
			if ($res->getModifiedCount() > 0) {
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
	 * @param string $slug
	 * @param array $infos Patch infos
	 * @return boolean True if the page was correctly patched
	 */
	#public function patch($slug, array $infos) {
	#	if (!$this->exists($slug)) {
	#		throw new PageException('Page `' . $slug . '` does not exists');
	#	}

	#	$prototype = [];
	#	var_dump($infos);
	#	foreach ($infos as $key => $value) {
	#		if ($key === 'sections') {
	#			# > db.pages.update({slug: 'home', 'sections.name': 'blabla'}, {$set:{"sections.$.new_attr": 'TG MERCI'}})
	#			#foreach ($value as
	#				#$this->collection->updateOne(
	#				#	['slug': 'home'
	#				#);
	#		} else {
	#			$prototype[$key] = $value;
	#		}
	#	}

	#	try {
	#		$res = $this->collection->updateOne(
	#			['slug' => $slug],
	#			['$set' => $prototype]
	#		);

	#		if ($res->getModifiedCount() > 0) {
	#			return true;
	#		}
	#		return false;

	#	} catch (Exception $e) {
	#		return false;
	#	}
	#}

	/**
	 * Delete a page
	 *
	 * @param string $slug
	 * @throws IOException If the page does not exists
	 * @return boolean If page is deleted
	 */
	public function delete($slug) {
		$deleteResult = $this->collection->deleteOne(['slug' => $slug]);
		return $deleteResult->getDeletedCount() > 0;
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

		return new Collection($this->collection->distinct('slug', ['hidden' => ['$nin' => $exceptions]]));
	}
}
