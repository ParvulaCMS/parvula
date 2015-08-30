<?php

namespace Parvula\Core\Serializer;

use Parvula\Core\Page;

/**
 * ParvulaPageSerializer class
 *
 * @package Parvula
 * @version 0.3.0
 * @since 0.2.0
 * @author Fabien Sa
 * @license MIT License
 */
class ParvulaJsonPageSerializer implements PageSerializerInterface {

	/**
	 * Serialize page
	 * @param Page $page
	 * @return boolean
	 */
	public function serialize(Page $page) {
		$header = PHP_EOL;

		$content = $page->content;
		unset($page->content);
		unset($page->url);

		$header =  json_encode($page, JSON_PRETTY_PRINT);

		$header .= PHP_EOL . str_repeat('-', 5) . PHP_EOL . PHP_EOL;


		return $header . $content;
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

		$headerInfos = preg_split("/\s[-=]{3,}\s+/", $data, 2);
		$headerData = trim($headerInfos[0]);
		$pageInfo = json_decode($headerData, true);

		$page = new Page();
		$page = Page::pageFactory($pageInfo);

		$page->url = ltrim($filePath, '/');
		if(!empty($headerInfos[1])) {
			$page->content = $headerInfos[1];
		} else {
			$page->content = '';
		}

		// print_r($this->serialize($page));

		return $page;
	}

}
