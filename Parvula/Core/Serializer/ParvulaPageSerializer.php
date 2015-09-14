<?php

namespace Parvula\Core\Serializer;

use Parvula\Core\Page;
use Parvula\Core\Exception\PageException;

/**
 * ParvulaPageSerializer class
 *
 * @package Parvula
 * @version 0.5.0
 * @since 0.2.0
 * @author Fabien Sa
 * @license MIT License
 */
class ParvulaPageSerializer implements PageSerializerInterface {

	/**
	 * Serialize page
	 * @param Page $page
	 * @throws PageException if $page does not have `title`
	 * @return boolean
	 */
	public function serialize(Page $page) {
		if(!isset($page->title)) {
			throw new PageException('Page MUST have a `title` to be serialized');
		}

		// Create the header
		$header = PHP_EOL;
		foreach ($page as $field => $value) {
			if($field !== 'content') {
				if(isset($page->{$field})) {
					if(is_array($value)) {
						$field .= '[]';
						$value = implode($value);
					}

					$header .= $field . ': ' . $value . PHP_EOL;
				}
			}
		}
		$header .= PHP_EOL . str_repeat('-', 5) . PHP_EOL . PHP_EOL;

		return $header . $page->content;
	}

	/**
	 * Unserialize data
	 * @param string $data
	 * @param array $options Default page field(s), must have the `slug` field
	 * @throws InvalidArgumentException if $options does not have field `slug`
	 * @return Page
	 */
	public function unserialize($data, array $options = []) {
		if (!isset($options['slug'])) {
			throw new \InvalidArgumentException('$options MUST have the `slug` field');
		}

		$pageInfos = preg_split("/\s[-=]{3,}\s+/", $data, 2);

		$headerStr = trim($pageInfos[0]);
		preg_match_all("/(\w+(?:\[\])?)[\s:=]+(.+)/", $headerStr, $headerMatches);

		$pageInfo = ['content' => $pageInfos[1]];

		for ($i = 0; $i < count($headerMatches[1]); ++$i) {
			$key = trim($headerMatches[1][$i]);
			$key = strtolower($key);
			$val = rtrim($headerMatches[2][$i], "\r\n");
			if(strlen($key) > 2 && substr($key, -2) === '[]') {
				$val = preg_split("/[\s,]+/", $val);
			}
			$pageInfo[$key] = $val;
		}

		// Use the `slug` field from $options
		unset($pageInfo['slug']);

		// Append $options to $pageInfo
		$pageInfo = $pageInfo + $options;

		return Page::pageFactory($pageInfo);
	}

}
