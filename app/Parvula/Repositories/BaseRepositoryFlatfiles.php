<?php

namespace Parvula\Repositories;

abstract class BaseRepositoryFlatfiles extends BaseRepository {

	/**
	 * Find by
	 */
	public function findBy($attr, $value) {
		$modelClassName = $this->model();
		foreach ($this->data as $model) {
			if ($model[$attr] === $value) {
				return new $modelClassName($model);
			}
		}

		return false;
	}

	// Filename
	public function find($uid) {
		$modelClassName = $this->model();
		foreach ($this->data as $model) {
			if ($model[$attr] === $value) {
				return new $modelClassName($model);
			}
		}

		return false;
	}

	/**
	 * @return array Array of Model
	 */
	public function all() {
		$modelClassName = $this->model();
		return array_map(function ($model) use ($modelClassName) {
			return new $modelClassName($model);
		}, $this->data);
	}
}
