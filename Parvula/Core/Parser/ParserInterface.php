<?php

namespace Parvula\Core\Parser;

interface ParserInterface {

	/**
	 * Parse input to appropriate PHP type
	 *
	 * @param string $input The string to parse
	 * @return mixed Appropriate PHP type
	 */
	public function decode($input);

	/**
	 * Generate representation of the data
	 *
	 * @param array|object $data Data to encode
	 * @return string The encoded string
	 */
	public function encode($data);
}
