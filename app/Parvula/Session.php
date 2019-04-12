<?php

namespace Parvula;

use Exception;

/**
 * Session
 *
 * Usage example:
 * ```
 * $sess = new \Parvula\Session('secret_string');
 * $sess->start();
 *
 * $sess->set('user_id', $userID); // Set user_id
 * $sess->get('user_id'); // Get previous value
 * ```
 *
 * @package Parvula
 * @author Fabien Sa
 * @license MIT License
 */
class Session {

	private $sessionName;

	/**
	 * Constructor
	 */
	public function __construct(string $sessionName = 'SESSID') {
		$this->sessionName = $sessionName;
	}

	/**
	 * Starts the session
	 *
	 * @param bool $regenerateId
	 * @return bool If the session has started
	 */
	public function start(bool $regenerateId = false) {
		if (!headers_sent()) {
			session_name($this->sessionName);

			if (!$this->isActive()) {
				session_start();
			}

			session_regenerate_id($regenerateId);

			return true;
		}

		return false;
	}

	/**
	 * Regenerate session id
	 */
	public function regenerateId(): void {
		session_regenerate_id(true);
	}

	/**
	 * Gets the session value
	 *
	 * @param string $index
	 * @param mixed $defaultValue (optional) Default value if nothing was found
	 * @return string|null
	 */
	public function get(string $index, $defaultValue = null) {
		if (!$this->has($index)) {
			return $defaultValue;
		}

		return $_SESSION[$index];
	}

	/**
	 * Set session value
	 *
	 * @param string $index
	 * @param mixed $value
	 * @return mixed
	 */
	public function set(string $index, $value) {
		return $_SESSION[$index] = $value;
	}

	/**
	 * Check if session has a given index
	 *
	 * @param  string  $index
	 * @return boolean If the session index exists
	 */
	public function has(string $index) {
		return isset($_SESSION[$index]);
	}

	/**
	 * Destroy all data registered to this session
	 *
	 * @return bool
	 */
	public function destroy(): bool {
		if ($this->isActive()) {
			session_unset();

			$res = session_destroy();
			session_write_close();

			return $res;
		}

		return false;
	}

	/**
	 * Check if the session is already started
	 *
	 * @return boolean
	 */
	public function isActive(): bool {
		return session_status() === PHP_SESSION_ACTIVE;
	}
}
