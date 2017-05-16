<?php

namespace Parvula\Repositories\Mongo;

use Parvula\Repositories\BaseRepository;

abstract class BaseRepositoryMongo extends BaseRepository {

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
	 * @return Collection Collection of Model
	 */
	public function all() {
	// 	$modelClassName = $this->model();

	// 	return $this->data->map(function ($model) use ($modelClassName) {
	// 		return new $modelClassName($model);
	// 	});
	}
}
