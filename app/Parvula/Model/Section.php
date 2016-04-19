<?php

namespace Parvula\Model;

use DateTime;
use Parvula\Exception\SectionException;

/**
 * This class represents a Section
 *
 * @package Parvula
 * @version 0.7.0
 * @since 0.7.0
 * @author Patrice Sa
 * @author Fabien Sa
 * @license MIT License
 */
class Section
{
	use ModelTrait;

	/**
	 * @var string Section's content
	 */
	public $content;

	public static function sectionFactory(array $infos) {
		$content = isset($infos['content']) ? $infos['content'] : '';
		unset($infos['content']);
		return new static($infos, $content);
	}

	/**
	 * Constructor
	 *
	 * @param array|object $meta Page's metas
	 * @param string $content (optional) Content
	 */
	public function __construct(array $meta = [], $content = '') {
		foreach ($meta as $key => $val) {
			$this->{$key} = $val;
		}

		$this->content = $content;
	}

	/**
	 * Get section's metadata
	 *
	 * @return array
	 */
	public function getMeta() {
		$meta = [];
		foreach ($this as $key => $value) {
			if ($key[0] !== '_' && $key !== 'content') {
				$meta[$key] = $value;
			}
		}
		return $meta;
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
