<?php

namespace Parvula;

use Psr\Container\ContainerInterface;

// DEV TODO
class Cache implements ContainerInterface
{
	private $filename;
	private $data;

	public function __construct($filename) {
		$this->filename = $filename;
	}

	private function readData() {
		if (!isset($this->data)) {
			if (file_exists($this->filename)) {
				$raw = file_get_contents($this->filename);
				$this->data = json_decode($raw, true);

				return true;
			}

			return false;
		}
	}

	/**
	 * Finds an entry of the container by its identifier and returns it.
	 *
	 * @param string $id identifier of the entry to look for
	 *
	 * @throws NotFoundException  no entry was found for this identifier
	 * @throws ContainerException error while retrieving the entry
	 *
	 * @return mixed entry
	 */
	public function get($id) {
		$this->readData();

		if (!isset($this->data[$id])) {
			throw new NotFoundException('No entry was found for this identifier.');
		}

		return $this->data[$id];
	}

	/**
	 * Returns true if the container can return an entry for the given identifier.
	 * Returns false otherwise.
	 *
	 * @param string $id identifier of the entry to look for
	 *
	 * @return bool
	 */
	public function has($id) {
		$this->readData();

		return isset($this->data[$id]);
	}

	/**
	 * Set a value.
	 *
	 * @param string $id
	 * @param mixed  $value
	 */
	public function set($id, $value): void {
		$this->readData();

		$this->data[$id] = $value;

		$ser = json_encode($this->data);
		file_put_contents($this->filename, $ser);
	}
}
