<?php

namespace Parvula\ContentParser;

use ParsedownExtra;
use Parvula\Parvula;

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
