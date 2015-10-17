<?php

namespace Parvula\Core\Model;

interface CRUDInterface {

	/**
	 * Index ressources
	 *
	 * @return array List of ressources
	 */
	public function index();

	/**
	 * Read
	 *
	 * @param string $id ID
	 * @throws Exception If the ressource does not exists
	 * @return mixed The ressource
	 */
	public function read($id);

	/**
	 * Update
	 *
	 * @param string $id ID
	 * @param mixed $data Data
	 * @return bool
	 */
	public function update($id, $data);

	/**
	 * Create
	 *
	 * @param mixed $data Data
	 * @return bool
	 */
	public function create($data);

	/**
	 * Delete
	 *
	 * @param string $id ID
	 * @return bool
	 */
	public function delete($id);
}
