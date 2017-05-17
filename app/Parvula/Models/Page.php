<?php

namespace Parvula\Models;

use Closure;
use DateTime;
use Parvula\Models\Section;
use Parvula\Collections\Collection;
use Parvula\Repositories\BaseRepository; // TODO PageRepo
use Parvula\Exceptions\PageException;

/**
 * This class represents a Page
 *
 * @package Parvula
 * @version 0.8.0
 * @since 0.1.0
 * @author Fabien Sa
 * @license MIT License
 */
class Page extends Model
{
	/**
	 * @var string Page's title
	 */
	public $title;

	/**
	 * @var string Page's slug ([a-z0-9-_+/]+)
	 */
	public $slug;

	/**
	 * @var array Page's sections (optional)
	 */
	public $sections;

	/**
	 * @var Page Parent page (optional)
	 */
	public $parent;

	/**
	 * @var \Parvula\Collections\Collection Children pages (optional)
	 */
	public $children;

	/**
	 * @var array
	 */
	protected $invisible = [
		'_id', 'parent', 'lazy'
	];

	/**
	 * Create a new page from the given array
	 * The parameter $info must contain at least `title` and `slug` fields.
	 * The `slug` field need to be normalized (a-z0-9-_+/).
	 *
	 * @param array $info Array with page information (must contain `title` and `slug` fields)
	 * @param string $content (optional) Content
	 * @param array $sections (optional) array of Section
     * @throws PageException if `$pageInfo` does not have field `title` and `slug`
	 * @throws PageException if `$pageInfo[slug]` value is not normalized
	 */
	public function __construct(array $info, $content = '', array $sections = []) {
		// Check if required meta informations are available
		if (empty($info['title']) || empty($info['slug'])) {
			throw new PageException('Page cannot be created, meta must contains `title` and `slug` keys');
		}

		if (!preg_match('/^[a-z0-9\-_\+\/]+$/', $info['slug'])) {
			throw new PageException('Page (' . htmlspecialchars($info['slug']) .
				') cannot be created, the slug must be normalized (with: a-z0-9-_+/)');
		}

		// Add children as a collection of child
		if (isset($info['children'])) {
			$this->children = new Collection($info['children'], Page::class);
			unset($info['children']);
		} else {
			$this->children = new Collection();
		}

		foreach ($info as $key => $value) {
			// object with private fields casted to array will have keys prepended with \0
			// https://php.net/manual/en/language.types.array.php#language.types.array.casting
			if (!is_null($value) && $key[0] !== "\0") {
				$this->{$key} = $value;
			}
		}

		if (func_num_args() === 1) {
			$this->content = '';
			if (isset($info['content'])) {
				$this->content = $info['content'];
				unset($info['content']);
			}

			$this->sections = [];
			if (isset($info['sections'])) {
				$this->sections = array_map(function ($section) {
					return new Section((array) $section);
				}, (array) $info['sections']);

				unset($info['sections']);
			}
		} else {
			$this->content = $content;
			$this->sections = $sections;
		}
	}

	/**
	 * @deprecated deprecated since version 0.7.0
	 * @see equal
	 */
	public function is(Page $page2) {
		return $this->slug === $page2->slug;
	}

	/**
	 * Compare this page with an other (compare the slug)
	 *
	 * @param Page $page2
	 * @return boolean True if both pages are the same
	 */
	public function equals(Page $page2) {
		return $this->slug === $page2->slug;
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
		foreach ($this->getVisibleFields() as $key => $value) {
			if ($value !== null && $key !== 'sections' && $key !== 'content' && $key !== 'children') {
				$meta[$key] = $value;
			}
		}
		return $meta;
	}

	/**
	 * Get sections
	 *
	 * @return array array of Section
	 */
	public function getSections() {
		return $this->sections;
	}

	/**
	 * Get section
	 *
	 * @param  string $name Section name
	 * @return Section|bool False if no section
	 */
	public function getSection($name) {
		foreach ($this->sections as $section) {
			if ($section->name === $name) {
				return $section;
			}
		}
		return false;
	}

	/**
	 * Add page child
	 *
	 * @param Page $child
	 * @return Page
	 */
	public function addChild(Page $child) {
		$this->children = $this->children->add($child);

		return $this;
	}

	/**
	 * Check if the page has a children
	 *
	 * @return bool
	 */
	public function hasChildren() {
		return !$this->children->isEmpty();
	}

	/**
	 * Get page children
	 *
	 * @return \Parvula\Collections\Collection Pages
	 */
	public function getChildren() {
		if ($this->children) {
			return $this->children;
		}
	}

	/**
	 * Get page parent
	 *
	 * @return Page Parent Page
	 */
	public function getParent() {
		return $this->parent;
	}

	/**
	 * Check if the page has a parent
	 *
	 * @return bool
	 */
	public function hasParent() {
		return (bool) $this->parent;
	}

	/**
	 * Get php DateTime object with Page date
	 * More info https://php.net/manual/en/class.datetime.php
	 *
	 * @return DateTime
	 */
	public function getDateTime() {
		if (!$this->date) {
			return false;
		}
		return new DateTime($this->date);
	}

	/**
	 * Breadcrumb of parents
	 * The first element is the oldest parent, the last one, the adjacent
	 *
	 * @return array Array of Page
	 */
	public function getBreadcrumb() {
		$pages = [];
		$page = $this;
		while ($page = $page->getParent()) {
			$pages[] = $page;
		}
		return array_reverse($pages);
	}

	/**
	 * {@inheritDoc}
	 */
	public function toArray() {
		$arr = parent::toArray();

		// Convert each section to array
		$arr['sections'] = array_map(function ($section) {
			return $section->toArray();
		}, $this->sections);

		return $arr;
	}

	/**
	 * Override `tostring` when print this object
	 *
	 * @return string
	 */
	public function __tostring() {
		return json_encode($this->toArray());
	}
}
