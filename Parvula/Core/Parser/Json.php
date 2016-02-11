<?php

namespace Parvula\Core\Parser;

class Json implements ParserInterface {

	/**
	 * Parse json
	 *
	 * @param string $input The string to parse
	 * @return array|object Appropriate PHP type
	 */
	public function decode($json) {
		return json_decode($json);
	}

	/**
	 * Generate json
	 *
	 * @param array|object $data Data to encode
	 * @return string The json encoded string
	 */
	public function encode($data) {
		return json_encode($data);
	}
}
