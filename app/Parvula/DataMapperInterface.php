<?php

namespace Parvula;

interface DataMapperInterface {
	public function find($id);

	public function insert($data);

	public function update($id, $data);

	public function delete($id);
}
