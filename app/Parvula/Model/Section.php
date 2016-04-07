<?php

namespace Parvula\Model;

use DateTime;
use Parvula\Exception\SectionException;

/**
 * This class represents a Section
 *
 * @package Parvula
 * @version 0.7.0
 * @since 0.7.0
 * @author Patrice Sa
 * @license MIT License
 */
class Section {

	/**
	 * @var string Section's name
	 */
	public $name;

	/**
	 * @var string Section's content
	 */
	public $content;

	/**
	 * Constructor
	 *
	 * @param string name Name
	 * @param string $content (optional) Content
	 */
	public function __construct($name, $content = '') {
		if (empty($name) || empty($content)) {
			throw new SectionException('Section cannot be created, section MUST have a `name` and a `content`');
		}

		$this->name = $name;
		$this->content = $content;
	}


	/**
	 * Override `tostring` when print this object
	 *
	 * @return string
	 */
	public function __tostring() {
		return json_encode($this);
	}
}
