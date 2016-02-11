<?php

namespace Parvula\Core\ContentParser;

use ParsedownExtra;

/**
 * ParvulaParsedownExtra class, extends ParsedownExtra
 *
 * @package Parvula
 * @version 0.5.0
 * @since 0.5.0
 * @author Fabien Sa
 * @license MIT License
 */
class ParvulaParsedownExtra extends ParsedownExtra {

	/**
	 * @inheritdoc
	 */
	protected function inlineLink($Excerpt) {
		$Link = parent::inlineLink($Excerpt);

		// Parvula patch for absolute slug
		$href = $Link['element']['attributes']['href'];
		if ($href[0] === '/') {
			$href = str_replace(['../', '..'], '', ltrim($href, '/')); // clean url
			$href = \HTML::linkRel('') . $href; // absolute from root
			$Link['element']['attributes']['href'] = $href;
		}

		return $Link;
	}

}
