<?php

namespace Parvula\Collections;

use MongoDB\Collection as MongoCollectionBase;

class MongoCollection extends Collection
{
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

	public function sortBy(string $field, ?bool $ascending = true) {
		$options = $this->options;

		if (!isset($options['sort'])) {
			$options['sort'] = [];
		}

		$options['sort'][$field] = $ascending ? 1 : -1;

		return new static($this->collection, $this->model, $this->filter, $options);
	}

	public function filter(string $field, array $values = [true]) {
		$filter = $this->filter;
		$filter[$field] = ['$in' => $values];

		return new static($this->collection, $this->model, $filter, $this->options);
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
			if ($this->filter) {
				$aggregate[] = ['$match' => $this->filter];
			}

			// Transform options and append a `$`
			foreach ($this->options as $key => $option) {
				if ($key === 'projection') {
					$key = 'project';
				}
				$aggregate[] = ['$' . $key => $option];
			}

			return $this->collection->aggregate($aggregate, $this->options);
		}

		return $this->collection->find($this->filter, $this->options);
	}

	public function count(): int {
		return count($this->collection->count());
	}

	protected function cloneCollection() {
		return new static($this->collection, $this->model, $this->filter, $this->options);
	}
}
