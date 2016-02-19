<?php

namespace Parvula\Core\PageRenderer;

use Parvula\Core\Model\Page;
use Parvula\Core\Parser\ParserInterface;
use Parvula\Core\Exception\PageException;
use Parvula\Core\ContentParser\ContentParserInterface;

/**
 * Parvula page renderer
 * This renderer allow you to create sections
 *
 * Page example:
 * ```
 * ---
 * [front matter...]
 * ---
 *
 * [content...]
 *
 * ---
 * My section
 * ---
 * Section content...
 * ```
 *
 * You can then access this section with `$page->sections->{'My section'};`
 */
class FlatFilesPageRenderer implements PageRendererInterface {
// class ParvulaPageRenderer extends SimplePageRenderer implements PageRendererInterface {

	/**
	 * @var ParserInterface
	 */
	protected $metadataParser;

	/**
	 * @var ContentParserInterface
	 */
	protected $contentParser;

	/**
	 * @var string Regexp to match front matter (preg_split)
	 */
	protected $delimiterMatcher;

	/**
	 * @var string Regexp to match sections (preg_split)
	 */
	protected $sectionMatcher;

	/**
	 * @var string To delimite front matter and sections
	 */
	protected $delimiterRender;

	/**
	 * Constructor
	 * Available $options keys are delimiterMatcher, sectionMatcher and delimiterRender
	 *
	 * @param ParserInterface $metadataParser
	 * @param ContentParserInterface $contentParser
	 * @param array $options
	 */
	public function __construct(
		ParserInterface $metadataParser, ContentParserInterface $contentParser, $options = []) {
		$this->metadataParser = $metadataParser;
		$this->contentParser = $contentParser;

		$defaultOptions = [
			'delimiterMatcher' => '/\s-{3,}\s+/',
			'sectionMatcher' => '/-{3}\s+(\w[\w- ]*?)\s+-{3}/',
			'delimiterRender' => '---'
		];

		$options += $defaultOptions;

		$this->delimiterMatcher = $options['delimiterMatcher'];
		$this->sectionMatcher = $options['sectionMatcher'];
		$this->delimiterRender = $options['delimiterRender'];
	}

	/**
	 * Render page to string
	 *
	 * @param Page $page
	 * @return string Rendered page
	 */
	public function render(Page $page) {
		if (!isset($page->title)) {
			throw new PageException('Page MUST have a `title` to be serialized');
		}

		$delimiter = $this->delimiterRender . PHP_EOL;

		$metaArr = $page->getMeta();
		if (isset($metaArr['slug'])) {
			// For flat files DB, the slug is the filename thus we don't need the slug
			unset($metaArr['slug']);
		}

		// Create the front matter
		$meta = $delimiter;
		$meta .= trim($this->metadataParser->encode($metaArr));
		$meta .= PHP_EOL . $delimiter . PHP_EOL;

		// Add the content
		$content = trim($page->getContent());

		// Add sections (if exist)
		if (($sections = $page->getSections()) !== false) {
			foreach ($sections as $name => $value) {
				$content .= PHP_EOL . PHP_EOL . $delimiter . $name . PHP_EOL . $delimiter;
				$content .= trim($value) . PHP_EOL;
			}
		}

		return $meta . $content;
	}

	/**
	 * Decode string data to create a page object
	 *
	 * @param mixed $data Data using to create the page
	 * @param array ($options) default page field(s)
	 * @param bool ($parseContent)
	 * @return Page
	 */
	public function parse($data, array $options = [], $parseContent = true) {
		$pageTokens = preg_split($this->delimiterMatcher, ltrim($data), 2);
		$metaRaw = trim($pageTokens[0]);

		$meta = $this->metadataParser->decode($metaRaw);

		$content = '';
		$sections = new \StdClass;
		if (!empty($pageTokens[1])) {
			// Split into sections
			$content = preg_split(
				$this->sectionMatcher, $pageTokens[1] . ' ', -1,
				PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

			if (($len = count($content)) > 1) {
				for ($i = 1; $i < $len; ++$i) {
					if ($i % 2 === 1) {
						$name = $content[$i];
					} else {
						$val = $content[$i];
						if ($parseContent) {
							$sections->{$name} = $this->contentParser->parse($val);
						} else {
							$sections->{$name} = $val;
						}
					}
				}
			}

			// First section is always the main content
			$content = $content[0];
			if ($parseContent) {
				$content = $this->contentParser->parse($content);
			}
		}

		// Append $options to $meta
		$meta += $options;

		return new Page($meta, $content, $sections);
	}

}
