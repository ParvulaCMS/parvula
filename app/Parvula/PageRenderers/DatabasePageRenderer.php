<?php

namespace Parvula\PageRenderers;

use Parvula\Models\Page;
use Parvula\Parsers\ParserInterface;
use Parvula\Exceptions\PageException;
use Parvula\PageRenderers\PageRendererInterface;
use Parvula\ContentParser\ContentParserInterface;

class DatabasePageRenderer implements PageRendererInterface {

	/**
	 * @var ContentParserInterface
	 */
	protected $contentParser;

	/**
	 * Constructor
	 * Available $options keys are delimiterMatcher, sectionMatcher and delimiterRender
	 *
	 * @param ContentParserInterface $contentParser
	 * @param array $options
	 */
	public function __construct(ContentParserInterface $contentParser, $options = []) {
		$this->contentParser = $contentParser;
		$this->options = $options;
	}

	/**
	 * Render Page object to string
	 *
	 * @param Page $page
	 * @return string Rendered page
	 */
	public function render(Page $page) {
		return;
	}

	/**
	 * Decode string data to create a Page object
	 *
	 * @param Page $page Page using to create the page
	 * @param array ($options) default page field(s)
	 * @return Page
	 */
	public function parse($page, array $options = []) {
		$sections = [];
		if (!empty($page->sections)) {
			$page->sections = array_map(function ($section) {
				$section->content = $this->contentParser->parse($section->content);
				return $section;
			}, (array) $page->sections);
		}

		$page->content = $this->contentParser->parse($page->content);

		return new Page((array) $page);
	}
}
