<?php

namespace Parvula\Core\Serializer;

use Parvula\Core\Page;

/**
 * ParvulaPageSerializer class
 *
 * @package Parvula
 * @version 0.5.0
 * @since 0.2.0
 * @author Fabien Sa
 * @license MIT License
 */
class ParvulaJson implements PageSerializerInterface {

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
	 * @param array $options Default page field(s), must have the `slug` field
	 * @return Page
	 */
	public function unserialize($data, array $options = []) {
		$pageInfos = preg_split("/\s[-=]{3,}\s+/", $data, 2);
		$headerData = trim($pageInfos[0]);
		$pageInfo = json_decode($headerData, true);

		if(!empty($pageInfos[1])) {
			$pageInfo['content'] = $pageInfos[1];
		} else {
			$pageInfo['content'] = '';
		}

		// Append $options to $pageInfo
		$pageInfo = $pageInfo + $options;

		return Page::pageFactory($pageInfo);
	}

}
