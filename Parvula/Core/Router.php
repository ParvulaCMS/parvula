<?php

namespace Parvula\Core;

/**
 * A quick, simple router
 *
 * @package Parvula
 * @since 0.3.0
 * @author Fabien Sa
 * @license MIT License
 */
class Router {

	/**
	 * @var \SplDoublyLinkedList
	 */
	private $routes;

	/**
	 * @var string
	 */
	private $uri;

	/**
	 * @var string
	 */
	private $method;

	/**
	 * Constructor
	 * @param string $uri (optional) If you want to override server URI
	 * @param string $method (optional) If you want to override server method
	 */
	public function __construct($uri = null, $method = null) {
		$this->routes = new \SplDoublyLinkedList;

		if($uri === null) {
			$uri = $_SERVER['SCRIPT_NAME'];
		}
		$this->uri = $uri;

		if($method === null) {
			$method = $_SERVER['REQUEST_METHOD'];
		}
		$this->method = $method;
		$this->prefix = '';
	}

	/**
	 * Add a new route with "GET" method
	 * @param string $path
	 * @param function $callback
	 * @return Router Self
	 */
	public function get($path, $callback) {
		return $this->on('GET', $path, $callback);
	}

	/**
	 * Add a new route with "POST" method
	 * @param string $path
	 * @param function $callback
	 * @return Router Self
	 */
	public function post($path, $callback) {
		return $this->on('POST', $path, $callback);
	}

	/**
	 * Add a new route with "PUT" method
	 * @param string $path
	 * @param function $callback
	 * @return Router Self
	 */
	public function put($path, $callback) {
		return $this->on('PUT', $path, $callback);
	}

	/**
	 * Add a new route with "DELETE" method
	 * @param string $path
	 * @param function $callback
	 * @return Router Self
	 */
	public function delete($path, $callback) {
		return $this->on('DELETE', $path, $callback);
	}


	/**
	 * Add a new route with all method
	 * @param string $path
	 * @param function $callback
	 * @return Router Self
	 */
	public function any($path, $callback) {
		return $this->on('*', $path, $callback);
	}

	/**
	 * Add a new route
	 * @param string $method Method name (`*` for all methods)
	 * @param string $path
	 * @param function $callback
	 * @return Router Self
	 */
	public function on($method, $path, $callback) {
		$this->routes->push(array(
			"method" => $method,
			"path" => $this->prefix . $path,
			"callback" => $callback
		));

		return $this;
	}

	/**
	 * Spacename
	 * @param string $prefix
	 * @param function $callback
	 * @return Router Self
	 */
	public function space($prefix, $callback) {
		$that = clone $this;
		$that->prefix = $prefix;
		$callback($that);

		return $that;
	}

	/**
	 * Run the router
	 * @return mixed
	 */
	public function run() {
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

					if($this->method !== 'GET') {
						if(!isset($data)) {
							parse_str(file_get_contents("php://input"), $data);
						}
						$req->body = $data;
					}


					$that = $this;
					$next = function() use ($that) {
						$that->routes->next();
						$that->dispatch();
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
		$regex = preg_replace("/\:(\w+)/", "(?P<$1>.+[^\/])", $regex);
		$regex = str_replace("*", "(.*)", $regex);

		return $regex;
	}

}
