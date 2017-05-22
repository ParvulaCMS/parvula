<?php

namespace Parvula\Collections;

use IteratorAggregate;
use MongoDB\Collection as MongoCollectionBase;
use Parvula\ArrayableInterface;
use Parvula\Models\Model;
use Parvula\Models\Page;

class MongoCollection extends Collection {

	/**
	 * @var \MongoDB\Collection
	 */
	protected $collection;

	/**
	 * @var array
	 */
	protected $options;

	/**
	 * @var array
	 */
	protected $filter;

	public function __construct(MongoCollectionBase $collection, $model, $filter = [], $options = []) {
		$this->collection = $collection;
		$this->model = $model;
		$this->filter = $filter;
		$this->options = $options;
	}

	/**
	 * {@inheritDoc}
	 * @return \Parvula\Collections\MongoCollection
	 */
	public function sortBy($field, $ascending = true) {
		if (!isset($this->options['sort'])) {
			$this->options['sort'] = [];
		}

		$this->options['sort'][$field] = $ascending ? 1 : -1;

		return $this->clone();
	}

	/**
	 * {@inheritDoc}
	 * @return \Parvula\Collections\MongoCollection
	 */
	public function filter($field, array $values = [true]) {
		$this->filter[$field] = ['$in' => $values];

		return $this->clone();
	}

	/**
	 * @return \MongoDB\Driver\Cursor
	 */
	protected function all() {
		// Possibility to use aggregation
		if (isset($this->filter['$$aggregate'])) {
			$aggregate = $this->filter['$$aggregate'];
			unset($this->filter['$$aggregate']);

			// Use the filter as a $match for aggregation
			$aggregate[] = ['$match' => $this->filter];

			// Use projection in option if exists
			if (isset($this->options['projection'])) {
				$aggregate[] = ['$project' => $this->options['projection']];
			}

			return $this->collection->aggregate($aggregate, $this->options);
		}
		return $this->collection->find($this->filter, $this->options);
	}

	/**
	 * {@inheritDoc}
	 */
	public function count() {
		return count($this->collection->count());
	}

	/**
	 * {@inheritDoc}
	 */
	protected function clone() {
		return new static($this->collection, $this->model, $this->filter, $this->options);
	}
}
