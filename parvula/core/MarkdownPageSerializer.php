<?php

namespace Parvula\Core;

/**
 * MarkdownPageSerializer class
 *
 * @package Parvula
 * @version 0.2.2
 * @since 0.2.0
 * @author Fabien Sa
 * @license MIT License
 */
class MarkdownPageSerializer implements PageSerializerInterface {

	/**
	 * Serialize page
	 * @param Page $page
	 * @return boolean
	 */
	public function serialize(Page $page) {

		$header = PHP_EOL;
		$header .= isset($page->title) ? 'title: ' . $page->title . PHP_EOL : ''; // Error if no title ? TOTO
		$header .= isset($page->description) ? 'description: ' . $page->description . PHP_EOL : '';
		$header .= isset($page->author) ? 'author: ' . $page->author . PHP_EOL : '';
		$header .= isset($page->date) ? 'date: ' . $page->date . PHP_EOL : '';
		$header .= isset($page->robots) ? 'robots: ' . $page->robots . PHP_EOL : '';

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
