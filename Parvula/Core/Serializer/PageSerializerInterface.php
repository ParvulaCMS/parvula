<?php

namespace Parvula\Core\Serializer;

use Parvula\Core\Page;

/**
 * PageSerilizer interface
 *
 * @package Parvula
 * @version 0.1.0
 * @since 0.1.0
 * @author Fabien Sa
 * @license MIT License
 */
interface PageSerializerInterface {

	/**
	 * Serialize page
	 * @param Page $page
	 * @return boolean
	 */
	public function serialize(Page $page);

	/**
	 * Unserialize data to get Page
	 * @param string $data
	 * @return Page
	 */
	public function unserialize($data);
}
