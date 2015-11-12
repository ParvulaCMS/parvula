<?php

namespace Parvula\Core\PageRenderer;

use Parvula\Core\Model\Page;

/**
 * PageRenderer interface
 *
 * @package Parvula
 * @version 0.5.0
 * @since 0.1.0
 * @author Fabien Sa
 * @license MIT License
 */
interface PageRendererInterface {

	/**
	 * Render page to string
	 *
	 * @param Page $page
	 * @return string
	 */
	public function render(Page $page);

	/**
	 * Fetch raw data to create a page object
	 *
	 * @param mixed $data Data using to create the page
	 * @param array ($options) default page field(s)
	 * @return Page
	 */
	public function fetch($data, array $options = []);
}
