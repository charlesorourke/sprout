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
* Holds the application configuration
*
* Any application level configuration option can be overridden by an Environment configuration
* option. The Application class defines properties for all configuration options required by Sprout.
* Developers can define additional configuration options required for their applications. Developer
* defined appliction configuration options are maintained separately in an associative array but
* behave like standard configuration options otherwise.
*/
class Application {

	/**
	 * An associative array to hold developer-defined application configuration options
	 *
	 * @var array
	 */
	private static $_user_defined_options = array();


	/**
	 * The current application environment
	 *
	 * This will hold the string name of the current loaded environment. This will be used to
	 * include the environment configuration.
	 *
	 * @var string
	 */
	protected static $environment;


	/**
	 * Label-friendly app name
	 *
	 * This is used to uniquely label database names, session stores, cookies, etc.
	 *
	 * @var string
	 */
	protected static $basename;


	/**
	 * Path to the directory containing your application code
	 *
	 * @var string
	 */
	protected static $path;


	/**
	 * Path to the application's view cache folder (must be web writable)
	 *
	 * @var string
	 */
	protected static $cache_dir;


	/**
	 * Path to a writable directory for temporary file storage
	 *
	 * @var string
	 */
	protected static $temp_dir;


	/**
	 * Path to the application's webroot
	 *
	 * In development, this should be a subdirectory of your application directory so that UI assets
	 * can be managed with the application with a version control system such as Git SCM. In
	 * production, the webroot should be outside of the webroot for greater security.
	 *
	 * @var string
	 */
	protected static $webroot;


	/**
	 * Define and return the appliction/environment configuration options
	 *
	 * @param array $options An associative array of configuration options
	 * @return array
	 **/
	public static function config($config = null) {
		if ($config && is_array($config)) {
			// Loop through the array and attempt to set each of it's keys to a defined property or
			// add it to the user defined options array.
			foreach ($config as $option => $value) {
				if (property_exists(get_class(), $option)) {
					self::set($option, $value);
				} else {
					self::$_user_defined_options[$option] = $value;
				}
			}

			if (!empty(self::$environment)) {
				$environment_configs = self::get('path') . DS . 'config' . DS . 'environments';
				require_once $environment_configs . DS . self::get('environment') . '.php';
			}
		}

		$properties = array();
		foreach (array_keys(get_class_vars(__CLASS__)) as $property) {
			if ($property != '_user_defined_options') {
				$properties[$property] = self::get($property);
			}
		}

		return $properties + self::$_user_defined_options;
	}


	/**
	 * Set a named configuration option
	 *
	 * @param $option_name The name of the configuration option to retrieve
	 * @return void
	 */
	public static function set($option, $value) {
		$option = Inflector::underscore($option);

		switch ($option) {
			case 'basename':
				$value = Inflector::underscore($value);
			break;

			case 'path':
			case 'webroot':
				if (!file_exists(realpath($value))) {
					throw new Exception($option . ' ' . realpath($value) . ' does not exist.');
				}

				$value = realpath($value);
			break;

			case 'cache_dir':
			case 'temp_dir':
				if (!file_exists(realpath($value)) && is_writable(dirname($value))) {
					mkdir($value);
				}

				if (!file_exists(realpath($value)) || !is_writable(realpath($value))) {
					throw new Exception($option . ' ' . realpath($value) . ' does not exist or is not writable.');
				}

				$value = realpath($value);
			break;
		}

		self::${$option} = $value;
	}


	/**
	 * Get a named configuration option
	 *
	 * @param $option The name of the configuration option to retrieve
	 * @return mixed
	 */
	public static function get($option) {
		$option = Inflector::underscore($option);
		$value = property_exists(__CLASS__, $option) ? self::${$option} : null;

		if (!isset($value)) {
			$value = self::get_default($option);
		}

		if (!isset($value)) {
			if (isset(self::$_user_defined_options[$option])) {
				$value = self::$_user_defined_options[$option];
			} else {
				throw new Exception('Trying to get undefined configuration option ' . $option . '.');
			}
		}

		return $value;
	}


	/**
	 * Returns the default value for a given property
	 *
	 * @param $option The name of the configuration option for which to retrieve the default value
	 * @return mixed
	 */
	private static function get_default($option) {
		$value = null;

		switch ($option) {
			case 'environment':
				$value = 'default';
			break;

			case 'basename':
				$value = Inflector::underscore(basename(self::get('path')));
			break;

			case 'path':
				$value = realpath('../../app_seed');
			break;

			case 'webroot':
				$value = self::get('path') . DS . 'webroot';
			break;

			case 'cache_dir':
				$value = self::get('path') . DS . 'cache';
			break;

			case 'temp_dir':
				$value = sys_get_temp_dir();
			break;

			case 'connections':
				$value = array();
			break;
		}

		return $value;
	}
}