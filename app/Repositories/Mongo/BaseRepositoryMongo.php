<?php

namespace App\Repositories\Mongo;

use Exception;
use Parvula\Collections\MongoCollection;
use Parvula\Repositories\BaseRepository;

abstract class BaseRepositoryMongo extends BaseRepository {

	/**
	 * @var \MongoDB\Collection
	 */
	protected $collection;

	/**
	 * Find by given field
	 *
	 * @return \Parvula\Models\Model|boolean
	 */
	public function findBy($attr, $value) {
		$bsonData = $this->collection->findOne([$attr => $value]);
		if (empty($bsonData)) {
			return false;
		}

		$modelClass = $this->model();
		return new $modelClass((array) $bsonData);
	}

	/**
	 * Find all by given field
	 *
	 * @return array|boolean List of Model
	 */
	public function findAllBy($attr, $value) {
		$bsonDataCol = $this->collection->find([$attr => $value]);
		if (empty($bsonDataCol)) {
			return false;
		}

		$modelClass = $this->model();
		$acc = [];
		foreach ($bsonDataCol as $data) {
			$acc[] = new $modelClass((array) $data);
		}

		return $acc;
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
		return (new MongoCollection($this->collection, $this->model(), [
			// 'projection' => ['_id' => 0]
			]))
			->map(function ($bsonData) use ($modelClassName) {
				return new $modelClassName((array) $bsonData);
			});
	}

	public function updateBy($attr, $value, array $data) {
		if (!$this->exists($attr, $value)) {
			throw new Exception('Model does not exists');
		}

		$modelClass = $this->model();
		$modelO = new $modelClass($data);

		try {
			$res = $this->collection->replaceOne(
				[$attr => $value],
				$modelO->toArray()
			);
			return $res->getModifiedCount() > 0;
		} catch (Exception $e) {
			return false;
		}
	}

	public function update($id, array $data) {
		return $this->updateBy('_id', $id, $data);
	}

	/**
	 * Delete a model by given field
	 *
	 * @param string $attr
	 * @param string $value
	 * @return boolean If model is deleted
	 */
	public function deleteBy($attr, $value) {
		$deleteResult = $this->collection->deleteOne([$attr => $value]);
		return $deleteResult->getDeletedCount() > 0;
	}

	/**
	 * Delete a model
	 *
	 * @param string $id
	 * @return boolean If model is deleted
	 */
	public function delete($id) {
		return $this->deleteBy('_id', $id);
	}

	/**
	 * Exists
	 *
	 * @param string $attr
	 * @param string $value
	 * @return bool
	 */
	protected function exists($attr, $value) {
		return !empty($this->collection->findOne([$attr => $value]));
	}
}
