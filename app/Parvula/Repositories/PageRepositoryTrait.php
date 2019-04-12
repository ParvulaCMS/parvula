<?php

namespace Parvula\Repositories;

use Iterator;
use Parvula\ArrayableInterface;
use Parvula\Models\Page;
use Parvula\PageRenderers\PageRendererInterface;

// abstract class PageRepository extends BaseRepository implements Iterator, ArrayableInterface {
trait PageRepositoryTrait
{
	// /**
	//  * @var array Cache (array<string, Page>)
	//  */
	// protected $cache;

	// /**
	//  * @var PageRendererInterface
	//  */
	// protected $renderer;

	// /**
	//  * Order pages
	//  *
	//  * @param integer ($sortType) Sort order
	//  * @param string ($sortField) Sorting field
	//  * @return PageRepository
	//  */
	// public function order($sortType = SORT_ASC, $sortField = 'slug') {
	// TODO remove
	// }

	/**
	 * Get all pages to array.
	 *
	 * @return array Return an array of 'Page' (array<Page>)
	 */
	public function toArray() {
		if (empty($this->data)) {
			return [];
		}

		$acc = [];
		foreach ($this->data as $page) {
			// TODO is is_array really needed ?
			if (isset($page->children) && !is_array($page->children)) {
				// We have to resolve children to arrays
				$page->children = $page->children->toArray();
			}
			$acc[] = $page;
		}

		return $acc;
	}

	/**
	 * Sort array of objects from a specific field.
	 *
	 * @param  array  &$arr     An array of objects (array<?>)
	 * @param  string $field    Field name to sort
	 * @param  int    $sortType Sorting type (flag)
	 * @return bool
	 */
	// TODO remove
	// private function arraySortByField(array &$arr, $field, $sortType) {
	// }

	/**
	 * Set page renderer.
	 *
	 * @param PageRendererInterface $customRenderer
	 */
	public function setRenderer(PageRendererInterface $customRenderer) {
		$this->renderer = $customRenderer;
	}
}
