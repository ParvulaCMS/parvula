<?php

namespace Parvula\Collections;

use Countable;
use JsonSerializable;
use IteratorAggregate;
use Parvula\ArrayableInterface;

class Collection implements ArrayableInterface, Countable, IteratorAggregate, JsonSerializable
{
	use Traits\PageCollectionTrait;

	/**
	 * @var array
	 */
	protected $items;

	/**
	 * @var string
	 */
	protected $model;

	public function __construct(array $items = [], ?string $model = null) {
		$this->items = $items;
		$this->model = $model;
	}

	/**
	 * Sort items by a specific field
	 *
	 * @param string $field
	 * @param boolean $ascending (optional) Default true
	 * @return static
	 */
	public function sortBy(string $field, ?bool $ascending = true) {
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
	 * @return static New collection
	 */
	public function filter(string $field, array $values = []) {
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
	public function count(): int {
		return count($this->items);
	}

	/**
	 * Map each item of the collection
	 *
	 * @param callable $fun Callback function
	 * @return \Parvula\Collections\Collection New collection
	 */
	public function map(callable $fun): self {
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
	public function isEmpty(): bool {
		return (array) $this->items === [];
	}

	/**
	 * Add item to the collection
	 *
	 * @param mixed $item
	 * @return \Parvula\Collections\Collection New collection
	 */
	public function add($item): self {
		return new static(array_merge($this->items, [$item]), $this->model);
	}

	/**
	 * Transform the collection to an array
	 *
	 * @return array
	 */
	public function toArray(?bool $removeNull = false): array {
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
	 * @return static
	 */
	protected function cloneCollection() {
		return new static($this->items, $this->model);
	}
}
