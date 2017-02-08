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

	/**
	 * Return all data from a collection
	 */
	// public function all($fields = ['*']) {
	// 	$modelName = get_class($this->model);

	// }
	// abstract public function lists($value, $key = null);
	// abstract public function paginate($perPage = 1, $columns = ['*']);
	// abstract public function create(array $data);
	// if you use mongodb then you'll need to specify primary key $attribute
	// abstract public function update(array $data, $id, $attribute = "id");
	// abstract public function delete($id);
	// abstract public function find($id, $columns = ['*']);
	// abstract public function findBy($field, $value, $columns = ['*']);
	// abstract public function findAllBy($field, $value, $columns = ['*']);
	// abstract public function findWhere($where, $columns = ['*']);
}
