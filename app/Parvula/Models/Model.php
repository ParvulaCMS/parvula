<?php

namespace Parvula\Models;

use Closure;
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
		$arr = $this->getVisibleFields($removeNull);

		foreach ($arr as $key => $value) {
			if ($value instanceof Closure) {
				$value = $value();
				$arr[$key] = $value;
			}
			if ($value instanceof Model) {
				$arr[$key] = $value->toArray($removeNull);
			}
		}

		return $arr;
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

	/**
	 * @param string $name
	 * @return mixed
	 */
	public function __get($name) {
		if (isset($this->$name) && $this->$name instanceof Closure) {
			return ($this->$name)();
		}
	}

	/**
	 * @param string $name
	 * @param Closure $val
	 * @return mixed
	 */
	public function __set($name, $val) {
		return $this->$name = $val;
	}

	/**
	 * @param string $name
	 * @return mixed
	 */
    public function __isset($name) {
        return isset($this->$name) && $this->$name instanceof Closure;
    }

	/**
	 * @param string $name
	 * @return mixed
	 */
    public function __unset($name) {
        if (isset($this->$name) && $this->$name instanceof Closure) {
        	unset($this->$name);
		}
    }
}
