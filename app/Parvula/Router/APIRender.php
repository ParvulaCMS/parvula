<?php

namespace Parvula\Router;

use Parvula\Parsers\ParserInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * API Render
 *
 * @package Parvula
 * @version 0.6.0
 * @since 0.6.0
 * @author Fabien Sa
 * @license MIT License
 */

class APIRender {

	/**
	 * Output rendered template
	 *
	 * @param  ResponseInterface $response
	 * @param  array $data Associative array of data to be returned
	 * @param  int $status HTTP status code
	 * @return ResponseInterface
	 */
	public function json(ResponseInterface $res, $data = [], $status = 200) {
		return $res
			->withStatus($status)
			->withHeader('Content-Type', 'application/json')
			->write(json_encode($data, JSON_PRETTY_PRINT));
	}
}
