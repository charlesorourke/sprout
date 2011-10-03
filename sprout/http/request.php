<?php
/**
 * Copyright (c) 2011, David Mingos
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

namespace Sprout\HTTP;

use \Sprout\Router;
use \Sprout\Inflector;

class Request {

	/**
	 * The request method
	 *
	 * GET, POST, PUT, or DELETE
	 *
	 * @var string
	 */
	public $method;


	/**
	 * The protocol of the request - usually HTTP/1.1
	 *
	 * @var string
	 */
	public $protocol;


	/**
	 * The server address including subdomains and host name and domain
	 *
	 * www.sample.com, sample.com, sample.dev, localhost, etc.
	 *
	 * @var string
	 */
	public $server;


	/**
	 * The port of the request - usually 80 or 443
	 *
	 * @var string
	 */
	public $port;


	/**
	 * The path portion of the URI
	 *
	 * @var string
	 */
	public $path;


	/**
	 * The full URI of the request
	 *
	 * @var string
	 */
	public $uri;


	/**
	 * The Route object the router matched to the URI path
	 *
	 * @var string
	 */
	public $route;


	/**
	 * Data sent via POST
	 *
	 * @var string
	 */
	public $data;


	/**
	 * Data sent via GET
	 *
	 * Merged array containing query string parameters as well as URI path
	 * parameters in the format: "key1:value/key2:value/key3:value"
	 *
	 * @var string
	 */
	public $query;


	/**
	 * A URI-friendly string containing query string data
	 *
	 * Array data is formatted: key1:value/key2:value,value,value/key3:value
	 * where key2 contains an array with three elements.
	 *
	 * @var string
	 */
	public $query_uri;


	/**
	 * Merged associative array containing $query, $data, $route->params
	 *
	 * When array keys exist in multiple places, their values are combined into
	 * an array.
	 *
	 * @var string
	 */
	public $params;


	/**
	 * Array of posted file data
	 *
	 * @var string
	 */
	public $files;


	/**
	 * HTTP referrer if present
	 *
	 * @var string
	 */
	public $referrer;


	/**
	 * The requester's IP address
	 *
	 * @var string
	 */
	public $remote_ip;


	/**
	 * User agent string
	 *
	 * @var string
	 */
	public $user_agent;


	/**
	 * Associative array containing all Raw HTTP request headers
	 *
	 * @var string
	 */
	public $headers;


	/**
	 * Initialize a new HTTP Request object
	 *
	 * This is the very first thing that executes after everything is
	 * bootstrapped and application configuration options are defined.
	 */
	function __construct($path_info = null) {
		$this->method = $_SERVER['REQUEST_METHOD'];

		// Protocol
		$this->protocol = $_SERVER['SERVER_PROTOCOL'];
		$formatted_protocol = strtolower(substr($this->protocol, 0, strpos($this->protocol, '/')));

		// Server
		$this->server = $_SERVER['SERVER_NAME'];

		// Port
		$this->port = $_SERVER['SERVER_PORT'];

		// Format protocol (check HTTPS) and port strings
		if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
			$formatted_protocol = 'https';
			$formatted_port = ($this->port != 443) ? ":{$this->port}" : '';
		} elseif ($this->protocol == 'HTTP/1.1' && $this->port == 80) {
			$formatted_port = '';
		} else {
			$formatted_port = ":{$this->port}";
		}
		$formatted_protocol .= '://';


		// Parse the path info portion of the URI and extract the correct path,
		// query array, and formatted query_uri.
		$path_parts = $this->parse_path($path_info);

		// Path
		$this->path = $path_parts['path'];

		// Route
		$this->route = Router::match($this->path);

		// Data
		$this->query = $path_parts['query'];
		$this->data = $_POST; // <-- could also hold REST data
		$this->files = $_FILES;

		// Params: merged array of path, querystring, post, and route parameters
		$this->params = array_merge_recursive($this->query, $this->data, $this->route->params);
		$this->query_uri = $path_parts['query_uri'];

		// Clear out the native get, post, request and files arrays to avoid
		// issues caused by the same data existing in multiple places.
		$_GET = $_POST = $_REQUEST = $_FILES = array();



		// URI (complete)
		$this->uri = $formatted_protocol . $this->server . $formatted_port . $this->path;
		if (strlen($this->query_uri) > 0) {
			$this->uri = rtrim($this->uri, '/') . '/' . $this->query_uri;
		}

		$this->referrer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null;
		$this->remote_ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;
		$this->user_agent = $_SERVER['HTTP_USER_AGENT'];

		$this->headers = getallheaders();
	}


	/**
	 * Returns the path portion of the current request URI path from the URL.
	 *
	 * Based on work from Dan Horrigan - modified to extend the GET array with
	 * key:value pairs in URI segments.
	 *
	 * This also corrects the QUERY_STRING server variable and the $_GET array.
	 * It supports all forms of mod_rewrite and the following URI formats:
	 *  - http://example.com/index.php/foo (returns '/foo')
	 *  - http://example.com/index.php?/foo (returns '/foo')
	 *  - http://example.com/index.php/foo?baz=bar (returns '/foo')
	 *  - http://example.com/index.php?/foo?baz=bar (returns '/foo')
	 *
	 * Similarly using mod_rewrite to remove index.php:
	 *  - http://example.com/foo (returns '/foo')
	 *  - http://example.com/foo?baz=bar (returns '/foo')
	 *  - http://example.com/users/1/edit/key:val1/test:a,b,c/test:d,e?key=val2
	 *    (returns /users/1/edit)
	 *
	 * @param bool $path_info (optional) String path info to use instead of
	 *    $_SERVER variables
	 * @return array Associative array with path, query, and query_uri keys
	 */
	private function parse_path($path_info = null) {

		$query = array();
		$params = array();

		if (isset($path_info) && is_string($path_info)) {
			$path = $path_info;
		} elseif (isset($_SERVER['PATH_INFO'])) {
			$path = $_SERVER['PATH_INFO'];
		} elseif (isset($_SERVER['REQUEST_URI'])) {
			$path = $_SERVER['REQUEST_URI'];

			if (strpos($path, $_SERVER['SCRIPT_NAME']) === 0) {
				$path = substr($path, strlen($_SERVER['SCRIPT_NAME']));
			} elseif (strpos($path, dirname($_SERVER['SCRIPT_NAME'])) === 0) {
				$path = substr($path, strlen(dirname($_SERVER['SCRIPT_NAME'])));
			}
		} else {
			throw new Exception('Could not determine the URI');
		}

		// This section ensures that even on servers that require the URI to be
		// in the query string (Nginx), a correct URI is found and also fixes
		// the QUERY_STRING server variable and $_GET array.

		// Path is something like /index.php?/users/edit/123?foo=bar
		if (strrpos($path, '?') !== false) {
			$query_position = strrpos($path, '?');
			parse_str(substr($path, $query_position + 1), $_GET);
			$path = substr($path, 0, $query_position);
		}

		// Path begins with ?/
		if (strncmp($path, '?/', 2) === 0) {
			$path = substr($path, 2);
		}
		$parts = preg_split('/\?/i', $path, 2);
		$path = $parts[0];

		if (isset($parts[1])) {
			$_SERVER['QUERY_STRING'] = $parts[1];
			parse_str($_SERVER['QUERY_STRING'], $_GET);
		} else {
			$_SERVER['QUERY_STRING'] = '';
			// $_GET = array();
		}
		$path = parse_url($path, PHP_URL_PATH);

		// Do some final cleaning of the URI path
		$path = str_replace(array('//', '../'), '/', trim($path, '/'));


		// Parse out key:value segments of the URI path into $params.
		if (strpos($path, ':') !== false) {
			$path_segments = explode('/', $path);

			foreach ($path_segments as $index => $segment) {
				if (strpos($segment, ':') > 0) { // We don't want any blank keys.
					$param_parts = explode(':', $segment);
					$key = $param_parts[0];
					$value = $param_parts[1];

					// If the value for the parameter is a comma-delimited
					// string, explode the values into an array.
					if (strpos($value, ',') !== false) $value = explode(',', $value);

					// If a $path_param key matches an existing $params key and
					// the existing value is an array, append the $path_param
					// value to the existing array.
					if (array_key_exists($key, $params)) {
						if (is_array($params[$key])) {
							// Merge the two existing arrays
							if (is_array($value)) {
								$params[$key] = array_merge($params[$key], $value);
							} else {
								array_push($params[$key], $value);
							}
						} else {
							// Convert the existing string value to an array
							$params[$key] = array($params[$key], $value);
						}
					} else {
						$params[$key] = $value;
					}
					unset($path_segments[$index]);
				}
			}
			$path = implode('/', $path_segments);
		}

		$query = array_merge_recursive($_GET, $params);

		$path = Router::front_controller() . '/' . $path;


		// Map GET and URI params to the following format where key2 in the
		// example contains an array with three items.
		//   "key1:value1/key2:valueA,valueB,valueC",
		$query_uri = array();
		foreach ($query as $key => $value) {
			$query_uri[] = $key . ':' . (is_array($value) ? implode(',', $value) : $value);
		}
		$query_uri = implode('/', $query_uri);


		return compact('path', 'query', 'query_uri');
	}
}