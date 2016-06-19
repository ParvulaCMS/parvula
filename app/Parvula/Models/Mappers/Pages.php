<?php

namespace Parvula\Models\Mappers;

use Iterator;
use Parvula\Models\Page;
use Parvula\ArrayableInterface;
use Parvula\Models\CRUDInterface;
use Parvula\PageRenderers\PageRendererInterface;

/**
 * Page Manager
 *
 * @package Parvula
 * @version 0.7.0
 * @since 0.5.0
 * @author Fabien Sa
 * @license MIT License
 */
abstract class Pages implements Iterator, CRUDInterface, ArrayableInterface
{
	/**
	 * @var array<Page>
	 */
	protected $pages;

	/**
	 * @var PageRendererInterface
	 */
	protected $renderer;

	/**
	 * Constructor
	 *
	 * @param ContentParserInterface $contentParser (optional)
	 */
	function __construct(PageRendererInterface $pageRenderer) {
		$this->setRenderer($pageRenderer);
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
	public function all($path = '') {
		$that = clone $this;
		$that->pages = [];

		$pagesIndex = $this->index(true, $path);

		foreach ($pagesIndex as $pageUID) {
			if (!isset($that->pages[$pageUID])) {
				$page = $this->read($pageUID);
				$that->pages[$page->slug] = $page;
			}
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
		return $this->visibility(true);
	}

	/**
	 * Show hidden pages
	 *
	 * @return Pages
	 */
	public function hidden() {
		return $this->visibility(false);
	}

	/**
	 * Filter pages by visibility (hidden or visible)
	 *
	 * @param  boolean $visible
	 * @return Pages
	 */
	public function visibility($visible) {
		return $this->filter(function ($page) use ($visible) {
			if ($visible) {
				return !isset($page->hidden) || !$page->hidden || $page->hidden === 'false';
			}
			return isset($page->hidden) && ($page->hidden || $page->hidden !== 'false');
		});
	}

	/**
	 * Show pages without a parent (the 'root' pages)
	 *
	 * @return Pages
	 */
	public function withoutParent() {
		return $this->filter(function (Page $page) {
			return (bool) !$page->get('parent');
		});
	}

	/**
	 * Show pages with a parent (the children pages)
	 *
	 * @return Pages
	 */
	public function withParent() {
		return $this->filter(function (Page $page) {
			return (bool) $page->get('parent');
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
			if ($fn($page) === true) {
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
		if (!$this->pages) {
			return [];
		}

		$acc = [];
		foreach ($this->pages as $page) {
			if (isset($page->children)) {
				// We have to resolve children to arrays
				$page->children = $page->children->toArray();
			}
			$acc[] = $page;
		}

		return $acc;
	}

	/**
	 * Return pages
	 *
	 * @return array Array of Page
	 */
	public function getPages() {
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

			if (isset($obj->$field)) {
				$sortFields[$key] = $obj->$field;
			}
		}

		return array_multisort($sortFields, $sortType, $arr);
	}

	/**
	 * Set page renderer
	 *
	 * @param PageRendererInterface $customRenderer
	 */
	public function setRenderer(PageRendererInterface $customRenderer) {
		$this->renderer = $customRenderer;
	}

	/**
	 * Rewind pages internal pointer
	 *
	 * @return mixed
	 */
	public function rewind() {
		return reset($this->pages);
	}

	/**
	 * Get current page
	 *
	 * @return Page
	 */
	public function current() {
		return current($this->pages);
	}

	/**
	 * Get current key
	 *
	 * @return string
	 */
	public function key() {
		return key($this->pages);
	}

	/**
	 * Get next page
	 *
	 * @return Page
	 */
	public function next() {
		return next($this->pages);
	}

	/**
	 * Check if current pages internal pointer is valid
	 *
	 * @return bool
	 */
	public function valid() {
		return key($this->pages) !== null;
	}

}
