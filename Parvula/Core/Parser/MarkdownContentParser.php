<?php

namespace Parvula\Core\Parser;

/**
 * MarkdownPageSerializer class
 *
 * @package Parvula
 * @since 0.4.0
 * @author Fabien Sa
 * @license MIT License
 */
class MarkdownContentParser implements ContentParserInterface {

	/**
	 * Parse data
	 * @param string $data
	 * @return string
	 */
	public function parse($data = '') {
		$parser = new MarkdownParvula;

		return $parser->transform($data);
	}

}
