<?php

namespace Parvula\Repositories;

abstract class BaseRepository {

	/**
	 * The repository current's class name model
	 *
	 * @return string Model's full class name
	 */
	abstract protected function model();

	/**
	 * Find one model by id
	 *
	 * @param mixed $id
	 * @return Model
	 */
	abstract public function find($id);

	/**
	 * Find one model by a given field
	 *
	 * @param mixed $field
	 * @param mixed $value
	 * @return Model
	 */
	abstract public function findBy($field, $value);

	/**
	 * Find all models by a given field
	 *
	 * @param mixed $field
	 * @param mixed $value
	 * @return array List of models
	 */
	abstract public function findAllBy($field, $value);

	/**
	 * Create a new model from an array of data
	 *
	 * @param array $data
	 * @return bool
	 */
	abstract public function create(array $data);

	/**
	 * Update a given model
	 *
	 * @param mixed $id
	 * @param array $data
	 * @return bool
	 */
	abstract public function update($id, array $data);

	/**
	 * Delete a given model
	 *
	 * @param mixed $id
	 * @return bool
	 */
	abstract public function delete($id);
}
