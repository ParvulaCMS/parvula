<?php

namespace Parvula;

/**
 * Helper for instances.
 *
 * @version 0.7.0
 * @since 0.7.0
 * @license MIT License
 */
trait AccessorTrait
{
	/**
	 * Get given field of an object if exists and not empty.
	 *
	 * @param  string $field
	 * @param  mixed  $default (optional)
	 * @return mixed  Field of an object, $default if nothing
	 */
	public function get(string $field, $default = '') {
		if (isset($this->{$field}) && !empty($this->{$field})) {
			return $this->{$field};
		}

		return $default;
	}

	/**
	 * Check if the given object has a specific field.
	 *
	 * @param  string $field
	 * @return bool
	 */
	public function has($field): bool {
		return isset($this->{$field});
	}
}
