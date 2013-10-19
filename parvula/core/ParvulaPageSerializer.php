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
	
	/**
	 * Serialize page
	 * @param Page $page 
	 * @return boolean
	 */
	public function serialize(Page $page) {

		$header = PHP_EOL;
		$header .= 'title: ' . $page->title;
		$header .= 'description' . $page->$description;
		$header .= 'author' . $page->$author;
		$header .= 'date' . $page->$date;
		$header .= 'robots' . $page->$robots;

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

		$infos = preg_split("/\s[-=]{3,}\s/", $data, 2);

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

		$parser = new MarkdownExtra;

		// Bit hacky but works
		$content = str_replace('][img](', '](' . HTML::linkRel('data/images/'), $content);
		$content = str_replace('][page](', '](' . HTML::linkRel(''), $content);

		$page->content = $parser->transform($content);

		return $page;
	}

}
