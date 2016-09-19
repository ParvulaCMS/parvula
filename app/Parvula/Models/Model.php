<?php

namespace Parvula\Models;

use Parvula\AccessorTrait;
use Parvula\ArrayableInterface;

/**
 * This abstract class represents a Model
 *
 * @package Parvula
 * @version 0.8.0
 * @since 0.8.0
 * @author Fabien Sa
 * @license MIT License
 */
abstract class Model implements ArrayableInterface {

	use AccessorTrait;

	/**
	 * Transform the instance fields to an array
	 *
	 * @return array|null Array of instance's fields
	 */
	public function toArray() {
		return $this->getVisibleFields();
	}

	/**
	 * Get all visible fields
	 *
	 * @return array|null Visible fields
	 */
	private function getVisibleFields() {
		$fields = $this->getAllFields();

		if (isset($this->visible)) {
			return array_intersect_key($fields, array_flip($this->visible));
		}
		else if (isset($this->invisible)) {
			// Notice: It will also remove the 'invisible' field
			$this->invisible[] = 'invisible';
			return array_diff_key($fields, array_flip($this->invisible));
		}

		return;
	}

	/**
	 * Get all fields from an instance
	 *
	 * @return array
	 */
	private function getAllFields() {
		$acc = [];
		foreach ($this as $key => $value) {
			$acc[$key] = $value;
		}
		return $acc;
	}
}
