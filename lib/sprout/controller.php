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

use \Sprout\HTTP\Response;

class Controller {

	/**
	 * Associative array to hold action data
	 *
	 * @var array
	 **/
	protected $data = array();


	/**
	 * The request object that got us here
	 *
	 * @var Request
	 **/
	protected $request;


	/**
	 * The response object used to respond to the client
	 *
	 * @var Response
	 **/
	protected $response;


	/**
	 * The view template
	 *
	 * @var View
	 **/
	protected $view;


	/**
	 * The view template rendered with action data
	 *
	 * @var string
	 **/
	protected $content;


	/**
	 * Alias of the current controller base name
	 *
	 * For example, with a PagesController, $controller_name would be "pages".
	 *
	 * @var string
	 **/
	protected $controller_name;


	/**
	 * Alias of the current action name
	 *
	 * @var string
	 **/
	protected $action_name;


	/**
	 * Alias of the current requested format
	 *
	 * @var string
	 **/
	protected $format;


	/**
	 * Alias of the current request params
	 *
	 * @var string
	 **/
	protected $params;


	/**
	 * Initialize an instance of a specified controller
	 *
	 * @param string $controller_name The name of the controller to initialize - can be something
	 *    like "PagesController", "pages", or "page"
	 * @param Request $request The initiating request object
	 * @return SpecifiedController
	 */
	public static function init($controller_name, $request) {
		$controller_class = Inflector::controllerize($controller_name);
		if (!class_exists($controller_class)) {
			throw new Exception($controller_class . ' does not exist.');
		}

		// The controller class exist. Create an instance and set up property aliases of the current
		// request's controller name, action name, format, and params array for convenience.
		$controller = new $controller_class;
		$controller->request = &$request;
		$controller->controller_name = &$request->params['controller'];
		$controller->action_name = &$request->params['action'];
		$controller->format = &$request->params['format'];
		$controller->params = &$request->params;

		// Set the default view template based on route params - this can be overridden in the
		// controller action by setting $this->view->template = 'some_other_template.ext'; with .ext
		// being optional.
		$view_template = $controller->action_name . '.' . $controller->format;
		$controller->view = new View($controller->controller_name, $view_template);

		// Create a new response object here so that headers can be set in the action if needed.
		$controller->response = new Response;

		return $controller;
	}


	/**
	 * Run all filters, call the action, and set $this->data and $this->content
	 *
	 * @param string $action The name of the action being called.
	 * @return void
	 */
	public function run_action($action) {
		$action = Inflector::underscore($action);

		// Verify action method exists on this controller.
		if (!method_exists($this, $action)) {
			throw new Exception('Action ' . $action . ' does not exist on ' . get_class($this) . '.');
		}

		// Run before_filters callback methods in case any params manipulation is needed.
		$this->_run_filters('before_filters');

		// Call the action and store any data returned.
		$action = $this->action_name;
		$action_data = $this->$action();

		// Set $this->data to the associative array of data elements returned by the action. If the
		// action sets data elements directly or if no data is required by the view, $action_data
		// may be null. If $action_data is empty or not an array, this step is skipped.
		if (!empty($action_data) && is_array($action_data)) {
			$this->data = array_merge($this->data, $action_data);
		}

		// Run before_render callback methods.
		$this->_run_filters('before_render');

		// Set the content property of the controller to the rendered template content. The content
		// property may be modified after the action has finished processing through after_filters.
		$this->content = $this->view->render($this->data);

		// Run after_filters callback methods.
		$this->_run_filters('after_filters');
	}


	/**
	 * Prepares the response object and sends it
	 *
	 * This sets the response object's content_type and content properties and sends the response to
	 * the client.
	 *
	 * @return void
	 */
	public function send_response() {
		// If the response content type has not yet been specified, try to assign one based on known
		// extensions/formats. If the content type cannot be determined by set_content_type, the
		// determination of content type is done by the web server.
		if (!isset($this->response->content_type)) {
			$this->response->set_mime_type($this->format);
		}

		if (!empty($this->content)) {
			$this->response->body = $this->content;
		}

		$this->response->send();
	}


	/**
	 * Run defined filters at a specific point in execution
	 *
	 * This will loop through the given set of filters and run each method in the order in which it
	 * was defined.
	 *
	 * @param string $filters_set Which set of filters to execute (associative array)
	 * @return void
	 */
	private function _run_filters($filters_set) {
		if (isset($this->$filters_set) && is_array($this->$filters_set)) {
			foreach ($this->$filters_set as $filter => $constraints) {
				if (is_numeric($filter) && is_string($constraints)) {
					$filter = $constraints;
					$constraints = array();
				}

				$this->_apply_filter($filter, $constraints);
			}
		}
	}


	/**
	 * Attempts to run a given filter callback function
	 *
	 * @param string $method The name of the method being called
	 * @param array $constraints Associative array with 'only' and/or 'except' options
	 * @return void
	 */
	private function _apply_filter($method, $constraints) {
		$constraints += array('only' => array(), 'except' => array());
		$inclusions = $constraints['only'];
		$exclusions = $constraints['except'];

		if (!is_array($inclusions)) $inclusions = array($inclusions);
		if (!is_array($exclusions)) $exclusions = array($exclusions);

		// If inclusions are set and the called method isn't in the list, return.
		if (!empty($inclusions) && !in_array($this->action_name, $inclusions)) return;

		// If exclusions are set and the called method is in the list, return.
		if (!empty($exclusions) && in_array($this->action_name, $exclusions)) return;

		if (is_callable(array($this, $method))) {
			call_user_func(array($this, $method));
		} else {
			throw new Exception('Action filter ' . $method . ' could not be called.');
		}
	}
}