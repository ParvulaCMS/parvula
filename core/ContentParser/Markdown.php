<?php

namespace Parvula\ContentParser;

/**
 * Markdown content parser.
 *
 * @since 0.4.0
 * @author Fabien Sa
 * @license MIT License
 */
class Markdown implements ContentParserInterface
{
	/**
	 * Parse data.
	 *
	 * @param  string     $data
	 * @throws \Exception
	 * @return string
	 */
	public function parse($data = ''): string {
		$parser = new ParvulaParsedownExtra();

		return $parser->text($data);
	}
}
