<?php

namespace Parvula\Models;

use Exception;

/**
 * This class represents a user
 *
 * @package Parvula
 * @version 0.7.0
 * @since 0.5.0
 * @author Fabien Sa
 * @license MIT License
 */
class User extends Model
{

	/**
	 * @var string Username
	 */
	public $username;

	/**
	 * @var string Email
	 */
	public $email;

	/**
	 * @var string Password (hashed)
	 */
	public $password;

	// public $group; // @future

	/**
	 * @var array Choose API fields visibility
	 */
	protected $visible = [
		'username', 'email', 'group'
	];

	public function __construct(array $infos) {
		foreach ($infos as $key => $value) {
			if (property_exists($this, $key)) {
				$this->{$key} = $value;
			}
		}

		// TODO
		// if (password_get_info($this->password)['algo'] === 0) {
		// 	throw new Exception('Password must be hashed with password_hash');
		// }
	}

	/**
	 * Check if it is the right password
	 *
	 * @param  string $password Password
	 * @return bool If the password is ok
	 */
	public function login($password) {
		if (strlen($this->password) < 50) {
			// Not hashed, TODO temporary
			return $password === $this->password;
		}
		return password_verify($password, $this->password);
	}

	// TODO
	/**
	 * Get user's roles
	 *
	 * @return array Roles
	 */
	public function getRoles() {
		return ['all'];
	}

	/**
	 * Check if the user has the given role
	 * [Always true for Parvula 0.5 @future]
	 *
	 * @return boolean
	 */
	public function hasRole($role) {
		return true;
	}
}
