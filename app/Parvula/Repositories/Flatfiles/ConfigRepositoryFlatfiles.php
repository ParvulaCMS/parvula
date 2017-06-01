<?php

namespace Parvula\Repositories\Flatfiles;

use Parvula\ArrayTrait;
use Parvula\FileParser;
use Parvula\FilesSystem as Files;
// use Parvula\Models\Config;
use Parvula\Collections\Collection;

class ConfigRepositoryFlatfiles extends BaseRepositoryFlatfiles
{
	/**
	 * @var Parvula\FileParser
	 */
	private $parser;

	/**
	 * @var string
	 */
	private $folder;

	private $extension = '.yml';

	/**
	 * @param FileParser $parser
	 * @param string     $configsFolder
	 */
	public function __construct(FileParser $parser, $configsFolder) {
		$this->parser = $parser;
		$this->folder = $configsFolder;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function model() {
		return null;
	}

	/**
	 * Find by field
	 * @return Model
	 */
	public function find($name) {
		if (!is_file($path = $this->folder . basename($name . $this->extension))) {
			return false;
		}

		return (array) $this->parser->read($path);
	}

	/**
	 * Update
	 *
	 * @param string $name
	 * @param mixed $data Data
	 * @return bool
	 */
	public function update($name, array $data) {
		return $this->parser->write($this->folder . $name . $this->extension, $data);
	}

	/**
	 * Create
	 *
	 * @param array $data
	 * @return bool
	 */
	public function create(array $data) {
		$name = trim($data['name']);
		return $this->parser->write($this->folder . $name . $this->extension, $data['data']);
	}

	/**
	 * Delete
	 *
	 * @param string $name Config name
	 * @return bool
	 */
	public function delete($name) {
		$fs = new Files($this->folder);
		$filename = $name . $this->extension;
		return $fs->delete($filename);
	}
}
