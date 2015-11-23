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

	public $params;

	public $cookie;

	/**
	 * @var array
	 */
	public $files;

	/**
	 * @var string
	 */
	public $uri;

	/**
	 * @var string
	 */
	public $method;

	/**
	 * @var string Scheme (without the `://`)
	 */
	public $scheme;

	/**
	 * @var bool If server use a cryptographic protocol (like TLS/SSL)
	 */
	public $secureLayer;

	/**
	 * @var string User agent
	 */
	public $userAgent;

	public $scriptName;


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

		// Client IP
		$this->ip = isset($server['REMOTE_ADDR']) ? $server['REMOTE_ADDR'] : '';

		// Query
		isset($server['QUERY_STRING']) ? parse_str($server['QUERY_STRING'], $this->query) : '';
		$this->query = (object) $this->query;

		$this->host = isset($server['HTTP_HOST']) ? $server['HTTP_HOST'] : '';

		// Script path
		$this->scriptName = isset($server['SCRIPT_NAME']) ? $server['SCRIPT_NAME'] : __FILE__;

		// HTTP method (verb)
		$this->method = isset($server['REQUEST_METHOD']) ? $server['REQUEST_METHOD'] : 'GET';

		$this->uri = isset($server['REQUEST_URI']) ? $server['REQUEST_URI'] : '';

		// http, https, ...
		$this->scheme = isset($server['REQUEST_SCHEME']) ? $server['REQUEST_SCHEME'] : '';

		$this->secureLayer = isset($server['HTTPS']) ? $server['HTTPS'] === 'on' : false;

		$this->userAgent = isset($server['HTTP_USER_AGENT']) ? $server['HTTP_USER_AGENT'] : '';

		// Body
		$this->body = (object) $post;

		$this->cookie = $cookie;

		// Handle "multipart/form-data" (often $_FILES)
		$this->files = $files;
	}

}
