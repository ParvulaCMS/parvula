<?php

namespace Parvula\Repositories;

use Parvula\ArrayTrait;
use Parvula\FileParser;
use Parvula\Models\User;

class UserRepositoryFlatfiles extends UserRepository
{
	/**
	 * @var array Array of User
	 */
	private $parser;

	/**
	 * @var array User[]
	 */
	protected $data;

	/**
	 * @param FileParser $parser
	 * @param string     $usersFile
	 */
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
		// Return users username
		return array_map(function($user) {
			return $user['username'];
		}, $this->data);
	}

	/**
	 * Read a user from ID
	 *
	 * @param  string $id ID (username)
	 * //@throws Exception If the ressource does not exists
	 * @return User|bool The user or false if user not found
	 */
	public function read($id) {
		foreach ($this->data as $user) {
			if ($user['username'] === $id) {
				return new User($user);
			}
		}

		return false;
	}

	/**
	 * Update @next
	 *
	 * @param string $id ID
	 * @param mixed $data Data
	 * @return bool
	 */
	public function update($username, $user) {
		if ($userOld = $parser->read($user->username)) {

		}

		return false;
	}

	/**
	 * Create @next
	 *
	 * @param User $user User
	 * @return bool
	 */
	public function create($user) {
		if (!$parser->read($user->username)) {

		}

		return false;
	}

	/**
	 * Delete @next
	 *
	 * @param string $id ID
	 * @return bool
	 */
	public function delete($id) {

	}

}
