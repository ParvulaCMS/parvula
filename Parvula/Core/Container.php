<?php

namespace Parvula\Core;

use Closure;
use ArrayAccess;
use Parvula\Core\ContainerInterface;
use Parvula\Core\Exception\NotFoundException;

/**
 * Container
 *
 * @package Parvula
 * @version 0.5.0
 * @since 0.5.0
 * @author Fabien Sa
 * @license MIT License
 */
class Container implements ArrayAccess
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
		if (isset($this->sharedInstances[$id])) {
			if ($this->sharedInstances[$id] === true) {
				$this->sharedInstances[$id] = $this->instances[$id]($this);
			}

			return $this->sharedInstances[$id];
		}
		else if (isset($this->instances[$id])) {
			return $this->instances[$id]($this);
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
	public function add($id, Closure $closure) {
		$this->instances[$id] = $closure;
	}

	/**
	 * Add a shared entry to the container (singleton)
	 *
	 * @param string $id Identifier of the entry to look for
	 * @param Closure $closure
	 */
	public function share($id, Closure $closure) {
		$this->sharedInstances[$id] = true;
		$this->instances[$id] = $closure;
	}

	/**
	 * Remove entry from the container
	 *
	 * @param string $id Identifier of the entry to look for
	 */
	public function remove($id) {
		if ($this->has($id)) {
			if (isset($this->sharedInstances)) {
				unset($this->sharedInstances);
			}

			unset($this->instance[$id]);
		}
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

	/**
	 * Alias for `get`
	 *
	 * @see Container::get
	 */
	public function offsetGet($id) {
		return $this->get($id);
	}

	/**
	 * Alias for `add`
	 *
	 * @see Container::add
	 */
	public function offsetSet($id, $closure) {
		$this->add($id, $closure);
	}

	/**
	 * Alias for `has`
	 *
	 * @see Container::has
	 */
	public function offsetExists($id) {
		return $this->has($id);
	}

	/**
	 * Alias for `remove`
	 *
	 * @see Container::remove
	 */
	public function offsetUnset($id) {
		$this->remove($id);
	}
}
