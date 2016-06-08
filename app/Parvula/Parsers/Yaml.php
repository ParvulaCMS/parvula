<?php

namespace Parvula\Parsers;

use Symfony\Component\Yaml\Yaml as YamlSymfony;

class Yaml implements ParserInterface {

	/**
	 * Parse Yaml
	 *
	 * @param string $input The string to parse
	 * @return array|object Appropriate PHP type
	 */
	public function decode($yaml) {
		return YamlSymfony::parse($yaml);
	}

	/**
	 * Generate Yaml
	 *
	 * @param array|object $data Data to encode
	 * @return string The json encoded string
	 */
	public function encode($data) {
		// preg_replace('/^---\n/', '', $dump); // TODO
		return YamlSymfony::dump($data);
	}
}
