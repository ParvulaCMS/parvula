<?php

namespace Parvula;

/**
 * IOInterface
 *
 * @package Parvula
 * @version 0.5.0
 * @since 0.5.0
 * @author Fabien Sa
 * @license MIT License
 */
interface IOInterface {

	/**
	 * Read ressource
	 *
	 * @param string $path Path
	 * @return mixed Content of a specific ressource
	 */
	public function read($path);

	/**
	 * Write ressource
	 *
	 * @param string $path Path
	 * @param mixed $data Data to write
	 * //@param mixed $flag (optinal) Flag
	 * //@throws Exception If the ressource does not exists ??
	 * @return bool If the data was writen
	 */
	public function write($path, $data);
}
