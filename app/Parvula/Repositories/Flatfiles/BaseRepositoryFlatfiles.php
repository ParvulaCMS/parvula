<?php

namespace Parvula\Repositories\Flatfiles;

use Parvula\Repositories\BaseRepository;

abstract class BaseRepositoryFlatfiles extends BaseRepository {

	/**
	 * Find by field
	 * @return Model
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

	// Filename // TODO
	/**
	 * Find one by "id" -> filename
	 *
	 * @return Model
	 */
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
	 * @return Collection Collection of Model
	 */
	public function all() {
		$modelClassName = $this->model();

		return $this->data->map(function ($model) use ($modelClassName) {
			return new $modelClassName($model);
		});
	}
}
