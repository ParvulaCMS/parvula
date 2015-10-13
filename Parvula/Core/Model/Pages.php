<?php

namespace Parvula\Core\Model;

use Parvula\Core\Page;
use Parvula\Core\Parser\ContentParserInterface;

/**
 * Page Manager
 *
 * @package Parvula
 * @version 0.5.0
 * @since 0.5.0
 * @author Fabien Sa
 * @license MIT License
 */
abstract class Pages
{
	/**
	 * @var array<Page>
	 */
	protected $pages;

	/**
	 * @var ContentParserInterface
	 */
	protected $parser;

	/**
	 * Constructor
	 *
	 * @param ContentParserInterface $contentParser
	 */
	 function __construct(ContentParserInterface $contentParser = null) {
		$this->setParser($contentParser);
	}

	/**
	 * Get a page object with parsed content
	 *
	 * @param string $pageUID Page unique ID
	 * @throws IOException If the page does not exists
	 * @return Page Return the selected page
	 */
	public abstract function read($pageUID);
	// public abstract function get($pageUID, $parseContent = true, $eval = false);

	/**
	 * Create page object
	 *
	 * @param Page $page Page object
	 * @param string $pageUID Page unique ID
	 * @throws IOException If the page does not exists
	 * @return string|bool Return true if ok, string if error
	 */
	public abstract function create($pageUID, Page $page);

	// TODO
	/**
	 * Update page object
	 *
	 * @param Page $page Page object
	 * @param string $pageUID Page unique ID
	 * @throws IOException If the page does not exists
	 * @return string|bool Return true if ok, string if error
	 */
	public abstract function update($pageUID, Page $page);

	/**
	 * Delete a page
	 *
	 * @param string $pageUID
	 * @throws IOException If the page does not exists
	 * @return boolean If page is deleted
	 */
	public abstract function delete($pageUID);

	/**
	 * Index pages and get an array of pages slug
	 *
	 * @return array Array of pages paths
	 */
	public abstract function index();

	/**
	 * Fetch all pages
	 * This method will read each pages
	 * If you want an array of Page use `toArray()` method
	 * Exemple: `$pages->all()->toArray();`
	 *
	 * @param string ($path) Pages in a specific sub path
	 * @return Pages
	 */
	public abstract function all($path = null);

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
	 * Set Parvula pages parser
	 *
	 * @param ContentParserInterface $customParser
	 * @return void
	 */
	public function setParser(ContentParserInterface $customParser = null) {
		$this->parser = $customParser;
	}

}
