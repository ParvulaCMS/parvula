<?php

namespace Parvula\Repositories;

use Parvula\ArrayTrait;
use Parvula\FileParser;
use Parvula\Models\User;
// use Parvula\Transformers\UserTransformer;

class UserRepositoryFlatfiles extends BaseRepositoryFlatfiles
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

	protected function model() {
		return User::class;
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
