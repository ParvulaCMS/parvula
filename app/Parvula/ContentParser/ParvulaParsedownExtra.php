<?php

namespace Parvula\ContentParser;

use ParsedownExtra;

/**
 * Extends ParsedownExtra to add link functionalities.
 *
 * @version 0.5.0
 * @since 0.5.0
 * @author Fabien Sa
 * @license MIT License
 */
class ParvulaParsedownExtra extends ParsedownExtra
{
	protected function inlineLink($excerpt) {
		$link = parent::inlineLink($excerpt);

		// Parvula patch for absolute slug
		$href = $link['element']['attributes']['href'];
		if ($href[0] === '/') {
			$href = str_replace(['../', '..'], '', ltrim($href, '/')); // clean url
			$href = url($href); // absolute from root
			$link['element']['attributes']['href'] = $href;
		}

		return $link;
	}
}
