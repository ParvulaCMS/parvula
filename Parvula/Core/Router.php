<?php

namespace Parvula\Core;

/**
 * A quick, simple router
 *
 * @package Parvula
 * @version 0.5.0
 * @since 0.3.0
 * @author Fabien Sa
 * @license MIT License
 */
class Router {

	/**
	 * @var \SplDoublyLinkedList Routes
	 */
	private $routes;

	/**
	 * @var string URI
	 */
	private $uri;

	/**
	 * @var string Method
	 */
	private $method;

	/**
	 * @var string Prefix used for groups
	 */
	private $prefix;

	/**
	 * Constructor
	 * @param string $uri (optional) If you want to override server URI
	 * @param string $method (optional) If you want to override server method
	 */
	public function __construct() {
		$this->routes = new \SplDoublyLinkedList;
		$this->prefix = '';
	}

	/**
	 * Get the current method (GET, POST, PUT, ...)
	 * @return String Current method
	 */
	public function getMethod() {
		return $this->method;
	}

	/**
	 * Get the current URI (/my/uri)
	 * @return String Current URI
	 */
	public function getUri() {
		return $this->uri;
	}

	/**
	 * Add a new route with "GET" method
	 * @param string $path
	 * @param function $callback
	 * @return Router Self
	 */
	public function get($path, callable $callback) {
		return $this->on('GET', $path, $callback);
	}

	/**
	 * Add a new route with "POST" method
	 * @param string $path
	 * @param function $callback
	 * @return Router Self
	 */
	public function post($path, callable $callback) {
		return $this->on('POST', $path, $callback);
	}

	/**
	 * Add a new route with "PUT" method
	 * @param string $path
	 * @param function $callback
	 * @return Router Self
	 */
	public function put($path, callable $callback) {
		return $this->on('PUT', $path, $callback);
	}

	/**
	 * Add a new route with "DELETE" method
	 * @param string $path
	 * @param function $callback
	 * @return Router Self
	 */
	public function delete($path, callable $callback) {
		return $this->on('DELETE', $path, $callback);
	}


	/**
	 * Add a new route with all method
	 * @param string $path
	 * @param function $callback
	 * @return Router Self
	 */
	public function any($path, callable $callback) {
		return $this->on('*', $path, $callback);
	}

	/**
	 * Add a new route
	 * @param string $method Method name (`*` for all methods)
	 * @param string $path
	 * @param function $callback
	 * @return Router Self
	 */
	public function on($method, $path, callable $callback) {
		$this->routes->push([
			"method" => $method,
			"path" => $this->prefix . $path,
			"callback" => $callback
		]);

		return $this;
	}

	/**
	 * Group, to prefix a group of routes
	 * @param string $prefix
	 * @param function $callback
	 * @return Router Self
	 */
	public function group($prefix, callable $callback) {
		$that = clone $this;
		$that->prefix = $prefix;
		$callback($that);

		return $that;
	}

	/**
	 * Run the router
	 * @return mixed
	 */
	public function run($method, $uri) {
		$this->method = $method;
		$this->uri = $uri;

		$this->routes->rewind();
		return $this->dispatch();
	}

	/**
	 * Dispatch
	 * @return mixed
	 */
	private function dispatch() {
		// for each route
		for (; $this->routes->valid(); $this->routes->next()) {
			$route = $this->routes->current();

			// if method is OK
			if(strtoupper($route['method']) === $this->method || $route['method'] === '*') {

				$regex = $this->normalizeRegex($route['path']);

				// if path is OK
				if(preg_match("@^{$regex}@", $this->uri, $matches)) {
					$callback = $route['callback'];

					$req = new \ArrayObject;
					$req->params = (object)$matches;
					$req->uri = $this->uri;
					$req->uri = $this->uri;
					$req->query = $this->query;

					if($this->method !== 'GET') {
						if(!isset($data)) {
							parse_str(file_get_contents("php://input"), $data);
						}
						$req->body = $data;
					}

					$that = $this;
					$next = function() use ($that) {
						$that->routes->next();
						return $that->dispatch();
					};

					return $callback($req, $next);
				}
			}
		}
	}

	/**
	 * Normalize regex
	 * @param string $regex Regex to normalize
	 * @return string Normalized regex
	 */
	private function normalizeRegex($regex) {
		$regex = preg_replace("/\::(\w+)/", "(?P<$1>.+)", $regex);
		$regex = preg_replace("/\:(\w+)/", "(?P<$1>[^\/]+)", $regex); // match segments
		$regex = str_replace("*", "(.*)", $regex);

		return $regex;
	}

}
