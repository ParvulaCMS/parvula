<?php

namespace Parvula\Core\Router;

/**
 * Request
 *
 * @package Parvula
 * @version 0.5.0
 * @since 0.5.0
 * @author Fabien Sa
 * @license MIT License
 */
class Request
{
	public $body;

	/**
	 * @var string IP address
	 */
	public $ip;

	public $query;

	public  $params;


	/**
	 * Constructor
	 *
	 * @param array $server
	 * @param array $get
	 * @param array $post
	 * @param array $cookie
	 * @param array $files
	 */
	public function __construct(
		array $server,
		array $get,
		array $post,
		array $cookie,
		array $files) {

		// IP
		$this->ip = isset($server['REMOTE_ADDR']) ? $server['REMOTE_ADDR'] : '';

		// Query
		isset($server['QUERY_STRING']) ? parse_str($server['QUERY_STRING'], $this->query) : '';
		$this->query = (object) $this->query;

		// Body
		$this->body = (object) $post;
	}

}
