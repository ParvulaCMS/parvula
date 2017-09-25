<?php

namespace Parvula\Repositories\Mongo;

use Exception;
use Parvula\ArrayTrait;
use Parvula\FileParser;
use Parvula\FilesSystem as Files;
use Parvula\Models\Config;
use Parvula\Collections\Collection;
use MongoDB\Collection as MongoCollectionBase;

class ConfigRepositoryMongo extends BaseRepositoryMongo {

	/**
	 * @param FileParser $parser
	 * @param string     $configsFolder
	 */
	public function __construct(MongoCollectionBase $collection) {
		$this->collection = $collection;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function model() {
		return Config::class;
	}

	/**
	 * Find by field
	 * @return Model
	 */
	public function find($name) {
		if (!$config = $this->findBy('name', $name)) {
			return false;
		}

		// Hack to remove name and _id
		return new Config((array) $config->toArray()['data']);
	}

	/**
	 * Update
	 *
	 * @param string $name
	 * @param mixed $data Data
	 * @return bool
	 */
	public function update($name, array $data) {
		if (!$this->exists('name', $name)) {
			return false;
		}

		$config = [
			'name' => $name,
			'data' => $data
		];

		try {
			return $this->collection->replaceOne(
				['name' => $name],
				$config
			)->getMatchedCount() > 0;
		} catch (Exception $e) {
			throw new IOException('Config cannot be created');
		}
	}

	/**
	 * Create
	 *
	 * @param array $data
	 * @return bool
	 */
	public function create(array $data) {
		if (!isset($data['name'], $data['data'])) {
			throw new Exception('Page cannot be created. It must have a name and a data field');
		}

		try {
			return $this->collection->insertOne($data)->getInsertedCount() > 0;
		} catch (Exception $e) {
			throw new IOException('Config cannot be created');
		}
	}

	/**
	 * Delete
	 *
	 * @param string $name Config name
	 * @return bool
	 */
	public function delete($name) {
		return $this->deleteBy('name', $name);
	}
}
