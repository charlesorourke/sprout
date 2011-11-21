<?php
/**
 * Copyright (c) <YEAR>, <YOU OR YOUR ORGANIZATION>
 * All rights reserved.
*/

use \Sprout\Application;

// Bootstrap Sprout PHP framework. By default, sprout.php and the libraries are located in the same
// directory as the app_seed but Sprout, your application, and your applications' webroot can be
// stored anywhere on the disk. You can even keep several versions of the framework checked out in
// different locations. For example, /frameworks/sprout/v0.1 or /frameworks/sprout/v0.2.
require_once realpath('../../sprout.php');


// Application::config sets up your application's default configuration. Sprout can handle a variety
// of setups with minimal configuration. You can (and should) keep your webroot separate from the
// rest of your application code. You can also keep Sprout in a central location on the disk so that
// several applications can use the same framework.
Application::config(array(
	// You can set up any number of custom configuration options. For example, here we set the name
	// of our application for use throughout our app via Application::get('name').
	// 'name' => 'My Application',

	// Setting the environment is technically optional but it is good practice. By configuring
	// separate operation environments for your application, it can behave differently depending on
	// where it is being used. Environment configuration options override Application configuration
	// options if they exist in both places.
	//
	// In your development environment for example, you will likely use a different database
	// connection than in your production environment. You might also keep your webroot within the
	// application directory for better SCM management but in your production environment, you will
	// likely keep your application files separate from the publicly accesible webroot.
	//
	// You can also bootstrap debugging tools such as FirePHP or ChromePHP for your development
	// environment, testing suites for your test environment, and so on. You can set up as many
	// environments as necessary for your workflow.
	'environment' => 'development',

	// The path is the full path to your application directory. Setting the path as an application
	// configuration option allows the framework to infer a few other things as well--like where to
	// cache view files and where to look for models, controllers, and views.
	'path' => realpath('../'),

	// Another common configuration option is the location of the webroot. The Sprout default is to
	// keep it inside the application directory but in production or staging environments, it is a
	// good idea to keep your application code in a more secure location on the diek and the webroot
	// somewhere accessible to the web server.
	// 'webroot' => '/www/my_app'
));


// Load your application's routes. Sprout was built with support for some basic MVC functionality
// out of the box but you can set up custom routes to make your application's URLs more easily
// understood by humans as well as search engines.
require_once 'routes.php';


// Sprout correctly handles some of the more common irregular plurals (person to people, child to
// children, etc.) and uncountable words (fish, sheep, etc.) but you can extend the lists of words
// that are uncountable or have irregular plural forms in inflections.php.
require_once 'inflections.php';
