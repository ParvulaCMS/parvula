<?php

namespace Parvula\PageRenderer;

use Parvula\Model\Page;
use Parvula\Parser\ParserInterface;
use Parvula\Exception\PageException;
use Parvula\PageRenderer\PageRendererInterface;
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
	public function __construct(
		ContentParserInterface $contentParser, $options = []) {
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
		if (!empty($page->sections)) {
			$sections = array_map(function ($section) {
				$section->content = $this->contentParser->parse($section->content);
				return $section;
			}, $page->sections);
		}

		$content = $this->contentParser->parse($page->content);
		$meta = iterator_to_array($page->meta);

		return new Page($meta, $content, $sections);
	}

}
