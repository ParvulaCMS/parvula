<?php

namespace Parvula\Models;

use Closure;
use DateTime;
use Parvula\Models\Section;
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
	 * @var string Page's content
	 */
	public $content;

	/**
	 * @var array Page's sections (optional)
	 */
	public $sections;

	/**
	 * @var string Page's parent slug (optional)
	 */
	public $parent;

	/**
	 * @var PageRepository Children pages (optional)
	 */
	public $children;

	/**
	 * @var array Array of Closure
	 */
	protected $lazyFunctions;

	/**
	 * @var array
	 */
	protected $invisible = [
		'lazyFunctions'
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
					return new Section($section);
				}, $info['sections']);

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
		foreach ($this->toArray() as $key => $value) {
			if ($key !== 'sections' && $key !== 'content' && $key !== 'children') {
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
	 */
	public function addChild(Page $child) {
		if (!$this->children) {
			$this->children = [];
		}

		$this->children[] = $child;
	}

	/**
	 * Set page children
	 *
	 * @param array $children Array of Page
	 */
	public function setChildren(BaseRepository $children) {
		$this->children = $children;
	}

	/**
	 * Check if the page has a children
	 *
	 * @return bool
	 */
	public function hasChildren() {
		return (bool) $this->children;
	}

	/**
	 * Get page children
	 *
	 * @return array Array of Page
	 */
	public function getChildrenArray() {
		if (!empty($this->children)) {
			$cp = clone $this->children;
			return $cp->toArray();
		}
	}

	/**
	 * Get page children
	 *
	 * @deprecated see getChildren
	 */
	public function getPagesChildren() {
		if ($this->children) {
			return $this->children;
		}
	}

	/**
	 * Get page children
	 *
	 * @return PageRepository Pages
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
		return $this->getLazy('parent');
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
	 * Add a lazy function
	 *
	 * @param string $key
	 * @param Closure $closure
	 */
	public function addLazy($key, Closure $closure) {
		$this->lazyFunctions[$key] = $closure;
	}

	/**
	 * Resolve a given lazy function
	 *
	 * @param string $key
	 * @return mixed Return the result of the lazy function
	 */
	public function getLazy($key) {
		if ($this->hasLazy($key)) {
			return $this->lazyFunctions[$key]();
		}

		return false;
	}

	/**
	 * Check if the given lazy function exists
	 *
	 * @param  string  $key
	 * @return bool
	 */
	public function hasLazy($key) {
		return isset($this->lazyFunctions, $this->lazyFunctions[$key]);
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
