<?php
/**
 * Copyright (c) <YEAR>, <YOU OR YOUR ORGANIZATION>
 * All rights reserved.
*/

use \Sprout\Router;

/**
 * Configuration file for all routing patterns in your application
 *
 * Routes are defined with a pattern at minimum and in most cases, and array of route component
 * definitions. Route patterns are slash-delimited strings with components tokenized with a
 * preceding semicolon (:) character. Tokenized route components should be defined with a regular
 * expression in the second parameter array. If no component definition is provided, a very generic
 * default one will be used.
 *
 * Tokenized route components and their values are added to the $request->params array. If
 * conflicting query string parameters or formatted key:value URI parameters exist, tokenized route
 * components will take precedence.
 *
 * Routes are prioritized in the order they are defined. Connect your routes in order of specificity
 * with the most generic default route last.
 *
 * The one exception to the priority rule is the home route ('/'). Because this route contains only
 * static segments and is likely to be a high traffic route, it is a good idea to make it the first
 * connection so that it is always the first match attempted. Routes with only static segments skip
 * component matching via regular expressions altogether.
 *
 * Some example route definitions:
 *
 * Router:connect()
 *
 * For regular expression help, see:
 *  - http://www.php.net/manual/en/reference.pcre.pattern.syntax.php
 *  - http://www.regular-expressions.info/reference.html
 */

// Unless set here, 'front_controller' will be empty. This can be set to index.php, default.php, or
// otherwise for servers where mod_rewrite is not available.
// Router::front_controller('default.php');

// Unless set here, 'pages' will be the default controller.
// Router::default_controller('store');

// Unless set here, 'index' will be the default action.
// Router::default_action('default');

// Unless set here, 'html' will be the default format.
// Router::default_format('json');

Router::connect('/', array(
	'action' => 'dashboard'
), 'dashboard');

Router::connect('/login', array(
	'controller' => 'auth',
	'action' => 'login'
), 'login');

Router::connect('/logout', array(
	'controller' => 'auth',
	'action' => 'logout'
), 'logout');

Router::connect('/signup', array(
	'controller' => 'auth',
	'action' => 'signup'
), 'signup');

Router::connect('/profile/:username', array(
	'controller' => 'users',
	'action' => 'view',
	'username' => '([\w]{2,12})' // 2 to 12 letters, numbers, and underscores
));

// define filters for the url parameters
Router::connect('/users/:id/', array(
	'controller' => 'users',
	'id' => '([\d]{1,8})'
));

// define filters for the url parameters
Router::connect('/store/products/:sku:format', array(
	'controller' => 'products',
	'action' => 'find_by_sku',
	'sku' => '([a-zA-Z]{3}\-[\d]{12})'
), 'view_product');

// Router::connect('/:controller/:id:format', array(
// 	'action' => 'view'
// ));
// Router::connect('/:controller/:id/:action:format');

// The following routes will be added by default.
// It is not necessary to uncomment these lines. They are just shown here for your reference.
// Router::connect('/' . Router::default_action() . ':format', array(), 'homepage');
// Router::connect('/:controller:format');
// Router::connect('/:controller/:action:format');
// Router::connect('/:controller/:action/:id:format');
