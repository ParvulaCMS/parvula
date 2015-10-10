<?php

namespace Parvula\Core;

use Parvula\Core\ContainerInterface;
use Parvula\Core\Exception\NotFoundException;

class Container
{
	/**
	 * @var array Instances
	 */
	private $instances = [];

	/**
	 * @var array Shared instances (singleton)
	 */
	private $sharedInstances = [];

	/**
	 * Finds an entry of the container by its identifier and returns it
	 *
	 * @param string $id Identifier of the entry to look for
	 * @throws NotFoundException No entry was found for this identifier
	 * @return mixed Entry
	 */
	public function get($id) {
		if(isset($this->sharedInstances[$id])) {
			if($this->sharedInstances[$id] === true) {
				$this->sharedInstances[$id] = $this->instances[$id]();
			}

			return $this->sharedInstances[$id];
		}
		else if (isset($this->instances[$id])) {
			return $this->instances[$id]();
		}

		throw new NotFoundException(
			sprintf('Instance of (%s) was not found in the container', $id)
		);
	}

	/**
	 * Add an entry to the container
	 *
	 * @param string $id Identifier of the entry to look for
	 * @param Closure $closure
	 */
	public function add($id, \Closure $closure) {
		$this->instances[$id] = $closure;
	}

	/**
	 * Add a shared entry to the container (singleton)
	 *
	 * @param string $id Identifier of the entry to look for
	 * @param Closure $closure
	 */
	public function share($id, \Closure $closure) {
		$this->sharedInstances[$id] = true;
		$this->instances[$id] = $closure;
	}

	/**
	 * Returns true if the container can return an entry for the given identifier
	 * Returns false otherwise
	 *
	 * @param string $id Identifier of the entry to look for
	 * @return boolean
	 */
	public function has($id) {
		return isset($this->instances[$id]) && $this->instances[$id] !== null;
	}
}
