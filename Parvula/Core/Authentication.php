<?php

namespace Parvula\Core;

use Parvula\Core\Model\User;

class Authentication extends Session {

	/**
	 * @var mixed Token
	 */
	private $token;

	/**
	 * Constructor
	 *
	 * @param string $prefix
	 * @param mixed $token
	 */
	public function __construct($prefix = '', $token) {
		parent::__construct($prefix);
		$this->token = $token;
		$this->start();
	}

	/**
	 * Check if username is currently logged
	 *
	 * @param  string  $username
	 * @return boolean If the given username is currently logged
	 */
	public function isLogged($username) {
		return $this->get('username') === $username && $this->get('token') === $this->token;
	}

	/**
	 * Log the given user **without checking any password or role**
	 *
	 * @param string $username
	 */
	public function log($username) {
		// Create a session
		$this->set('username', $username);
		$this->set('token', $this->token);
	}

	/**
	 * Logout all sessions
	 *
	 * @return bool
	 */
	public function logout() {
		return $this->destroy();
	}

}
