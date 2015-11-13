<?php

namespace Parvula\Core\PageRenderer;

use Parvula\Core\Model\Page;
use Parvula\Core\Parser\ParserInterface;
use Parvula\Core\ContentParser\ContentParserInterface;

/**
 * PageRenderer interface
 *
 * @package Parvula
 * @version 0.5.0
 * @since 0.1.0
 * @author Fabien Sa
 * @license MIT License
 */
abstract class AbstractPageRenderer implements PageRendererInterface {

	/**
	 * @var ParserInterface
	 */
	protected $metadataParser;

	/*
	 * @var ContentParserInterface
	 */
	protected $contentParser;

	abstract public function __construct(
		ParserInterface $metadataParser, ContentParserInterface $contentParser);

}
