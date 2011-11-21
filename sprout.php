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

if (!defined('PHP_VERSION_ID') || PHP_VERSION_ID < 50300) {
	die('Sprout requires PHP 5.3 or higher. The end.');
}

define('DS', DIRECTORY_SEPARATOR);

require_once 'lib' . DS . 'sprout' . DS . 'inflector.php';

/**
 * Autoloader for all Sprout framework classes
 */
spl_autoload_register(function($class) {
	if (strpos($class, '\\') !== false) {
		$segments = array();
		foreach (explode('\\', $class) as $segment) {
			array_push($segments, Inflector::underscore($segment));
		}
		$class_path = __DIR__ . DS . 'lib' . DS . implode(DS, $segments) . '.php';
	} else {
		$app_path = Application::get('path');
		$class = Inflector::underscore($class);

		// If we can't autoload this via namespace, see if we can pull it in from one of the app's
		// known directories.
		if (stripos($class, 'controller') !== false) {
			$class_path = $app_path . DS . 'controllers' . DS . $class . '.php';
		} elseif (stripos($class, 'helper') !== false) {
			$class_path = $app_path . DS . 'helpers' . DS . $class . '.php';
		} else {
			$class_path = $app_path . DS . 'models' . DS . $class . '.php';
		}
	}

	if (file_exists($class_path)) require_once $class_path;
});