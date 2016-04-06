<?php

namespace Parvula\Parser;

class Php implements ParserInterface {

	public $include = true;

	/**
	 * This method simply return the php
	 *
	 * @param mixed $input
	 * @return mixed (same as $input)
	 */
	public function decode($php) {
		return $php;
	}

	/**
	 * Generate php
	 *
	 * @param array|object $data Data to encode
	 * @param bool $phpTag (optional) If `<?php ` tag needs to be output
	 * @return string The php string
	 */
	public function encode($php, $phpTag = true) {
		return ($phpTag ? '<?php' . PHP_EOL : '') . var_export($php, true) . ';';
	}
}
