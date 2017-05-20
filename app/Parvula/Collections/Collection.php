<?php

namespace Parvula\Collections;

use Countable;
use JsonSerializable;
use IteratorAggregate;
use Parvula\ArrayableInterface;
use Parvula\Models\Model;
use MongoDB\Driver\Manager;
use Parvula\Models\Page;

class Collection implements Countable, IteratorAggregate, JsonSerializable {

	use CollectionTraits\PageCollectionTrait;

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

	/**
	 * Sort items by a specific field
	 *
	 * @param string $field
	 * @param boolean $ascending (optional) Default true
	 * @return \Parvula\Collections\Collection
	 */
	public function sortBy($field, $ascending = true) {
		$callback = function ($a, $b) use ($field) {
			if (isset($a->$field, $b->$field)) {
				return strcmp($a->$field, $b->$field);
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

	/**
	 * Filter items from the collection
	 *
	 * @param string $field
	 * @param array $values Values to filter (it will keep items with one of those values)
	 * @return \Parvula\Collections\Collection New collection
	 */
	public function filter($field, array $values) {
		$filteredItems = array_filter((array) $this->items, function ($item) use ($field, $values) {
			if (isset($item->$field)) {
				return in_array($item->$field, $values, true);
			}

			return in_array(null, $values, true);
		});

		return new static($filteredItems, $this->model);
	}

	/**
	 * Count the number of items in this collection
	 *
	 * @return int Number of items in the collection
	 */
	public function count() {
		return count($this->items);
	}

	/**
	 * Map each item of the collection
	 *
	 * @param callable $fun Callback function
	 * @return \Parvula\Collections\Collection New collection
	 */
	public function map(callable $fun) {
		$model = $this->model;

		$transformedItems = [];
		foreach ($this as $item) {
			// Do not re create the same model if it's already done
			if ($model !== null && !$item instanceof $model) {
				$transformedItems[] = $fun(new $model((array) $item));
			} else {
				$transformedItems[] = $fun($item);
			}
		}

		return new self($transformedItems, $model);
	}

	/**
	 * Check if the collection is empty
	 *
	 * @return bool
	 */
	public function isEmpty() {
		return $this->items === [];
	}

	/**
	 * Add item to the collection
	 *
	 * @param mixed $item
	 * @return \Parvula\Collections\Collection New collection
	 */
	public function add($item) {
		return new static(array_merge($this->items, [$item]), $this->model);
	}

	/**
	 * Transform the collection to an array
	 *
	 * @return array
	 */
    public function toArray($removeNull = false) {
		$accumulator = [];
		$model = $this->model;
		foreach ($this->all() as $item) {
			if ($model !== null) {
				$modelObject = new $model((array) $item);
				$accumulator[] = $modelObject instanceof ArrayableInterface ? $modelObject->toArray($removeNull) : $modelObject;
			} else {
				$accumulator[] = $item;
			}
		}

		return $accumulator;
    }

    /**
     * Convert the collection into json
     *
     * @return array
     */
    public function jsonSerialize() {
		$accumulator = [];
		foreach ($this->all() as $item) {
            if ($item instanceof JsonSerializable) {
                $accumulator[] = $item->jsonSerialize();
            } elseif ($item instanceof ArrayableInterface) {
                $accumulator[] = $item->toArray();
            } else {
                $accumulator[] = $item;
            }
		}

        return $accumulator;
    }

	/**
	 * Collection iterator
	 *
	 * @return \Traversable
	 */
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
	 * Get all items
	 *
	 * @return \Traversable Iterable items
	 */
	protected function all() {
		return $this->items;
	}

	/**
	 * @return \Parvula\Collections\Collection
	 */
	protected function clone() {
		return new static($this->items, $this->model);
	}
}
