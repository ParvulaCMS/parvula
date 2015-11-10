<?php

namespace Parvula\Core\PageSerializer;

use InvalidArgumentException;
use Parvula\Core\Model\Page;
use Parvula\Core\Parser\Yaml;

/**
 * ParvulaPageSerializer class
 *
 * @package Parvula
 * @version 0.5.0
 * @since 0.5.0
 * @author Fabien Sa
 * @license MIT License
 */
class ParvulaYaml implements PageSerializerInterface {

	/**
	 * Serialize page
	 *
	 * @param Page $page
	 * @return boolean
	 */
	public function serialize(Page $page) {
		if(!isset($page->title)) {
			throw new PageException('Page MUST have a `title` to be serialized');
		}

		$content = $page->content;
		unset($page->content); // No content in the header

		// Create the header
		$header = str_repeat('-', 3) . PHP_EOL;
		$header .= trim((new Yaml)->encode((array) $page));
		$header .= PHP_EOL . str_repeat('-', 3) . PHP_EOL . PHP_EOL;

		return $header . $content;
	}

	/**
	 * Unserialize data
	 *
	 * @param string $data
	 * @param array $options Default page field(s), must have the `slug` field
	 * @return Page
	 */
	public function unserialize($data, array $options = []) {
		if (!isset($options['slug'])) {
			throw new InvalidArgumentException('$options MUST have the `slug` field');
		}

		$pageInfos = preg_split("/\s[-=]{3,}\s+/", ltrim($data), 2);
		$headerData = trim($pageInfos[0]);

		$pageInfo = (new Yaml)->decode($headerData);

		$pageInfo['content'] = '';
		if (!empty($pageInfos[1])) {
			$pageInfo['content'] = $pageInfos[1];
		}

		// Append $options to $pageInfo
		$pageInfo = $pageInfo + $options;

		return Page::pageFactory($pageInfo);
	}

}
