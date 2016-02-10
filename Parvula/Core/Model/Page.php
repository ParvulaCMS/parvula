<?php

namespace Parvula\Core\Model;

use DateTime;
use Parvula\Core\Exception\PageException;

/**
 * This class represents a Page
 *
 * @package Parvula
 * @version 0.5.0
 * @since 0.1.0
 * @author Fabien Sa
 * @license MIT License
 */
class Page {

	/**
	 * @var string Page's title
	 */
	public $title;

	/**
	 * @var string Page's slug ([a-z0-9-_+/]+)
	 */
	public $slug;

	/**
	 * @var string Page's content
	 */
	public $content;

	/**
	 * @var stdClass Page's sections (optional)
	 */
	public $sections;

	/**
	 * Page factory, create a new page from an array
	 * The parameter $infos must contain at least `title` and `slug` fields.
	 * The `slug` need to be normalized (a-z0-9-_+/).
	 *
	 * @param array $infos Array with page informations (must contain `title` and `slug` fields)
	 * @throws PageException if `$pageInfo` does not have field `title` and `slug`
	 * @throws PageException if `$pageInfo[slug]` value is not normalized
	 * @return Page The created Page
	 */
	public static function pageFactory(array $infos) {
		$content = isset($infos['content']) ? $infos['content'] : '';
		$sections = isset($infos['sections']) ? $infos['sections'] : null;
		unset($infos['content']);
		unset($infos['sections']);

		return new static($infos, $content, $sections);
	}

	/**
	 * Constructor
	 *
	 * @param array $meta Metadata
	 * @param string $content (optional) Content
	 * @param object|array $sections (optional) Sections
	 */
	public function __construct(array $meta, $content = '', $sections = null) {
		// Check if required meta informations are available
		if (empty($meta['title']) || empty($meta['slug'])) {
			throw new PageException('Page cannot be created, $meta MUST contain `title` and `slug` keys');
		}

		if (!preg_match('/^[a-z0-9\-_\+\/]+$/', $meta['slug'])) {
			throw new PageException('Page cannot be created, $meta[slug] (' .
				htmlspecialchars($meta['slug']) . ') value is not normalized');
		}

		foreach ($meta as $key => $value) {
			$this->{$key} = $value;
		}

		// date can be set after the creation, by the database or file last edit
		if (!empty($this->date)) {
			$this->date = new DateTime($this->date);
		}
		$this->content = $content;
		$this->sections = (object) $sections;
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
	 * Get given field of page if exists and not empty
	 *
	 * @param  string $field
	 * @param  string $default (optional)
	 * @return string Field of page, $default if nothing
	 */
	public function get($field, $default = '') {
		if (isset($this->{$field}) && !empty($this->{$field})) {
			return $this->{$field};
		}
		return $default;
	}

	/**
	 * Check if the page has a specific field
	 *
	 * @param  string $field
	 * @return boolean
	 */
	public function has($field) {
		return isset($this->{$field});
	}

	/**
	 * Get page's content
	 *
	 * @return string
	 */
	public function getContent() {
		return $this->content;
	}

	/**
	 * Get page's metadata
	 *
	 * @return array
	 */
	public function getMeta() {
		$meta = [];
		foreach ($this as $key => $value) {
			if ($key !== 'sections' && $key !== 'content') {
				$meta[$key] = $value;
			}
		}
		return $meta;
	}

	/**
	 * Get sections
	 *
	 * @return object|bool False if no section
	 */
	public function getSections() {
		if (!isset($this->sections) || empty((array) $this->sections)) {
			return false;
		}
		return $this->sections;
	}

	/**
	 * Get section
	 *
	 * @param  string $name Section name
	 * @return string|bool False if no section
	 */
	public function getSection($name) {
		if (!isset($this->sections->{$name})) {
			return false;
		}
		return $this->sections->{$name};
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
