<?php

namespace Parvula\Core;

/**
 * Template
 * Light, simple and fast PHP Template Engine
 *
 * @package Parvula
 * @version 0.1.0
 * @since 0.1.0
 * @author Fabien Sa
 * @license MIT License
 */
class View {

	/**
	 * @var string
	 */
	private $extension;

	/**
	 * @var string
	 */
	private $path;

	/**
	 * @var array
	 */
	private $variables = [];

	/**
	 * Constructor. You can set the root path for all paths.
	 *
	 * @param string Root path (dir)
	 */
	function __construct($path = '/templates', $variables = []) {
		$this->path = rtrim($path, '/') . '/';
		$this->variables = $variables;
		$this->extension = '.html';
	}

	/**
	 * Assign array for simple tags in template.<br />
	 * You can assign array or associate string in array.
	 *
	 * Exemple :<br />
	 * <code>assign(array('var1' => 'foo'))</code><br />
	 * or<br />
	 * <code>assign('var1', 'foo')</code>
	 *
	 * @param mixed array OR string and array
	 * @return
	 */
	public function assign($array, $arg2 = NULL) {
		if(is_array($array)) {
			$this->variables = $array + $this->variables;
		} else if(is_string($array)) {
			$this->variables[$array] = $arg2;
		}
	}

	public function __set($key, $var) {
		$this->variables[$key] = $var;
	}

	/**
	 * Display final result, after parsing.
	 *
	 * @param string $templateName is the Canonical name of the template (without file extension if it was set).
	 * @param boolean $printBuffer if directly want to print result.
	 * @return string| return String if $printBuffer is true. Else void.
	 */
	public function display($templateName, $printBuffer = true) {
		if(is_array($printBuffer)) {
			$this->assign($printBuffer);
			$printBuffer = true;
		}
		$i = 1 + is_bool($printBuffer);

		$args = func_get_args();
		$argsLength = sizeof($args);
		for(; $i < $argsLength; ++$i) {
			$var = ('page' . $i);
			$this->variables[$var] = $args[$i];
		}

		if($printBuffer) {
			echo $this->parse($templateName);
		} else {
			return $this->parse($templateName);
		}
	}

	/**
	 * Set extension type for all files.<br />
	 * Exemple <code>setExtension('.tpl.html');</code>
	 *
	 * @param string $templatePath
	 * @return
	 */
	public function setExtension($extension) {
		$this->extension = trim($extension);
	}

	/**
	 * Secure print. Escape special chars from string.
	 *
	 * @param string $varName variable to print
	 * @param string $print true if directly print result
	 * @return
	 */
	private function sprint($varName, $print = true) {
		if($print)
			echo htmlspecialchars($varName);
		else
			return htmlspecialchars($varName);
	}

	/**
	 * Alias of {@see display}
	 */
	public function view($templateName, $returnBuffer = false) {
		return $this->display($templateName, $returnBuffer);
	}

	/**
	 * Parse template (evaluate short syntaxe, include sub files)
	 *
	 * @param string $templateName Canonical name of the template
	 * @return string $buffer
	 */
	private function parse($templateName) {
		$templatePath = $this->path . $templateName . $this->extension;
		if(!is_readable($templatePath)) {
			throw new \Exception("Parse fail. File $templatePath not found.", 1);

			return false;
		}
		$view = $this; // ref.
		extract($this->variables);

		//Turn on output buffering
		ob_start();
		include_once $templatePath;
		$buffer = ob_get_clean();

		return $buffer;
	}

	/**
	 * Clean path (only for dir, not file)
	 *
	 * @param string $path Path to clean
	 * @return string $path
	 */
	private function cleanDirPath($path) {
		$path = trim($path);
		if($path !== '' && $path[strlen($path) - 1] !== '/') {
			$path .= '/';
		}

		return $path;
	}

	/**
	 * Alias of {@see display}
	 */
	public function __invoke($templateName, $returnBuffer = false) {
		return $this->display($templateName, $returnBuffer);
	}

	/**
	 * Get path
	 * @return string Path
	 */
	public function getPath() {
		return $this->path;
	}

	/**
	 * Alias of {@see display}
	 */
	public function __tostring() {
		return $this->display($templateName, true);
	}

}
