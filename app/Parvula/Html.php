<?php

namespace Parvula;

/**
 * HTML utils
 *
 * @package Parvula
 * @version 0.8.0
 * @since 0.1.0
 * @author Fabien Sa
 * @license MIT License
 */
class Html {

	/**
	 * Html anchor
	 *
	 * @param string $value
	 * @param string $href
	 * @param array $attr
	 * @return string Html anchor
	 */
	public static function anchor($value, $href = '#', $attr = []) {
		$attr = implode(' ', $attr);
		return sprintf('<a href="%s" %s>%s</a>', $href, $attr, $value);
	}

	/**
	 * Html image
	 *
	 * @param string $src Image source
	 * @param array $attr Image attributs
	 * @return string Html image
	 */
	public static function img($src, $attr = []) {
		$attr = implode(' ', $attr);
		return sprintf('<img src="%s" %s>', $src, $attr);
	}

	/**
	 * Secure echo. Return $var if exists, else return $else and encode special
	 * html chars.
	 *
	 * @param mixed $var Value to print
	 * @param mixed $else (optional) Value to print if variable doesn't exists
	 * @return string Secure string from XSS
	 */
	public static function escape($var, $else = '') {
		if (isset($var)) {
			$varCopy = $var;
		} else {
			$varCopy = $else;
		}

		return htmlspecialchars($varCopy);
	}

	/**
	 * Secure echo. Return '$value . $then' if $value exists
	 * @param mixed &$var
	 * @param mixed $then
	 * @return string Secure string from XSS
	 */
	public static function escapeThen($var, $then) {
		if (isset($var)) {
			$varCopy = $var . $then;
		} else {
			$varCopy = '';
		}

		return htmlspecialchars($varCopy);
	}

	/**
	 * Create html nav from array [TMP]
	 * @param array $items
	 * @param integer $level
	 * @return string Html nav
	 */
	public static function nav($items, $level = 0) {
		$ret = "";
		$indent = str_repeat(" ", $level * 2);
		$ret .= sprintf("%s<ul>\n", $indent);
		$indent = str_repeat(" ", ++$level * 2);
		foreach ($items as $item => $subitems) {
			if (is_array($subitems)) {
				$ret .= "\n";
				$ret .= static::nav($subitems, $level + 1);
				$ret .= $indent;
			} else {
				$ret .= sprintf("%s<li><a href='%s'>%s</a>", $indent, $subitems, $subitems);
			}
			$ret .= sprintf("</li>\n", $indent);
		}
		$indent = str_repeat(" ", --$level * 2);
		$ret .= sprintf("%s</ul>\n", $indent);
		return $ret;
	}

}
