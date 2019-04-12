<?php

namespace App\Repositories\Flatfiles;

use Parvula\Repositories\BaseRepository;

abstract class BaseRepositoryFlatfiles extends BaseRepository
{
	/**
	 * Collection
	 * @var Parvula\Collections\Collection Collection of model
	 */
	protected $data;

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

	/**
	 * Find all by field
	 * @return array
	 */
	public function findAllBy($attr, $value) {
		$modelClassName = $this->model();
		$acc = [];
		foreach ($this->data as $model) {
			if ($model[$attr] === $value) {
				$acc[] = new $modelClassName($model);
			}
		}

		return $acc;
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

	public function map($callback) {
		$modelClassName = $this->model();

		return $this->data->map(function ($model) use ($modelClassName) {
			return $callback(new $modelClassName($model));
		});
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
