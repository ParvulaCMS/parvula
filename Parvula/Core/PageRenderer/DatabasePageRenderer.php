<?php

namespace Parvula\Core\PageRenderer;

use Parvula\Core\Model\Page;
use Parvula\Core\Parser\ParserInterface;
use Parvula\Core\Exception\PageException;
use Parvula\Core\PageRenderer\PageRendererInterface;
use Parvula\Core\ContentParser\ContentParserInterface;

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
	 * @param mixed $data Data using to create the page
	 * @param array ($options) default page field(s)
	 * @param bool ($parseContent)
	 * @return Page
	 */
	public function parse($data, array $options = []) {
		$sections = [];
		if (isset($data->sections)) {
			$sections = array_map(function($section) {
				return $this->contentParser->parse($section);
			}, iterator_to_array($data->sections));
		}

		$content = $this->contentParser->parse($data->content);
		$meta = iterator_to_array($data->meta);

		return new Page($meta, $content, $sections);
	}

}
