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
		$this->options = $options;
		$this->filter = $filter;
	}

	public function sortBy($field, $ascending = true) {
		if (!isset($this->options['sort'])) {
			$this->options['sort'] = [];
		}

		$this->options['sort'][$field] = $ascending ? 1 : -1;

		return $this->clone();
	}

	public function filter($field, array $values = [true]) {
		$this->filter[$field] = ['$in' => $values];

		return $this->clone();
	}

	/**
	 * @return \MongoDB\Driver\Cursor
	 */
	protected function all() {
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
