<?php

namespace Parvula\ContentParser;

/**
 * None content parser.
 *
 * @since 0.5.0
 * @author Fabien Sa
 * @license MIT License
 */
class None implements ContentParserInterface
{
	/**
	 * Parse data.
	 *
	 * @param  string $data
	 * @return string
	 */
	public function parse($data = ''): string {
		return $data;
	}
}
