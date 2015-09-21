<?php

namespace Parvula\Core;

use Parvula\Core\Exception\PageException;

/**
 * Page type
 *
 * @package Parvula
 * @version 0.5.0
 * @since 0.1.0
 * @author Fabien Sa
 * @license MIT License
 */
class Page {

	/**
	 * @var string
	 */
	public $title;

	/**
	 * @var string
	 */
	public $slug;

	/**
	 * @var string
	 */
	public $content;

	// public $hidden;
	// public $index;

	/**
	 * Page factory, create a new page from an array
	 * The parameter $pageInfo must contain, at least, the `title` and `slug` fields.
	 * The `slug` need to be normalized (a-z0-9@-_+./).
	 *
	 * @param array $pageInfo Array with page information (must contain `title` and `slug` fields)
	 * @throws PageException if `$pageInfo` does not have field `title` and `slug`
	 * @throws PageException if `$pageInfo[slug]` value is not normalized
	 * @return Page The created Page
	 */
	public static function pageFactory(array $pageInfo) {
		$page = new static;

		// Check if $pageInfo array is complete
		if (empty($pageInfo['title']) || empty($pageInfo['slug'])) {
			throw new PageException('Page cannot be created, $pageInfo MUST contain `title` and `slug` fields');
		} else if (!isset($pageInfo['content'])) {
			$pageInfo['content'] = '';
		}

		if (!preg_match('/^[a-z0-9@-_\+\.\/]+$/', $pageInfo['slug'])) {
			throw new PageException('Page cannot be created, $pageInfo[slug] (' .
				htmlspecialchars($pageInfo['slug']) . ') value is not normalized');
		}

		foreach ($pageInfo as $field => $value) {
			$page->$field = $value;
		}

		return $page;
	}

	/**
	 * Compare this page with an other (compare the slug)
	 *
	 * @param Page $page2
	 * @return boolean True if both pages are the same
	 */
	public function is(Page $page2) {
		return $this->slug === $page2->slug;
	}

	/**
	 * Override `tostring` when print this object
	 *
	 * @return string
	 */
	public function __tostring() {
		return json_encode($this);
	}
}
