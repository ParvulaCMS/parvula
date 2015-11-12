<?php

namespace Parvula\Core\PageRenderer;

use Parvula\Core\Model\Page;
use Parvula\Core\Parser\ParserInterface;
use Parvula\Core\ContentParser\ContentParserInterface;

/**
 * Default page renderer
 */
class SimplePageRenderer implements PageRendererInterface {

	/**
	 * @var ParserInterface
	 */
	private $parser;

	/*
	 * @var ContentParserInterface
	 */
	private $contentParser;

	public function __construct(ParserInterface $parser, ContentParserInterface $contentParser) {
		$this->parser = $parser;
		$this->contentParser = $contentParser;
	}

	/**
	 * Render page to string
	 *
	 * @param Page $page
	 * @return string
	 */
	public function render(Page $page) {
		if(!isset($page->title)) {
			throw new PageException('Page MUST have a `title` to be serialized');
		}

		$content = $page->content;
		unset($page->content); // No content in the header

		// Create the header
		$header = str_repeat('-', 3) . PHP_EOL;
		$header .= trim($this->parser->encode((array) $page));
		$header .= PHP_EOL . str_repeat('-', 3) . PHP_EOL . PHP_EOL;

		return $header . $content;
	}

	/**
	 * Fetch raw data to create a page object
	 *
	 * @param mixed $data Data using to create the page
	 * @param array ($options) default page field(s)
	 * @return Page
	 */
	public function fetch($data, array $options = []) {

		$pageInfos = preg_split('/\s[-=]{3,}\s+/', ltrim($data), 2);
		$headerData = trim($pageInfos[0]);

		$pageInfo = $this->parser->decode($headerData);

		$pageInfo['content'] = '';
		if (!empty($pageInfos[1])) {
			$content = $this->contentParser->parse($pageInfos[1]);
			$pageInfo['content'] = $content;
		}

		// Append $options to $pageInfo
		$pageInfo = $pageInfo + $options;

		if (!isset($pageInfo['slug'])) {
			throw new InvalidArgumentException('data MUST have the `slug` field');
		}

		return Page::pageFactory($pageInfo);
	}

}
