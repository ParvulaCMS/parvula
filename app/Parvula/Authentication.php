<?php

namespace Parvula;

use Parvula\Model\User;

class Authentication {

	/**
	 * @var mixed Token
	 */
	private $token;

	/**
	 * @var Session
	 */
	private $session;

	/**
	 * Constructor
	 *
	 * @param string $name
	 * @param mixed $token
	 */
	public function __construct(Session $session, $token) {
		$this->token = $token;
		$this->session = $session;
	}

	/**
	 * Check if username is currently logged
	 *
	 * @param  string  $username
	 * @return boolean If the given username is currently logged
	 */
	public function isLogged($username) {
		return $this->session->get('username') === $username && $this->session->get('token') === $this->token;
	}

	/**
	 * Log the given user **without checking any password or role**
	 *
	 * @param string $username
	 */
	public function log($username) {
		$this->session->regenerateId();

		// Create a session
		$this->session->set('username', $username);
		$this->session->set('token', $this->token);
	}

	/**
	 * Logout all sessions
	 *
	 * @return bool
	 */
	public function logout() {
		$this->session->regenerateId();

		return $this->session->destroy();
	}

}
