<?php

namespace App\Repositories\Flatfiles;

use Parvula\FileParser;
use Parvula\FilesSystem as Files;
use Parvula\Models\Config;

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

	/**
	 * @var string
	 */
	private $extension;

	/**
	 * @param FileParser $parser
	 * @param string     $configsFolder
	 * @param string     $extension optional extension (default 'yml')
	 */
	public function __construct(FileParser $parser, $configsFolder, $extension = 'yml') {
		$this->parser = $parser;
		$this->folder = $configsFolder;
		$this->extension = '.' . ltrim($extension, ".\t\n\r\0 ");
	}

	/**
	 * {@inheritDoc}
	 */
	protected function model() {
		return Config::class;
	}

	/**
	 * Find by field
	 * @return Config
	 */
	public function find($name) {
		if (!is_file($path = $this->folder . basename($name . $this->extension))) {
			return false;
		}

		return new Config($this->parser->read($path));
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
