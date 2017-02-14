<?php

namespace Parvula\Collections;

use Countable;
use IteratorAggregate;
use Parvula\ArrayableInterface;
use Parvula\Models\Model;
use MongoDB\Driver\Manager;
use Parvula\Models\Page;

class Collection implements Countable, IteratorAggregate {

	/**
	 * @var array
	 */
	protected $items;

	/**
	 * @var string
	 */
	protected $model;

	public function __construct($items = [], $model = null) {
		$this->items = $items;
		$this->model = $model;
	}

	public function sortBy($field, $ascending = true) {
		$callback = function ($a, $b) use ($field) {
			if (isset($a->$field, $b->$field)) {
				return $a->$field - $b->$field;
			}
			return 1;
		};

		if ($ascending) {
			$callbackAsc = function ($a, $b) use ($callback) {
				return $callback($a, $b);
			};
		} else {
			$callbackAsc = function ($a, $b) use ($callback) {
				return $callback($b, $a);
			};
		}

		 $sortedItems = $this->items;

		usort($sortedItems, $callbackAsc);

		return new static($sortedItems, $this->model);
	}

	public function filter($field, array $values) {
		// $new = array_filter($this->items, function ($item) use ($field, $values) {
		// 	return in_array($item[$field], [false]);
		// });

		// $new = array_filter($this->items, function ($item) use ($field, $values) {
		// 	return in_array($item, $values);
		// });

		$new = $this->items;

		return new static($new, $this->model);
	}

	public function withoutParent() {
		return $this->clone();
	}

	public function visible() {
		return $this->filter('hidden', [false, null]);
	}

	public function map(callable $cb) {
		foreach ($this->all() as $key => $item) {
			yield $item => $cb($item);
		}
	}

	/**
	 * Count the number of items in this collection
	 *
	 * @return int Number of items in the collection
	 */
	public function count() {
		return count($this->items);
	}

    public function toArray() {
		$accumulator = [];
		$model = $this->model;
		foreach ($this->all() as $item) {
			if ($model !== null) {
				$modelObject = new $model((array) $item);
				$accumulator[] = $modelObject instanceof ArrayableInterface ? $modelObject->toArray() : $modelObject;
			} else {
				$accumulator[] = $item;
			}
		}

		return $accumulator;
    }

	public function getIterator() {
		$model = $this->model;

		foreach ($this->all() as $item) {
			if ($model !== null) {
				yield new $model((array) $item);
			} else {
				yield $item;
			}
		}
	}

	/**
	 * @return Traversable Iterable items
	 */
	protected function all() {
		return $this->items;
	}

	/**
	 * @return Parvula\Collections\Collection
	 */
	protected function clone() {
		return new static($this->items, $this->model);
	}
}
