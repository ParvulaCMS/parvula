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

	/**
	 * @var string
	 */
	private $id;
	// public $hidden;
	// public $index;

	/**
	 * Page factory, create a new page from an array
	 * @param array $pageInfo Array with page information (must contain `title` and `slug` fields)
	 * @throws PageException if $pageInfo does not have field `title` and `slug`
	 * @return Page The created Page
	 */
	public static function pageFactory(array $pageInfo) {
		$page = new static;

		if (empty($pageInfo['title']) || empty($pageInfo['slug'])) {
			throw new PageException('Page cannot be created, $pageInfo MUST contain `title` and `slug` fields');
		} else if (!isset($pageInfo['content'])) {
			$pageInfo['content'] = '';
		}

		foreach ($pageInfo as $field => $value) {
			$page->$field = $value;
		}

		$page->id = hash('crc32b', trim($page->slug, ' /'));

		return $page;
	}

	public function is(Page $page2) {
		return $this->id === $page2->id;
	}

	/**
	 * Override `tostring` when print this object
	 * @return string
	 */
	public function __tostring() {
		return json_encode($this);
	}
}
