<?php
/**
 * Copyright (c) <YEAR>, <YOU OR YOUR ORGANIZATION>
 * All rights reserved.
*/

/**
* Base class of all controllers in your application.
*
* Controller should be used to perform tasks with application-wide scope. For example, since it is
* the base class of all controllers in your application, Controller is an ideal place to handle
* authentication.
*/
abstract class Controller extends \Sprout\Controller {
	// Do not implement __construct methods in controllers. Instead, define before_callbacks and
	// $before_callbacks with optional $contdtions arrays with 'only' and 'except' options.

	// For example, the following three structures are good examples of $before_callback functions,
	// implemented in controller.

	// protected $before_filters = array(
	// 	'log_page_visit',  // applied before every method call
	// 	'initialize' => array('only' => 'edit'),
	// 	'authenticate' => array('except' => 'login, signup'),
	// );
}