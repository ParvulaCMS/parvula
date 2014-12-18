<?php

namespace Parvula\Core;

/**
 * Page type
 *
 * @package Parvula
 * @version 0.1.0
 * @since 0.1.0
 * @author Fabien Sa
 * @license MIT License
 */
class Page {
//implements Serializable {
	public $title;
	public $description;
	public $author;
	public $date;
	public $robots;
	public $index;
	public $content;
	public $hidden;

	public static function pageFactory($pageInfo) {
		$page = new self;

		foreach ($pageInfo as $field => $value) {
			if(property_exists(get_class(), $field)) {
				$page->$field = $value;
			}
		}

		return $page;
	}

	/**
	 * Override "tostring" when print this object
	 * @return string
	 */
	public function __tostring() {
		return json_encode($this);
	}

}
