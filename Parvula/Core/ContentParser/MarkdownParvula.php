<?php

namespace Parvula\Core\ContentParser;

/**
 * MarkdownParvula class
 *
 * @package Parvula
 * @version 0.1.0
 * @since 0.1.2
 * @author Fabien Sa
 * @license MIT License
 */
class MarkdownParvula extends \Michelf\MarkdownExtra {

	/**
	 * inline_callback for doImages
	 */
	protected function _doImages_inline_callback($matches) {
		$whole_match	= $matches[1];
		$alt_text		= $matches[2];
		$url			= $matches[3] === '' ? $matches[4] : $matches[3];
		$title			=& $matches[7];
		$attr           = $this->doExtraAttributes("img", $dummy =& $matches[8]);

		$alt_text = $this->encodeAttribute($alt_text);

		if(!preg_match('/^(https?|ftp):\/\//', $url)) {
			$url = \HTML::linkRel(IMAGES) . $url;
		}
		$url = $this->encodeAttribute($url);

		$result = "<img src=\"$url\" alt=\"$alt_text\"";
		if (isset($title)) {
			$title = $this->encodeAttribute($title);
			$result .=  " title=\"$title\""; # $title already quoted
		}
		$result .= $attr;
		$result .= $this->empty_element_suffix;


		return $this->hashPart($result);
	}

	/**
	 * inline_callback for doAnchors
	 */
	protected function _doAnchors_inline_callback($matches) {
		$whole_match	=  $matches[1];
		$link_text		=  $this->runSpanGamut($matches[2]);
		$url			=  $matches[3] === '' ? $matches[4] : $matches[3];
		$title			=& $matches[7];

		if(!preg_match('/^(https?|ftp):\/\//', $url)) {
			$url = \HTML::linkRel('') . $url;
		}
		$url = $this->encodeAttribute($url);

		$result = "<a href=\"$url\"";
		if (isset($title)) {
			$title = $this->encodeAttribute($title);
			$result .=  " title=\"$title\"";
		}

		$link_text = $this->runSpanGamut($link_text);
		$result .= ">$link_text</a>";

		return $this->hashPart($result);
	}

}
