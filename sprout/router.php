<?php
/**
 * Copyright (c) 2011, Dave Mingos
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification, are permitted
 * provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice, this list of conditions
 *    and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright notice, this list of
 *    conditions and the following disclaimer in the documentation and/or other materials provided
 *    with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR
 * IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND
 * FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR
 * CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
 * WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY
 * WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

namespace Sprout;

/**
* Route management
*/
class Router {

	/**
	 * Default controller, action, format, etc. to use if not set
	 *
	 * @var array
	 */
	private static $_defaults = array(
		// Front controller could be set to index.php, default.php, or otherwise if mod_rewrite is
		// not available.
		'front_controller' => '',
		'controller' => 'pages',
		'action' => 'index',
		'format' => 'html'
	);


	/**
	 * Contains all defined routes
	 *
	 * @var array
	 */
	private static $_routes = array();


	/**
	 * Getter/setter for the front controller
	 *
	 * This can be set to index.php, default.php, or otherwise if mod_rewrite is not available.
	 *
	 * @param string (optional) The default front controller file name
	 * @return string Default front controller file name
	 */
	public static function front_controller($front_controller_name = null) {
		if (isset($front_controller_name) && strlen($front_controller_name) > 0) {
			self::$_defaults['front_controller'] = trim($front_controller_name);
		}
		return self::$_defaults['front_controller'];
	}


	/**
	 * Getter/setter for the default action name
	 *
	 * @uses Inflector::underscore
	 *
	 * @param string (optional) The default action name
	 * @return string Default action name
	 */
	public static function default_action($action_name = null) {
		if (isset($action_name) && strlen($action_name) > 0) {
			self::$_defaults['action'] = Inflector::underscore($action_name);
		}
		return self::$_defaults['action'];
	}


	/**
	 * Getter/setter for the default controller name
	 *
	 * @uses Inflector::controllerize
	 * @uses Inflector::underscore
	 *
	 * @param string
	 * @return string Default controller name
	 */
	public static function default_controller($controller_name = null) {
		if (isset($controller_name) && strlen($controller_name) > 0) {
			$controller_name = Inflector::controllerize($controller_name);
			$controller_name = Inflector::underscore($controller_name);
			$controller_name = str_replace('_controller', '', $controller_name);
			self::$_defaults['controller'] = $controller_name;
		}
		return self::$_defaults['controller'];
	}


	/**
	 * Getter/setter for the default format
	 *
	 * @param string (optional) The default format
	 * @return string Default format
	 */
	public static function default_format($format_name = null) {
		if (isset($format_name) && strlen($format_name) > 0) {
			self::$_defaults['format'] = $format_name;
		}
		return self::$_defaults['format'];
	}


	/**
	 * Add a route to the routes array.
	 *
	 * @see Route#__construct
	 */
	public static function connect($pattern, $components = array(), $name = null) {
		$route = new Route($pattern, $components, $name);
		self::$_routes[$pattern] = $route;
		if ($route->name !== null) {
			self::$_routes[$route->name] = $route;
		}
	}


	/**
	 * Return the first matching Route object
	 *
	 * The match function attempts to match the URI path to a Route object in the routes array.  If
	 * the URI path given exactly matches a static route, something like, "/dashboard" for example,
	 * it will return the Route and skip the process of matching via regular expression.
	 *
	 * If no static route match exists, it will iterate through the routes array until it finds a
	 * regular expression match.  If no match can be found, match returns false.
	 *
	 * @param string $path URI path to match
	 * @return Route|null
	 */
	public static function match($path) {
		self::_verify_routes_exist();
		$matched_route = null;
		$route_data = array();

		// If not URI rewriting, ignore front controller for matching.
		if (substr($path, 0, strlen(self::front_controller())) == self::front_controller()) {
			$path = substr($path, strlen(self::front_controller()));
		}

		// Attempt to match the URI path to a route.  If the $path exactly matches a static route,
		// something like, "/dashboard" for example, then skip the whole RegEx matching process.
		// Otherwise, use the route's regex_pattern property to match via regulrar expressions.
		if (isset(self::$_routes[$path])) {
			$matched_route = self::$_routes[$path];
			$route_data = $matched_route->components;
		} else {
			foreach (self::$_routes as $route) {
				$match_count = preg_match_all(
					"/^{$route->regex_pattern}$/i", $path, $component_matches, PREG_SET_ORDER
				);

				if ($match_count > 0) {
					// Replace regular expression component values with their parsed values.
					preg_match_all('/:(?<tokens>[\w]+)+/', $route->pattern, $token_matches);
					$token_matches = $token_matches['tokens'];
					$component_matches = $component_matches[0];

					// The first element in component_matches is always the full path match and we
					// don't want that here so get rid of it.
					array_shift($component_matches);

					$token_values = array();
					foreach ($token_matches as $index => $token) {
						if (isset($component_matches[$index])) {
							$token_values[$token] = $component_matches[$index];
						}
					}
					$route_data = array_merge($route->components, $token_values);
					$matched_route = $route;
					break;
				}
			}
		}

		if (is_null($matched_route)) {
			throw new Exception(trim('No match for route ' . $path) . '.');
		}

		// Populate the route's params property
		$matched_route->params = self::_parse_route_params($route_data);

		return $matched_route;
	}


	/**
	 * Populate a route's params property array with values from the URI path
	 *
	 * Token values are added to the $params property array.  Route params are eventually merged
	 * into the Request objects params array.
	 *
	 * @param array $route_data Associative array
	 *   The $route_data array contains at least the following keys:
	 *   * controller => self::default_controller() (default)
	 *   * action => self::default_action() (default)
	 *   * format => 'html' (default)
	 * @return array
	 */
	private static function _parse_route_params(array $route_data) {
		$params = array(
			'controller' => self::default_controller(),
			'action' => self::default_action(),
			'format' => self::default_format()
		);

		foreach ($route_data as $key => $value) {
			$key = Inflector::underscore($key);

			switch ($key) {
				case 'controller':
					$value = Inflector::underscore(Inflector::pluralize($value));
				break;

				case 'action':
					$value = Inflector::underscore($value);
				break;

				case 'format':
					$value = substr($value, 1);
				break;
			}

			$params[$key] = $value;
		}

		return $params;
	}


	/**
	 * Ensure the routes array is not empty
	 *
	 * If the app developer hasn't set up any routes, the default route is added to provide some
	 * basic MVC functionality.
	 *
	 * @return void
	 */
	private static function _verify_routes_exist() {
		$default_patterns = array(
			'/',
			'/' . self::default_action() . ':format',
			'/:controller:format',
			'/:controller/:action:format',
			'/:controller/:action/:id:format'
		);

		// For each of the default route definitions, if they do not exist in the routes array, add
		// them to the end as a default option.
		foreach ($default_patterns as $route_pattern) {
			if (!isset(self::$_routes[$route_pattern])) {
				self::connect($route_pattern);
			}
		}
	}


	/**
	 * Finds the best route for a given set of route components
	 *
	 * Usage:
	 *  1. Router::find_route(array(
	 *     	'controller' => 'users',
	 *     	'action' => 'edit',
	 *     	'id' => 32
	 *     ));
	 *
	 *  2. Router::find_route(array(
	 *     	'controller' => 'store',
	 *     	'action' => 'find_by_sku',
	 *     	'sku' => 'ABC-111222333444'
	 *     ));
	 *
	 * @todo find_route should find the best route for a given set of components
	 */
	function find_route(array $components = array()) {
		// ...
	}
}