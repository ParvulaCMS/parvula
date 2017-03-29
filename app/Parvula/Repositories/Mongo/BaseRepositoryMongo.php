<?php

namespace Parvula\Repositories\Mongo;

use Parvula\Repositories\BaseRepository;

abstract class BaseRepositoryMongo extends BaseRepository {

	protected $collection;

	/**
	 * Find by field
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
	 * @return Collection Collection of Model
	 */
	public function all() {
	// 	$modelClassName = $this->model();

	// 	return $this->data->map(function ($model) use ($modelClassName) {
	// 		return new $modelClassName($model);
	// 	});
	}
}
