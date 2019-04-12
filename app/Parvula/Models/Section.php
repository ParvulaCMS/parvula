<?php

namespace Parvula\Models;

use DateTime;
use Parvula\Exceptions\SectionException;

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
class Section extends Model
{

	/**
	 * @var array
	 */
	protected $invisible = ['_id'];

	/**
	 * Constructor
	 *
	 * @param array|object $info Section information
	 * @param \Closure|string $content (optional) Content
	 */
	public function __construct(array $info = [], $content = null) {
		if (func_num_args() === 1) {
			if (isset($info['content'])) {
				$this->content = $info['content'];
				unset($info['content']);
			}
		} else {
			$this->content = $content;
		}

		foreach ($info as $key => $val) {
			$this->$key = $val;
		}
	}

	/**
	 * Get section's metadata
	 *
	 * @return array
	 */
	public function getMeta(): array {
		$meta = [];
		foreach ($this->getVisibleFields() as $key => $value) {
			if ($key[0] !== '_' && $key !== 'content') {
				$meta[$key] = $value;
			}
		}
		return $meta;
	}

	/**
	 * Override `__toString` when print this object
	 *
	 * @return string
	 */
	public function __toString() {
		return json_encode($this->toArray());
	}
}
