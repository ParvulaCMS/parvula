<?php

namespace Parvula\Model;

/**
 * CRUDI Interface
 *
 * @package Parvula
 * @version 0.5.0
 * @since 0.5.0
 * @author Fabien Sa
 * @license MIT License
 */
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
