<?php

namespace Parvula\Core\Model;

/**
 * This class represents a user
 *
 * @package Parvula
 * @version 0.5.0
 * @since 0.5.0
 * @author Fabien Sa
 * @license MIT License
 */
class User {

	public $login;

	public $email;

	protected $password;

	// public $group; // @future

	public function __construct(array $infos) {
		foreach ($infos as $key => $value) {
			if (property_exists($this, $key)) {
				$this->{$key} = $value;
			}
		}
	}

}
