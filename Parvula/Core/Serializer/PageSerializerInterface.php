<?php

namespace Parvula\Core\Serializer;

use Parvula\Core\Page;

/**
 * PageSerializer interface
 *
 * @package Parvula
 * @version 0.5.0
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
	 * @param mixed $data Data using to create the page
	 * @param array ($options) default page field(s)
	 * @return Page
	 */
	public function unserialize($data, array $options = []);
}
