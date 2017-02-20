<?php

namespace Parvula\Repositories;

abstract class BaseRepository {

	/**
	 * The repository current's class name model
	 *
	 * @return string Model's full class name
	 */
	abstract protected function model();

	abstract public function find($id);

	abstract public function findBy($field, $value);

	abstract public function create(array $data);

	abstract public function update($id, array $data);
}
