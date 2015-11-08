<?php

// TODO Doc

namespace Parvula\Core;

use Parvula\Core\Parser\ParserInterface;
use Parvula\Core\FilesSystem as Files;
use Parvula\Core\IOInterface;

/**
 * File Parser
 *
 * @package Parvula
 * @version 0.5.0
 * @since 0.5.0
 * @author Fabien Sa
 * @license MIT License
 */
class FileParser implements IOInterface
{

	/**
	 * @var array<string, ParserInterface>
	 */
	private $parsers;

	/**
	 * @var string Prefix paths
	 */
	private $folder;

	/**
	 * Constructor
	 *
	 * Example:
	 * `new FileParser(['json' => new JsonParser, 'yaml' => new Yaml]);`
	 *
	 * @param array $parser Extensions associated to the right parser
	 *                      (the parser must implements ParserInterface)
	 * @param string $folder To prefix all paths
	 */
	public function __construct(array $parsers, $folder = '') {
		$this->parsers = $parsers;
		$this->folder = $folder;
	}

	public function read($filePath) {
		if (!is_file($this->folder. $filePath)) {
			return false;
		}

		$parser = $this->getParser($this->folder. $filePath);

		// Read method
		if (isset($parser->include) && $parser->include === true) {
			$raw = require $this->folder. $filePath;
		} else {
			$raw = file_get_contents($this->folder. $filePath);
		}

		return $this->decode($parser, $raw);
	}

	public function write($filePath, $data) {
		// if (!is_file($this->folder . $filePath)) {
			// return false;
		// }

		$parser = $this->getParser($this->folder . $filePath);

		$dataStr = $this->encode($parser, $data);

		return file_put_contents($this->folder . $filePath, $dataStr);
	}

	public function delete($filePath) {
		return unlink($this->folder . $filePath); // TODO or empty file ?
	}

	/**
	 * TODO
	 * @throws ParseException
	 */
	private function getParser($filePath) {

		$ext = pathinfo($this->folder. $filePath, PATHINFO_EXTENSION);

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
