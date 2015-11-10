<?php

namespace Parvula\Core\Model;

/**
 * This class represents a user
 *
 * @package Parvula
 * @version 0.5.0
 * @since 0.5.0
 * @author Fabien Sa
 * @license MIT License
 */
class User {

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
	protected $password;

	// public $group; // @future

	public function __construct(array $infos) {
		foreach ($infos as $key => $value) {
			if (property_exists($this, $key)) {
				$this->{$key} = $value;
			}
		}
	}

	/**
	 * Check if it is the right password
	 *
	 * @param  string $password Password
	 * @return bool If the password is ok
	 */
	public function login($password) {
		return password_verify($password, $this->password);
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
