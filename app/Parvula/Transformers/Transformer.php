<?php

namespace Parvula\Transformers;

use Parvula\Models\Model;

/**
 * Abstract Transformer
 *
 * @package Parvula
 * @since 0.8.0
 * @author Fabien Sa
 * @license MIT License
 */
abstract class Transformer {
	public function __invoke(Model $model) {
		return $this->apply($model);
	}
}
