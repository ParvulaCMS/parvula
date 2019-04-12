<?php

namespace Parvula\Http;

use Psr\Http\Message\ResponseInterface;

/**
 * API Render.
 *
 * @version 0.6.0
 * @since 0.6.0
 * @author Fabien Sa
 * @license MIT License
 */
class APIResponse
{
	/**
	 * Output rendered template.
	 *
	 * @param  ResponseInterface $response
	 * @param  array|object      $data     Associative array of data to be returned
	 * @param  int               $status   HTTP status code
	 * @return ResponseInterface
	 */
	public function json(ResponseInterface $response, $data = [], int $status = 200) {
		return $response
			->withStatus($status)
			->withHeader('Content-Type', 'application/json')
			->write(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
	}
}
