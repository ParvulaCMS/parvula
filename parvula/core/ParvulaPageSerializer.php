<?php

namespace Parvula\Core;

use \Michelf\MarkdownExtra;

/**
 * ParvulaPageSerializer class
 * 
 * @package Parvula
 * @version 0.1.0
 * @author Fabien Sa
 * @license MIT License
 */
class ParvulaPageSerializer implements PageSerializerInterface {
	
	// Page $page TODO
	public function serialize(Page $page) {

		$header = '';
		$header .= 'title: ' . $page->title;

		$content = json_encode($page->content);

		// return 
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

		$infos = preg_split("/\s[-=]{3,}\s/", $data, 2);
		// print_r($infos);

		$headerData = trim($infos[0]);
		preg_match_all("/(\w+)[\s:=]+(.+)/", $headerData, $headerMatches);

		$page = new Page();
		// Set page headers fields
		$page->url = $filePath;
		for ($i=0; $i < count($headerMatches[1]); ++$i) {
			$key = trim($headerMatches[1][$i]);
			$page->{$key} = rtrim($headerMatches[2][$i], "\r\n");
		}

		$content = $infos[1];

		// $markdownParser = new MarkdownExtraParser();
		// MarkdownExtra::defaultTransform($my_text);
		$page->content = MarkdownExtra::defaultTransform($content);
		// $page->content = $markdownParser->transformMarkdown($content);

		return $page;
	}
}
