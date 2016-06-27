<?php

namespace Parvula\ContentParser;

/**
 * Markdown content parser
 *
 * @package Parvula
 * @since 0.4.0
 * @author Fabien Sa
 * @license MIT License
 */
class Markdown implements ContentParserInterface {

	/**
	 * Parse data
	 *
	 * @param string $data
	 * @return string
	 */
	public function parse($data = '') {
		$parser = new ParvulaParsedownExtra;

		return $parser->text($data);
	}
}
