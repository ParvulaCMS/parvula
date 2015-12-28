<?php

namespace Parvula\Core;

use Exception;

/**
 * Session
 *
 * Usage example:
 * ```
 * $sess = new Parvula\Core\Session('secret_string');
 * $sess->start();
 *
 * $sess->set('user_id', $userID); // Set user_id
 * $sess->get('user_id'); // Get previous value
 * ```
 */
class Session {

	private $sessionName;

	/**
	 * Constructor
	 */
	public function __construct($sessionName = 'PHPSESSID') {
		$this->sessionName = $sessionName;
	}

	/**
	 * Starts the session
	 *
	 * @param bool $regenerateId
	 * @return bool If the session has started
	 */
	public function start($regenerateId = false) {
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
	public function regenerateId() {
		session_regenerate_id(true);
	}

	/**
	 * Gets the session value
	 *
	 * @param string $index
	 * @param  mixed $defaultValue (optional) Default value if nothing was found
	 * @return string|null
	 */
	public function get($index, $defaultValue = null) {
		if(!$this->has($index)) {
			return $defaultValue;
		}

		return $_SESSION[$index];
	}

	/**
	 * Set session value
	 *
	 * @param string $index
	 * @param mixed  $value
	 */
	public function set($index, $value) {
		return $_SESSION[$index] = $value;
	}

	/**
	 * Check if session has a given index
	 *
	 * @param  string  $index
	 * @return boolean If the session index exists
	 */
	public function has($index) {
		return isset($_SESSION[$index]);
	}

	/**
	 * Destroy all data registered to this session
	 *
	 * @return bool
	 */
	public function destroy() {
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
	public function isActive() {
		return session_status() === PHP_SESSION_ACTIVE;
	}

}
