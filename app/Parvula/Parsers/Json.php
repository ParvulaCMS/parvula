<?php

namespace Parvula\Parsers;

use LogicException;

class Json implements ParserInterface
{
	/**
	 * Parse json.
	 *
	 * @param  string         $input The string to parse
	 * @throws LogicException If json could not be parsed
	 * @return array|object   Appropriate PHP type
	 */
	public function decode($json) {
		$json = trim($json, '-');
		$data = json_decode($json, true);
		if (json_last_error() !== JSON_ERROR_NONE) {
			$error = json_last_error_msg();
			throw new LogicException(sprintf("Failed to parse json string '%s', error: '%s'", $json, $error));
		}

		return $data;
	}

	/**
	 * Generate json.
	 *
	 * @param  array|object   $data Data to encode
	 * @throws LogicException If data could not be encoded
	 * @return string         The json encoded string
	 */
	public function encode($data) {
		$json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
		if (json_last_error() !== JSON_ERROR_NONE) {
			$error = json_last_error_msg();
			throw new LogicException(sprintf("Failed to encode data, error: '%s'", $error));
		}

		return $json;
	}
}
