<?php

namespace Parvula\Core\Model\Mapper;

use Parvula\Core\Model\Page;
use Parvula\Core\ContentParser\ContentParserInterface;
use Parvula\Core\Model\CRUDInterface;

/**
 * Page Manager
 *
 * @package Parvula
 * @version 0.5.0
 * @since 0.5.0
 * @author Fabien Sa
 * @license MIT License
 */
abstract class Pages implements CRUDInterface
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
	 * @param ContentParserInterface $contentParser (optiona)
	 */
	 function __construct(ContentParserInterface $contentParser = null) {
		$this->setParser($contentParser);
	}

	public abstract function patch($pageUID, array $page);

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
	 * Example:
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
	 * @param ContentParserInterface $customParser (optional) Set a content parser, null if nothing
	 */
	public function setParser(ContentParserInterface $customParser = null) {
		$this->parser = $customParser;
	}

}
