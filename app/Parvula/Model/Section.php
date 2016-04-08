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
 * @author Fabien Sa
 * @license MIT License
 */
class Section {

	// /**
	//  * @var array Section's metadata
	//  */
	// public $meta;

	/**
	 * @var string Section's content
	 */
	public $content;

	/**
	 * Constructor
	 *
	 * @param array|object $meta Page's metas
	 * @param string $content (optional) Content
	 */
	public function __construct(array $meta, $content = '') {
		if (empty($meta)) {
			throw new SectionException('Section cannot be created, section MUST have a `name`');
		}

		foreach ($meta as $key => $val) {
			$this->{$key} = $val;
		}

		// $this->meta = $meta;
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
