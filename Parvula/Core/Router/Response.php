<?php

namespace Parvula\Core\Router;

use finfo;

class Response {

	/**
	 * @var integer
	 */
	protected $status = 200;

	/**
	 * @var array
	 */
	protected $headers;

	/**
	 * @param array $headers
	 */
	public function __construct($headers = []) {
		$this->headers = $headers;
	}

	/**
	 * Check if the HTTP headers are already sent
	 *
	 * @return boolean
	 */
	public function headerSent() {
		return headers_sent();
	}

	/**
	 * Set HTTP status
	 * @param integer $status Status code
	 * @return Response
	 */
	public function status($status) {
		$this->status = $status;
		return $this;
	}

	/**
	 * Sends a json response
	 * Same as {send} but encode body to json
	 *
	 * @param mixed $body
	 */
	public function json($body = null) {
		$this->set('Content-Type', 'application/json');
		$this->send(json_encode($body));
	}

	/**
	 * Sends the HTTP response
	 *
	 * @param mixed $body If the body is an array/object it will be encoded to json
	 */
	public function send($body = null) {
		http_response_code($this->status);

		foreach ($this->headers as $field => $value) {
			header($field . ': ' . $value);
		}

		if ($body !== null && !is_scalar($body)) {
			$this->json($body);
		}
		else if ($body !== null) {
			echo $body;
		}
	}

	/**
	 * Send HTTP status without body
	 *
	 * @param integer $status Status code
	 */
	public function sendStatus($status) {
		$this->status($status)->send();
	}

	/**
	 * Location helper
	 *
	 * @param string $uri
	 * @return Response
	 */
	public function location($uri) {
		$this->add('location', $uri);
		return $this;
	}

	/**
	 * Get a specific header
	 *
	 * @param string $field
	 * @return string
	 */
	public function get($field) {
		return $this->headers[$field];
	}

	/**
	 * Appends the specified value to the HTTP response header field.
	 *
	 * @param string $field
	 * @param string $value
	 * @return Response
	 */
	public function set($field, $value) {
		$this->headers[$field] = $value;
		return $this;
	}

	/**
	 * Send file
	 * Sets the Content-Type response based on the filename's extension
	 *
	 * @param string $path File path
	 * @return bool False if file does not exists
	 */
	public function sendFile($path) {
		if (!is_file($path)) {
			return false;
		}

		$info = new finfo(FILEINFO_MIME_TYPE);
		$contentType = $info->file($path);

		$this->set('Content-Type', $contentType)->send();
		readfile($path);
	}

}
