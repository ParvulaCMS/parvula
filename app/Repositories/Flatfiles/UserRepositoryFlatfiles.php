<?php

namespace App\Repositories\Flatfiles;

use Parvula\FileParser;
use Parvula\Models\User;
use Parvula\Collections\Collection;

class UserRepositoryFlatfiles extends BaseRepositoryFlatfiles
{
	/**
	 * @var Parvula\FileParser
	 */
	private $parser;

	/**
	 * @param FileParser $parser
	 * @param string     $usersFile
	 */
	public function __construct(FileParser $parser, $usersFile) {
		$this->parser = $parser;
		$this->data = new Collection($parser->read($usersFile));
	}

	/**
	 * {@inheritDoc}
	 */
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
	public function update($username, array $userData) {
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
	public function create(array $userData) {
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
