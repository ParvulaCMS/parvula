<?php

namespace Parvula\Repositories\Mongo;

use Illuminate\Support\Collection;
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

	// Filename // TODO
	/**
	 * Find one by "id" -> filename
	 *
	 * @return Model
	 */
	public function find($uid) {
	// 	$modelClassName = $this->model();
	// 	foreach ($this->data as $model) {
	// 		if ($model[$attr] === $value) {
	// 			return new $modelClassName($model);
	// 		}
	// 	}

	// 	return false;
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
