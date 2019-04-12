<?php

namespace App\Repositories\Mongo;

use Parvula\Models\User;
use MongoDB\Collection as MongoCollectionBase;

class UserRepositoryMongo extends BaseRepositoryMongo
{

	/**
	 * Constructor
	 *
	 * @param Collection $collection
	 */
	public function __construct(MongoCollectionBase $collection) {
		$this->collection = $collection;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function model() {
		return User::class;
	}

	/**
	 * Index ressources
	 *
	 * @return array List of ressources
	 */
	public function index() {
		return $this->collection->distinct('username');
	}

	/**
	 * Read a user from ID
	 *
	 * @param  string $username
	 * @throws Exception If the resource does not exists
	 * @return User|bool The user or false if user not found
	 */
	public function find($username) {
		if ($username === null) {
			return false;
		}

		$user = $this->collection->findOne(['username' => $username]);

		if ($user === null) {
			return false;
		}

		return new User(iterator_to_array($user));
	}

	/**
	 * Update @next
	 *
	 * @param string $username
	 * @param mixed $data Data
	 * @return bool
	 */
	public function update($username, array $data) {
		return false;
	}

	/**
	 * Create @next
	 *
	 * @param User $user User
	 * @return bool
	 */
	public function create(array $user) {
		if (get_class($user) !== $this->model()) {
			#throw ''; # TODO
			return false;
		}

		if (in_array($user->username, $this->collection->distinct('name')) ||
			in_array($user->email, $this->collection->distinct('email'))) {
			return false; # TODO
		}

		$user->password = password_hash($user->password, PASSWORD_DEFAULT);

		return $this->collection->insertOne($user)->getInsertedCount() > 0 ? true : false;
	}

	/**
	 * Delete @next
	 *
	 * @param string $username name
	 * @return bool
	 */
	public function delete($username) {
		if ($username === null || $this->collection->findOneAndDelete(['username' => $username]) === null) {
			return false;
		}

		return true;
	}
}
