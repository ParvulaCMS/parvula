<?php

// TODO Doc

namespace Parvula\Core\Model;

use Parvula\Core\Parser\ParserInterface;
use Parvula\Core\FilesSystem as Files;
use Parvula\Core\Config;

/**
 * File Parser
 *
 * @package Parvula
 * @version 0.5.0
 * @since 0.5.0
 * @author Fabien Sa
 * @license MIT License
 */
class FileParser implements CRUDInterface
{

	/**
	 * @var array<string, ParserInterface>
	 */
	private $parsers;

	/**
	 * Constructor
	 *
	 * Example:
	 * `new FileParser(['json' => new JsonParser, 'yaml' => new Yaml]);`
	 *
	 * @param array $parser Extensions associated to the right parser
	 *                      (the parser must implements ParserInterface)
	 */
	public function __construct(array $parsers) {
		$this->parsers = $parsers;
	}

	public function read($filePath) {
		if (!is_file($filePath)) {
			return false;
		}

		$parser = $this->getParser($filePath);

		// Read method
		if (isset($parser->include) && $parser->include === true) {
			$raw = require $filePath;
		} else {
			$raw = file_get_contents($filePath);
		}

		return $this->decode($parser, $raw);
	}

	public function index() {
		return false;
	}

	public function update($filePath, $data) {
		if (!is_file($filePath)) {
			return false;
		}

		$parser = $this->getParser($filePath);

		$dataStr = $this->encode($parser, $data);

		return file_put_contents($filePath, $dataStr);
	}

	public function create($data) {
		return false;
	}

	public function delete($filePath) {
		return unlink($filePath); // TODO or empty file ?
	}

	/**
	 * TODO
	 * @throws ParseException
	 */
	private function getParser($filePath) {

		$ext = pathinfo($filePath, PATHINFO_EXTENSION);

		if (!isset($this->parsers[$ext])) {
			throw new ParseException('`' . htmlspecialchars($ext) . '` files cannot be parsed');
			return;
		}

		return $this->parsers[$ext];
	}

	private function decode(ParserInterface $parser, $raw) {
		return $parser->decode($raw);
	}

	private function encode(ParserInterface $parser, $data) {
		return $parser->encode($data);
	}
}
