<?php

namespace Parvula\Transformers;

use Parvula\Models\Model;

abstract class Transformer {
	public function __invoke(Model $model) {
		return $this->apply($model);
	}
}
