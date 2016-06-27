<?php

namespace Parvula;

use Parvula\Parsers\ParserInterface;
use Parvula\FilesSystem as Files;
use Parvula\IOInterface;
use Parvula\Exceptions\ParseException;

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
	protected $parsers;

	/**
	 * @var string Prefix paths
	 */
	protected $folder;

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

	/**
	 * Read the given file
	 *
	 * @param  string $filePath File path
	 * @return mixed Returns data if the file was parsed, false on failure
	 */
	public function read($filePath) {
		if (!is_file($this->folder . $filePath)) {
			return false;
		}

		$parser = $this->getParser($this->folder. $filePath);

		// Read method
		if (isset($parser->include) && $parser->include) {
			$raw = require $this->folder . $filePath;
		} else {
			$raw = file_get_contents($this->folder . $filePath);
		}

		return $this->decode($parser, $raw);
	}

	/**
	 * Write the given data to filePath
	 *
	 * @param  string $filePath File path
	 * @param  mixed $data Data
	 * @return int|bool Returns the number of bytes that were written or false on failure
	 */
	public function write($filePath, $data) {
		// if (!is_file($this->folder . $filePath)) {
			// return false;
		// }

		$parser = $this->getParser($this->folder . $filePath);

		$dataStr = $this->encode($parser, $data);

		return file_put_contents($this->folder . $filePath, $dataStr);
	}

	/**
	 * Get the right parser (given the file extension)
	 *
	 * @param  string $filePath File path
	 * @throws ParseException If file type cannot be parsed
	 * @return ParserInterface Parser
	 */
	private function getParser($filePath) {

		$ext = pathinfo($this->folder. $filePath, PATHINFO_EXTENSION);

		if (!isset($this->parsers[$ext])) {
			throw new ParseException('`' . htmlspecialchars($ext) . '` files cannot be parsed');
			return;
		}

		return $this->parsers[$ext];
	}

	/**
	 * Decode serialized data
	 *
	 * @param  ParserInterface $parser
	 * @param  string $raw Raw data
	 * @return mixed Decoded raw data
	 */
	private function decode(ParserInterface $parser, $raw) {
		return $parser->decode($raw);
	}

	/**
	 * Encode structured data
	 *
	 * @param  ParserInterface $parser
	 * @param  mixed $data Structured data
	 * @return string Encoded data
	 */
	private function encode(ParserInterface $parser, $data) {
		return $parser->encode($data);
	}
}
