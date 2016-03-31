<?php

namespace Parvula\Core\Model\Mapper;

use Parvula\Core\Model\User;
use Parvula\Core\ArrayTrait;
use Parvula\Core\FileParser;
use Parvula\Core\Model\Mapper\AbstractDataMapper;

class UsersMongo
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
	public function __construct($collection) {
		$this->collection = $collection;
	}

	private function exists($slug) {
		if ($this->read($slug)) {
			return true;
		}
		return false;
	}


	/**
	 * Index ressources
	 *
	 * @return array List of ressources
	 */
	public function index() {
		// Return users username
		return $this->collection->distinct('uid');
	}

	/**
	 * Read a user from ID
	 *
	 * @param  string $id ID (username)
	 * @throws Exception If the ressource does not exists
	 * @return User|bool The user or false if user not found
	 */
	public function read($id) {
		$user = $this->collection->findOne(['uid' => $id]);

		if (empty($page)) {
			return false;
		}
			return new User();
		}

	/**
	 * Update @next
	 *
	 * @param string $id ID
	 * @param mixed $data Data
	 * @return bool
	 */
	public function update($username, $user) {
		return false;
	}

	/**
	 * Create @next
	 *
	 * @param User $user User
	 * @throws
	 * @return bool
	 */
	public function create($user) {
		if (!isset($user['name'], $user['password'], $user['email'])) {
			# throw ''; # TODO
		}

		#if ($this->collection->
		echo('tgggg');

		return $this->collection->insertOne($user)->getInsertedCount() > 0 ? true : false;
	}

	/**
	 * Delete @next
	 *
	 * @param string $id ID
	 * @return bool
	 */
	public function delete($id) {
		if ($id === null) {
			return false;
		}

		if ($this->collection->findOneAndDelete(['' => $id]) === null) {
			return false;
		}

		return true;
	}

}
