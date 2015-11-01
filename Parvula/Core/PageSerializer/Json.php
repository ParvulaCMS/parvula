<?php

namespace Parvula\Core\PageSerializer;

use Parvula\Core\Model\Page;

/**
 * JsonPageSerializer class
 *
 * @package Parvula
 * @version 0.3.0
 * @since 0.2.0
 * @author Fabien Sa
 * @license MIT License
 */
class Json implements PageSerializerInterface {

	/**
	 * Serialize page
	 * @param Page $page
	 * @return boolean
	 */
	public function serialize(Page $page) {
		return json_encode($page);
	}

	/**
	 * Unserialize data
	 * @param string $data
	 * @return Page
	 */
	public function unserialize($filePath, $data = null) {
		if($data === null) {
			$data = $filePath;
			$filePath = '';
		}

		$dataArr = json_decode($data);

		$page = Page::pageFactory((array)$dataArr);

		$page->url = ltrim($filePath, '/');
		// $page->content = $headerInfos[1];
		//
		// print_r($page);

		return $page;
	}

}
