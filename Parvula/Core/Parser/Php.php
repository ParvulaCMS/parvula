<?php

namespace Parvula\Core\Parser;

class Php implements ParserInterface {

	public $include = true;

	/**
	 * This method simply return the php
	 *
	 * @param mixed $input
	 * @return mixed (same as $input)
	 */
	public function decode($php) {
		return $php;
	}

	/**
	 * Generate php
	 *
	 * @param array|object $data Data to encode
	 * @return string The php string
	 */
	public function encode($php) {
		return var_export($php, true);
	}
}
