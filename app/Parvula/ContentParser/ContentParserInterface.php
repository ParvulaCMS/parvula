<?php

namespace Parvula\ContentParser;

/**
 * ContentParser interface
 *
 * @package Parvula
 * @since 0.5.0
 * @author Fabien Sa
 * @license MIT License
 */
interface ContentParserInterface {

	/**
	 * Parse data
	 * @param string $data
	 * @return string parsed data
	 */
	public function parse($data);
}
