<?php

namespace Parvula\Core\Model\Mapper;

use Parvula\Core\Model\User;
use Parvula\Core\ArrayTrait;
use Parvula\Core\FileParser;
use Parvula\Core\Model\Mapper\AbstractDataMapper;

class Users extends AbstractDataMapper
{

	/**
	 * @var array Array of User
	 */
	private $parser;

	public function __construct(FileParser $parser, $usersFile) {
		$this->parser = $parser;
		$this->data = $parser->read($usersFile);
	}

	/**
	 * Index ressources
	 *
	 * @return array List of ressources
	 */
	public function index() {
		// Return users login
		return array_map(function($user) {
			return $user['login'];
		}, $this->data);
	}

	/**
	 * Read a user from ID
	 *
	 * @param string $id ID (login)
	 * @throws Exception If the ressource does not exists
	 * @return mixed The ressource
	 */
	public function read($id) {
		foreach ($this->data as $user) {
			if ($user['login'] === $id) {
				return new User($user);
			}
		}
	}

	/**
	 * Update
	 *
	 * @param string $id ID
	 * @param mixed $data Data
	 * @return bool
	 */
	public function update($login, $user) {
		// ovveride ?
		if ($userOld = $parser->read($user->login)) {

		}

		return false;
	}

	/**
	 * Create
	 *
	 * @param User $user User
	 * @return bool
	 */
	public function create($user) {
		if (!$parser->read($user->login)) {

		}

		return false;
	}

	/**
	 * Delete
	 *
	 * @param string $id ID
	 * @return bool
	 */
	public function delete($id) {

	}

}
