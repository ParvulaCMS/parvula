<?php

namespace Parvula;

/**
 * Helper for instances
 *
 * @package Parvula
 * @version 0.7.0
 * @since 0.7.0
 * @author Fabien Sa
 * @license MIT License
 */
trait AccessorTrait {

	/**
	 * Get given field of an object if exists and not empty
	 *
	 * @param  string $field
	 * @param  string $default (optional)
	 * @return string Field of an object, $default if nothing
	 */
	public function get($field, $default = '') {
		if (isset($this->{$field}) && !empty($this->{$field})) {
			return $this->{$field};
		}
		return $default;
	}

	/**
	 * Check if the given object has a specific field
	 *
	 * @param  string $field
	 * @return boolean
	 */
	public function has($field) {
		return isset($this->{$field});
	}
}
