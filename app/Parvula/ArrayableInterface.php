<?php

namespace Parvula;

/**
 * Arrayable Interface
 *
 * @package Parvula
 * @version 0.7.0
 * @since 0.7.0
 * @author Fabien Sa
 * @license MIT License
 */
interface ArrayableInterface {

	/**
	 * Return the object as an array
	 *
	 * @return array
	 */
	public function toArray(): array;
}
