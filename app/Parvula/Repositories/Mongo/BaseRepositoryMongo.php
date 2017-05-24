<?php

namespace Parvula\Repositories\Mongo;

use Parvula\Collections\MongoCollection;
use Parvula\Repositories\BaseRepository;

abstract class BaseRepositoryMongo extends BaseRepository {

	/**
	 * @var MongoDB\Collection
	 */
	protected $collection;

	/**
	 * Find by given field
	 *
	 * @return Model|boolean
	 */
	public function findBy($attr, $value) {
		$model = $this->collection->findOne([$attr => $value]);
		if (empty($model)) {
			return false;
		}

		return $model;
	}

	/**
	 * Find all by given field
	 *
	 * @return array|boolean
	 */
	public function findAllBy($attr, $value) {
		$model = $this->collection->find([$attr => $value]);
		if (empty($model)) {
			return false;
		}

		return $model;
	}

	/**
	 * Find one by Mongo _id
	 *
	 * @return Model
	 */
	public function find($id) {
		return $this->findBy('_id', $id);
	}

	/**
	 * @return Collection Collection of Model
	 */
	public function all() {
		$modelClassName = $this->model();
		return (new MongoCollection($this->collection, $this->model()))
			->map(function ($model) use ($modelClassName) {
				return new $modelClassName((array) $model);
			});
	}
}
