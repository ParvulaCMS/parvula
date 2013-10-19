<?php

namespace Parvula\Core;

/**
 * HTML utils
 * 
 * @package Parvula
 * @version 0.1.0
 * @author Fabien Sa
 * @license MIT License
 */
class HTML {

	/**
	 * @var string
	 */
	private $path;

	/**
	 * Constructor
	 * @param string $path Path to prefix all path
	 * @param array $variables 
	 */
	function __construct($path = '/template', $variables = array()) {
		$this->path = rtrim($path, '/') . '/';
		$this->variables = $variables;
		$this->extension = '.html';
	}

	/**
	 * Html anchor
	 * @param string $value
	 * @param string $href 
	 * @param array $attr 
	 * @return string Html anchor
	 */
	public static function anchor($value, $href = '#', $attr = array()) {
		$attr = implode(' ', $attr);

		if(!preg_match('/^https?:\/\//', $href)) {
			if(!Config::get('URLRewriting')) {
				$href = 'index.php/' . $href;
			}
			$href = Parvula::getRelativeURIToRoot() . $href;
		}

		return sprintf('<a href="%s" %s>%s</a>', $href, $attr, $value);
	}
	
	/**
	 * Html image
	 * @param string $src Image source
	 * @param array $attr Image attributs
	 * @return string Html image
	 */
	public static function img($src, $attr = array()) {
		$attr = implode(' ', $attr);

		if(!preg_match('/^https?:\/\//', $src)) {
			if(!Config::get('URLRewriting')) {
				$src = 'index.php/' . $src;
			}
			$src = Parvula::getRelativeURIToRoot() . $src;
		}

		return sprintf('<img src="%s" %s>', $src, $attr);
	}

	/**
	 * Secure echo. Return $var if exists, else return $else and encode special 
	 * html chars.
	 * @param mixed $var Value to print
	 * @param mixed ($else) Value to print if variable doesn't exists
	 * @return string Secure string from XSS
	 */
	public static function sEcho(&$var, $else = '') {
		if(isset($var)) {
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
	public static function sEchoThen(&$var, $then) {
		if(isset($var)) {
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
