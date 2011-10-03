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
* Static class for managing configuration options
*
* The Config class defines properties for configuration options required by
* Sprout. Developers can extend the required configuration with any number of
* options required in their applications. Application configuration options will
* be added to an associative array stored in the $options property.
*/
class Config {

	/**
	 * Whether the configuration has been initialized with defaults
	 *
	 * @var boolean
	 */
	private static $initialized = false;


	/**
	 * The environment of the application: development, test, or production
	 *
	 * @var string
	 */
	private static $environment;


	/**
	 * Label-friendly app name
	 *
	 * This is used to uniquely label database names, session stores, etc.
	 * Example: my_application
	 *
	 * @var string
	 */
	private static $app_basename;


	/**
	 * Path to the directory containing your application code
	 *
	 * @var string
	 */
	private static $app_dir;


	/**
	 * Path to the view cache folder (must be web accessible)
	 *
	 * @var string
	 */
	private static $cache_dir;


	/**
	 * Path to a writable directory for temporary file storage
	 *
	 * @var string
	 */
	private static $temp_dir;


	/**
	 * Path to your applications' webroot
	 *
	 * In development, this should be a subdirectory of your application
	 * directory so that UI assets can be managed with a version control system
	 * such as Git SCM.
	 *
	 * In production, the webroot should be outside of the webroot for greater
	 * security.
	 *
	 * @var string
	 */
	private static $webroot_dir;


	/**
	 * Database connections
	 * @todo move database connections ot an ini or yaml file
	 *
	 * @var array
	 */
	private static $connections; // array of database configurations


	/**
	 * An array of configuration options
	 *
	 * @var array
	 */
	private static $options; // array of database configurations


	/**
	 * Retrieve the value of a given option
	 *
	 * @param string $option The name of the config option being requested
	 * @return mixed The config option value
	 */
	public static function get($option) {
		if (property_exists(__CLASS__, $option)) {
			$value = self::${$option};
		} elseif (array_key_exists($option, self::$options)) {
			$value = self::$options[$option];
		} else {
			throw new Exception('Config options ' . $option . ' not found.');
		}

		return $value;
	}


	/**
	 * Set a given option
	 *
	 * @param string $option The name of the config option being set
	 * @param mixed $value The value of the config option being set
	 * @return void
	 */
	public static function set($option, $value) {
		if (self::$initialized !== true) {
			self::init();
		}

		switch ($option) {
			case 'app_basename':
				self::${$option} = Inflector::underscore($value);
				break;

			case 'app_dir':
				if (file_exists(realpath($value))) {
					self::${$option} = realpath($value);
				} else {
					throw new Exception('app_dir ' . realpath($value) . ' does not exist.');
				}
				break;

			case 'webroot_dir':
				if (file_exists(realpath($value))) {
					self::${$option} = realpath($value);
				} else {
					throw new Exception('webroot ' . realpath($value) . ' does not exist.');
				}
				break;

			case 'cache_dir':
				if (file_exists(realpath($value)) && is_writable(realpath($value))) {
					self::${$option} = realpath($value);
				} else {
					throw new Exception('cache_dir ' . realpath($value) . ' is not writable or does not exist.');
				}
				break;

			case 'temp_dir':
				if (file_exists(realpath($value)) && is_writable(realpath($value))) {
					self::${$option} = realpath($value);
				} else {
					throw new Exception('temp_dir ' . realpath($value) . ' is not writable or does not exist.');
				}
				break;

			default:
				// We don't want to set the options property here because it is
				// used to store user-defined configuration options. If the app
				// developer defines a config parameter called 'options' we will
				// want to store it in the $options property, not as the options
				// property itself.
				if ($option !== 'options') {
					self::${$option} = $value;
				}
		}
	}


	/**
	 * Return the entire configuration as an array
	 *
	 * @return array All configuration options
	 */
	public static function all() {
		$properties = get_class_vars(__CLASS__);

		unset($properties['initialized']);
		unset($properties['options']);

		return array_merge($properties, self::$options);
	}


	/**
	 * Take an array of user-defined options and populate the Config properties
	 * and $options array.
	 *
	 * @param array $options An associative array of configuration parameters
	 * @return void
	 **/
	public static function load(array $options = array()) {
		if (self::$initialized !== true) {
			self::init();
		}

		$properties = get_class_vars(__CLASS__);
		unset($properties['initialized']);
		unset($properties['options']);

		// Loop through the user-defined configuration options and test validity
		// where applicable. Additional config options that are not config class
		// properties will be stored in the options property.
		foreach ($options as $option => $value) {
			if (array_key_exists($option, $properties)) {
				self::set($option, $value);
				unset($options[$option]);
			}
		}

		// For each of the remaining $options, add them to the self::$options
		// array.
		foreach ($options as $option => $value) {
			self::$options[$option] = $value;
		}
	}


	/**
	 * Initialize the configuration options and set some defaults
	 *
	 * @param array $options A user-defined associative array of configuration
	 *   options
	 */
	public static function init(array $options = array()) {

		// Initialize the configuration with Sprout defaults

		self::$environment = 'development';


		self::$app_dir = dirname(dirname(__DIR__)) . DS . 'app';
		self::$webroot_dir = self::$app_dir . DS . 'webroot';
		self::$cache_dir = self::$webroot_dir . DS . 'cache';
		self::$temp_dir = sys_get_temp_dir();

		self::$app_basename = Inflector::underscore(basename(self::$app_dir));

		self::$connections = array(
			'development' => 'mysql://root@localhost/' . self::$app_basename . '_development'
		);

		self::$options = array();

		self::$initialized = true;
	}
}