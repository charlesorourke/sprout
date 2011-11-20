<?php
/**
 * Copyright (c) <YEAR>, <YOU OR YOUR ORGANIZATION>
 * All rights reserved.
*/

class PagesController extends ApplicationController {

	// FILTERS SETUP
	// Controllers can configure arrays of $before_filters to be run before actions are called,
	// before_render filters run after the action but before the view is rendered, and/or
	// after_filters methods run after the view is rendered. Filter methods must be defined with
	// public or protected visibility and may be set to run with 'only' a specific set of actions or
	// all actions 'except' a specific set. Filters will be run with all actions by default.
	//
	// For example:
	// protected $before_filters = array(
	// 	'start_timer',
	// 	'load_preferences' => array(
	// 		'only' => 'dashboard'
	// 	),
	// 	'set_page_title' => array(
	// 		'except' => array('dashboard', 'logout')
	// 	)
	// );
	//
	// protected $before_render = array();
	//
	// protected $after_filters = array(
	// 	'stop_timer'
	// );



	// ACTIONS

	/**
	 * The application dashboard
	 *
	 * This is an example page purely here to demonstrate some of the ways you can work with
	 * controller actions.
	 */
	public function dashboard() {
		// All action methods must be public. Since this is the default, you can technically leave
		// off the public declaration but it is good practice to use it.

		// You can optionally specify an alternate location for your view templates. The following
		// example tells Sprout to look for view templates in {app}/views/special_pages/* instead of
		// the default location of {app}/views/{controller_name}/*.
		// $this->view->folder = 'special_pages';

		// You can optionally specify an alternate template to use when rendering this action. The
		// following example will tell Sprout to look for a view template in $this->view->folder
		// named alternate_template.html.php. The default template format specified in the Router
		// configuration will be used unless otherwise specified.
		// $this->view->template = 'alternate_template';

		// Alternate templates may optionally include further nesting within $this->view->folder and
		// may or may not include an specific format to use. The following example tells Sprout to
		// use the {app}/views/{$this->view->folder}/one/two/my_template.wiki.php template file.
		// $this->view->template = 'one/two/alternate_template.wiki';

		// You can pass data to the view by setting it directly in the $this->data array. This will
		// create a variable in the view called $answer with a value of 42.
		// $this->data['answer'] = 42;

		// The preferred way of passing data to the view is to define local variables, compact them
		// into an array and return them. The following three lines would pass the $greeting and
		// $today variables to the view.
		$greeting = 'Hello, Sprout!';
		$today = date('m/d/Y');

		return compact('greeting', 'today');
	}



	// ACTION FILTER IMPLEMENTATIONS

	// protected function load_preferences() {
	// 	// Load dashboard user preferences
	// }
}