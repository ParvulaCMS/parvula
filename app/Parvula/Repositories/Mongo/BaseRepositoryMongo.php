<?php

namespace Parvula\Repositories\Mongo;

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
}
