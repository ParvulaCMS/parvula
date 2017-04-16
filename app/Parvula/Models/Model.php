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
	public function toArray($removeNull = false) {
		return $this->getVisibleFields($removeNull);
	}

	/**
	 * Get all visible fields
	 *
	 * @return array|null Visible fields
	 */
	private function getVisibleFields($removeNull = false) {
		$fields = $this->getAllFields();
		$res = [];

		if (isset($this->visible)) {
			$res = array_intersect_key($fields, array_flip($this->visible));
		} elseif (isset($this->invisible)) {
			// Notice: It will also remove the 'invisible' field
			$this->invisible[] = 'invisible';
			$res = array_diff_key($fields, array_flip($this->invisible));
		}

		if ($removeNull) {
			return array_filter($res, function ($value) {
				return $value !== null;
			});
		}

		return $res;
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

	/**
	 * Transform model
	 *
	 * @param callable $fun Callback function for the model
	 * @return mixed
	 */
	public function transform(callable $fun) {
		return $fun($this);
	}
}
