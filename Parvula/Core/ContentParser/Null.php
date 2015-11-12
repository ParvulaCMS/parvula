<?php

namespace Parvula\Core\ContentParser;

/**
 * Null content parser
 *
 * @package Parvula
 * @since 0.5.0
 * @author Fabien Sa
 * @license MIT License
 */
class Null implements ContentParserInterface {

	/**
	 * Parse data
	 *
	 * @param string $data
	 * @return string
	 */
	public function parse($data = '') {
		return $data;
	}
}
