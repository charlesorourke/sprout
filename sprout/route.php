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

namespace Sprout;

/**
 * A route and its components
 *
 * Route definitions consist of:
 *  - a pattern, usually with tokenized components
 *  - an array of static and regular expression component definitions
 *  - (optionally), a name
 *
 * There are default regular expressions for :controller, :action, :id, and
 * :format but they can be overridden.
 *
 * $route = new Route('/', array(
 * 	'controller' => 'pages',
 * 	'action' => 'view'
 * 	'page' => 'home'
 * ));
 *
 * $route = new Route('/products/:sku', array(
 * 	'controller' => 'catalog',
 * 	'action' => 'view_product'
 * 	'sku' => '([a-zA-Z]{3}-[0-9]{6})'
 * ), 'view_product');
 *
 */
class Route {

	/**
	 * Name by which the route can be referenced
	 *
	 * If no name is provided, the router will name the route. For routes with
	 * an action and controller defined, [action]_[underscored controller] will
	 * be used -- for example, edit_users or add_pages.  Otherwise, the pattern
	 * will be assigned as the name.
	 *
	 * @var string
	 */
	public $name;


	/**
	 * Human-readable routing pattern
	 *
	 * Pattern is a slash-delimited string containing static and tokenized
	 * component keys defined in the $components property array. Static
	 * components are plain text strings like, "store." Tokenized components
	 * are defined with a semi-colon followed by the string component name,
	 * ":controller" for example.
	 *
	 * The following are all valid patterns:
	 *  - /my-profile
	 *  - /store/products/:name
	 *  - /:controller/:id/:action
	 *
	 * @var string
	 */
	public $pattern;


	/**
	 * Associative array of static and tokenized components
	 *
	 * Pattern components can be defined with strings or regular expressions.
	 * If not specified, the router will attempt to assign one of the default
	 * component definitions.  If no controller or action can be determined, the
	 * router will assign the values for default_controller and
	 * default_action.
	 * 
	 * Custom components are defined by regular expressions.
	 *
	 * array(
	 *     'controller' => 'products',
	 *     'action' => 'view',
	 *     'username' => '([\w\d]{2,12})'
	 * )
	 *
	 * @var array
	 */
	public $components;


	/**
	 * Associative array containing component values parsed out by Router#match
	 *
	 * When the router matches a route to a URI path, static and tokenized
	 * component values will be extracted and stored in this array.
	 *
	 * @var array
	 */
	public $params;


	/**
	 * Regular expression version of $pattern used for matching
	 *
	 * @var string
	 */
	public $regex_pattern;



	/**
	 * Array of default RegEx patterns to match controllers, actions, and IDs
	 *
	 * Controller and action names:
	 *  - Strings containing only letters, numbers, underscores and dashes
	 *  - "Letters" are a-z, A-Z, and the bytes from 127 through 255 (0x7f-0xff)
	 *    as defined by PHP.net for valid label names.
	 *    http://www.php.net/manual/en/language.oop5.basic.php
	 * IDs:
	 *  - Numbers 1 through 9999999999
	 * Format:
	 *  - A period followed by 1 to 12 letters, numbers, dashes or underscores
	 * Default:
	 *  - Any number of alphanumeric characters, @, $, (, ), +, -, =, ., or &
	 *
	 * @var array
	 */
	private $default_patterns = array(
		'controller' => '([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\-\x7f-\xff]+)',
		'action' => '([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\-\x7f-\xff]+)',
		'id' => '([1-9][0-9]{0,9})',
		'format' => '(\.[\w\-]{1,12})?',
		'default' => '([\w\@\$\(\)_\+\-=\.&]+)'
	);


	/**
	 * Initialize a new route object
	 *
	 * @todo Consider adding :format to the end of every route except for '/' 
	 * @param string $pattern Forward slash-delimited URI pattern
	 * @param array $components (optional) Static and tokenized component
	 *    definitions
	 * @return void
	 */
	function __construct($pattern, array $components = array(), $name = null) {
		// Route patterns cannot have a trailing slash.
		$pattern = rtrim($pattern, '/') ?: '/';

		// If no controller was specified, use the default controller configured
		if (strpos($pattern, ':controller') === false && !isset($components['controller'])) {
			$components['controller'] = Router::default_controller();
		}

		// If no action was specified, use the default action configured
		if (strpos($pattern, ':action') === false && !isset($components['action'])) {
			$components['action'] = Router::default_action();
		}

		// Build out the regular expression pattern for matching
		$regex_pattern = str_replace('/', '\/', $pattern);
		preg_match_all('/:(?<tokens>[\w]+)+/', $pattern, $matches);

		foreach ($matches['tokens'] as $token) {
			if (isset($components[$token])) {
				$token_pattern = $components[$token];
			} elseif (isset($this->default_patterns[$token])) {
				$token_pattern = $this->default_patterns[$token];
			} else {
				$token_pattern = $this->default_patterns['default'];
			}
			// todo: Consider auto-wrapping token patterns in parentheses.
			$regex_pattern = str_replace(":{$token}", "{$token_pattern}", $regex_pattern);
		}

		if (!isset($name) && isset($components['action']) && isset($components['controller'])) {
			$name = Inflector::underscore($components['action'] . '_' . $components['controller']);
		} elseif (!isset($name)) {
			$name = $pattern;
		}

		$this->name = $name;
		$this->pattern = $pattern;
		$this->components = $components;
		$this->regex_pattern = $regex_pattern;
	}
}