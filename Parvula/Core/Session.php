<?php

namespace Parvula\Core;

class Session {

	/**
	 * @var bool If session is started
	 */
	protected $isStarted = false;

	/**
	 * @var bool Prefix
	 */
	protected $prefix;

	/**
	 * Constructor
	 *
	 * @param string $prefix
	 */
	public function __construct($prefix = '') {
		$this->prefix = $prefix;
	}

	/**
	 * Starts the session
	 *
	 * @return bool If the session has started
	 */
	public function start() {
		if (!headers_sent() && !$this->isStarted) {
			$this->isStarted = true;
			return session_start() && session_regenerate_id(true);
		}

		return false;
	}

	/**
	 * Get session value
	 *
	 * @param  string $index
	 * @param  mixed $defaultValue (optional) Default value if nothing in the session
	 * @return mixed
	 */
	public function get($index, $defaultValue = null) {
		if ($this->has($index)) {
			return $_SESSION[$this->prefix . $index];
		}

		return $defaultValue;
	}

	/**
	 * Set session value
	 *
	 * @param string $index
	 * @param mixed $value
	 */
	public function set($index, $value) {
		$_SESSION[$this->prefix . $index] = $value;
	}

	/**
	 * Check if session has a given index
	 *
	 * @param  string  $index
	 * @return boolean If the session index exists
	 */
	public function has($index) {
		return isset($_SESSION[$this->prefix . $index]);
	}

	/**
	 * Destroy all data registered to this session
	 *
	 * @return bool
	 */
	public function destroy() {
		if ($this->isStarted) {
			return session_destroy();
		}

		return false;
	}

}
