<?php

namespace Parvula\Core\PageRenderer;

use Parvula\Core\Model\Page;
use Parvula\Core\Parser\ParserInterface;
use Parvula\Core\ContentParser\ContentParserInterface;

/**
 * Parvula page renderer
 * This renderer allow you to create sections
 *
 * Example:
 * ```
 * ...
 * ---
 * My section
 * ---
 * Section content...
 * ```
 *
 * You can access a section with $page->sections->{'My section'};
 */
class ParvulaPageRenderer extends SimplePageRenderer implements PageRendererInterface {

	private $sectionsRegex = '/-{3}\s*(?<LOL>\w[\w- ]*?)\s*-{3}/';

	/**
	 * Render page to string
	 *
	 * @param Page $page
	 * @return string
	 */
	public function encode(Page $page) {
		if (!isset($page->title)) {
			throw new PageException('Page MUST have a `title` to be serialized');
		}

		$limiter = str_repeat('-', 3);

		$content = $page->content;

		// Add sections
		foreach ($page->sections as $name => $value) {
			$content .= $limiter . PHP_EOL . $name . PHP_EOL;
			$content .= $value . PHP_EOL;
		}

		// No content or sections in the header
		unset($page->content);
		unset($page->sections);

		// Create the header
		$header = $limiter . PHP_EOL;
		$header .= trim($this->metadataParser->encode((array) $page));
		$header .= PHP_EOL . $limiter . PHP_EOL . PHP_EOL;

		return $header . $content;
	}

	/**
	 * Decode string data to create a page object
	 *
	 * @param mixed $data Data using to create the page
	 * @param array ($options) default page field(s)
	 * @return Page
	 */
	public function parse($data, array $options = []) {

		$pageInfos = preg_split('/\s[-=]{3,}\s+/', ltrim($data), 2);
		$headerData = trim($pageInfos[0]);

		$pageInfo = $this->metadataParser->decode($headerData);

		$pageInfo['content'] = '';
		if (!empty($pageInfos[1])) {

			// Split into sections
			$content = preg_split(
				$this->sectionsRegex, $pageInfos[1] . ' ', -1,
				PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

			if (($len = count($content)) > 1) {
				$sections = new \StdClass;
				for ($i = 1; $i < $len; ++$i) {
					if ($i % 2 === 1) {
						$name = $content[$i];
					} else {
						$val = $content[$i];
						$sections->{$name} = $this->contentParser->parse($val);
					}
				}

				$pageInfo['sections'] = $sections;
			}

			// First section is always the main content
			$pageInfo['content'] = $this->contentParser->parse($content[0]);
		}

		// Append $options to $pageInfo
		$pageInfo = $pageInfo + $options;

		if (!isset($pageInfo['slug'])) {
			throw new InvalidArgumentException('data MUST have the `slug` field');
		}

		return Page::pageFactory($pageInfo);
	}

}
