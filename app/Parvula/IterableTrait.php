<?php

namespace Parvula;

/**
 * Helper for Iterable classes
 * This trait will iterate on the `data` field
 *
 * @package Parvula
 * @version 0.7.0
 * @since 0.7.0
 * @author Fabien Sa
 * @license MIT License
 */
trait IterableTrait
{
	/**
	 * Rewind pages internal pointer
	 *
	 * @return mixed
	 */
	public function rewind() {
		return reset($this->data);
	}

	/**
	 * Get current page
	 *
	 * @return Page
	 */
	public function current() {
		return current($this->data);
	}

	/**
	 * Get current key
	 *
	 * @return string
	 */
	public function key() {
		return key($this->data);
	}

	/**
	 * Get next page
	 *
	 * @return Page
	 */
	public function next() {
		return next($this->data);
	}

	/**
	 * Check if current pages internal pointer is valid
	 *
	 * @return bool
	 */
	public function valid() {
		return key($this->data) !== null;
	}
}
