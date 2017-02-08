<?php

namespace Parvula\Repositories;

abstract class BaseRepository {

	/**
	 * The repository current's class name model
	 *
	 * @return string Model's full class name
	 */
	abstract protected function model();

}
