<?php

namespace Parvula\PageRenderers;

use Parvula\ContentParser\ContentParserInterface;
use Parvula\Models\Page;

class DatabasePageRenderer implements PageRendererInterface
{
	/**
	 * @var ContentParserInterface
	 */
	protected $contentParser;

	/**
	 * Constructor
	 * Available $options keys are delimiterMatcher, sectionMatcher and delimiterRender.
	 *
	 * @param ContentParserInterface $contentParser
	 * @param array                  $options
	 */
	public function __construct(ContentParserInterface $contentParser, $options = []) {
		$this->contentParser = $contentParser;
		$this->options = $options;
	}

	/**
	 * Render Page object to string.
	 *
	 * @param  Page   $page
	 * @return string Rendered page
	 */
	public function render(Page $page) {
	}

	/**
	 * Decode string data to create a Page object.
	 *
	 * @param \MongoDB\Model\BSONDocument $page Page using to create the page
	 * @param array ($options) default page field(s)
	 * @return Page
	 */
	public function parse($page, array $options = []) {
		$sections = [];
		if (!empty($page->sections)) {
			$page->sections = array_map(function ($section) {
				if (isset($section->content)) {
					$section->content = $this->contentParser->parse($section->content);
				}

				return $section;
			}, (array) $page->sections);
		}

		$page->content = $this->contentParser->parse($page->content);

		return new Page((array) $page);
	}
}
