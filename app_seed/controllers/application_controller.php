<?php
/**
 * Copyright (c) <YEAR>, <YOU OR YOUR ORGANIZATION>
 * All rights reserved.
*/

/**
* Base class of all controllers in your application.
*
* ApplicationController is an ideal place to define methods for implementing logic of application
* scope.  ApplicationController methods are often used for before_Filters, before_render filters,
* and after_filters.  Filter methods only applicable to one particular controller should be defined
* within that controller.
*
* Filter methods defined in ApplicationController must have protected or public visibility.
*/
abstract class ApplicationController extends \Sprout\Controller {

	/**
	 * EXAMPLE: The title of a rendered page
	 *
	 * @var string
	 */
	// public $page_title;


	/**
	 * EXAMPLE: Track the execution time of an action
	 *
	 * @var float
	 */
	// private $_execution_time;


	/**
	 * EXAMPLE: Initiate the page title to the titleized version of the action name
	 */
	// protected function set_page_title() {
	// 	$this->page_title = \Sprout\Inflector::titleize($this->action_name);
	// }


	/**
	 * EXAMPLE: Start the action timer
	 */
	// protected function start_timer() {
	// 	$this->_execution_time = microtime(true);
	// }


	/**
	 * EXAMPLE: Stop the action timer
	 */
	// protected function stop_timer() {
	// 	$this->_execution_time = microtime(true) - floatval($this->_execution_time);
	// }
}