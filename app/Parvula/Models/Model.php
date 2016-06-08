<?php

namespace Parvula\Models;

use Parvula\ArrayableInterface;
use Parvula\AccessorTrait;

abstract class Model implements ArrayableInterface {

	use AccessorTrait;

	public function toArray() {
		// TODO
	}

}
