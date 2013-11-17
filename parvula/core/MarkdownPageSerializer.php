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
		$header .= isset($page->title) ? 'title: ' . $page->title : ''; // Error if no title ? TOTO
		$header .= isset($page->description) ? 'description: ' . $page->description : '';
		$header .= isset($page->author) ? 'author: ' . $page->author : '';
		$header .= isset($page->date) ? 'date: ' . $page->date : '';
		$header .= isset($page->robots) ? 'robots: ' . $page->robots : '';

		$header .= PHP_EOL . PHP_EOL . str_repeat('-', 5) . PHP_EOL . PHP_EOL;

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

		$infos = preg_split("/\s[-=]{3,}\s+/", $data, 2);

		$headerData = trim($infos[0]);
		preg_match_all("/(\w+)[\s:=]+(.+)/", $headerData, $headerMatches);

		$page = new Page();
		// Set page headers fields
		$page->url = $filePath;
		for ($i = 0; $i < count($headerMatches[1]); ++$i) {
			$key = trim($headerMatches[1][$i]);
			$page->{$key} = rtrim($headerMatches[2][$i], "\r\n");
		}

		$page->content = $infos[1];

		return $page;
	}

}
