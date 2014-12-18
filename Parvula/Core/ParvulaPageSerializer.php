<?php

namespace Parvula\Core;

/**
 * ParvulaPageSerializer class
 *
 * @package Parvula
 * @version 0.3.0
 * @since 0.2.0
 * @author Fabien Sa
 * @license MIT License
 */
class ParvulaPageSerializer implements PageSerializerInterface {

	/**
	 * Serialize page
	 * @param Page $page
	 * @return boolean
	 */
	public function serialize(Page $page) {
		$header = PHP_EOL;
		
		// @TODO Error if no title ?
		foreach ($page as $field => $value) {
			if($field !== 'content') {
				// Create header
				$header .= isset($page->{$field}) ? $field . ': ' . $value . PHP_EOL : '';
			}
		}

		$header .= PHP_EOL . str_repeat('-', 5) . PHP_EOL . PHP_EOL;

		$content = $page->content;

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
		preg_match_all("/(\w+)[\s:=]+(.+)/", $headerData, $headerMatches);

		$page = new Page();

		$pageInfo = array();
		for ($i = 0; $i < count($headerMatches[1]); ++$i) {
			$key = trim($headerMatches[1][$i]);
			$key = strtolower($key);
			$pageInfo[$key] = rtrim($headerMatches[2][$i], "\r\n");
		}

		$page = Page::pageFactory($pageInfo);

		$page->url = $filePath;
		$page->content = $headerInfos[1];

		return $page;
	}

}
