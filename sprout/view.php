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

use \Sprout\Util\Serializer;

/**
 * View template
 *
 * Handles the loading and rendering of view templates
 **/
class View {

	/**
	 * Filename of the view template
	 *
	 * @var string
	 **/
	protected $template;


	/**
	 * View folder in in {app}/views containing the template
	 *
	 * @var string
	 **/
	protected $folder;


	/**
	 * Full path to the view template including the filename
	 *
	 * @var string
	 **/
	protected $full_path;


	/**
	 * The file extension of the format requested
	 *
	 * @var string
	 **/
	protected $format;


	/**
	 * Array of data formats with built-in handlers
	 *
	 * @var array
	 */
	private $_data_formats = array('json', 'xml');


	/**
	 * Create a new view object
	 *
	 * @param string $folder The folder in {app}/views containing the template
	 * @param string $template The view template filename
	 * @return void
	 */
	public function __construct($folder, $template) {
		$this->folder = $this->_folder($folder);
		$this->template = $this->_template($template);

		// If the format requested doesn't have a built-in data handler and the view template does
		// not exist, throw an exception.
		if (!in_array($this->format, $this->_data_formats) && !file_exists($this->_full_path())) {
			throw new Exception('View template ' . $this->_full_path() . ' does not exist.');
		}
	}


	/**
	 * Getter magic method
	 *
	 * Checks to see if the requested property has an method of the same name and if so, utilizes it
	 * to return its value.  Otherwise, it returns the property's value as expected.
	 *
	 * @param string $varname The property requested
	 * @return mixed The property value
	 **/
	public function __get($varname) {
		switch ($varname) {
			case 'full_path':
				$value = $this->$varname();
			break;

			default:
				$value = $this->$varname;
			break;
		}

		return $value;
	}


	/**
	 * Setter magic method
	 *
	 * Checks to see if the property being set has a method of the same name for setting its value
	 * and if so, uses it to set the value.  Otherwise, it sets the property as expected.
	 *
	 * @param string $varname The name of the variable being set
	 * @param mixed $value The value to set
	 * @return void
	 **/
	public function __set($varname, $value) {
		if (is_callable(array($this, $varname))) {
			$value = $this->$varname($value);
		}

		$this->$varname = $value;
	}


	/**
	 * Renders data into a view template
	 *
	 * Render takes one parameter, an associative array of data and renders it into a view template.
	 *
	 * @param array $data Associative array of data
	 * @return string
	 */
	public function render($data = array()) {

		// Create local variables for all of the extracted data. Extract variables by reference so
		// that the view data can be conveniently passed to partials if desired.
		if (is_array($data)) {
			extract($data, EXTR_REFS);
		} else {
			throw new Exception('Invalid data passed to ' . __METHOD__ . '.');
		}

		// If the format requested has a built-in handler, template is not required.
		if (in_array($this->format, $this->_data_formats)) {
			$content = Serializer::serialize($data, $this->format);
		} else {
			// Start output buffering.
			ob_start();

			// Include the view template.
			require_once $this->_full_path();

			// End output buffering and store the current buffer.
			$content = ob_get_clean();
		}

		return $content;
	}


	/**
	 * Escapes values to prevent SQL injection and other unexpected behavior
	 *
	 * @param string $value
	 * @return mixed
	 */
	public function escape($value) {
		// If $value is an array, escape each of the array values.
		if (is_array($value)) {
			$escaped_value = array_map(array($this, __FUNCTION__), $value);
		} else {
			$escaped_value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
		}

		return $escaped_value;
	}


	/**
	 * Ensure the folder of the view template is formatted correctly
	 *
	 * @param string $name The folder name
	 * @return string
	 */
	private function _folder($name) {
		return Inflector::underscore($name);
	}


	/**
	 * Ensure the template filename is formatted correctly
	 *
	 * @param string $name The filename of the view template
	 * @return string
	 */
	private function _template($name) {
		$filename = $name;

		// Remove the php extension if specified since it will be added back later.
		if (substr(strtolower($filename), strlen($filename) - 4) == '.php') {
			$filename = substr($filename, 0, strlen($filename) - 4);
		}

		// Find the extension in formats like: template, template.json, or template.json
		$dot_count = substr_count($filename, '.');
		if ($dot_count == 1) {
			$this->format = substr($filename, strpos($filename, '.') + 1);
		} elseif ($dot_count == 0) {
			$this->format = Router::default_format();
		} else {
			throw new Exception('Invalid template name format ' . $name . '.');
		}

		$filename = Inflector::underscore(str_replace('.' . $this->format, '', $filename));

		return "{$filename}.{$this->format}.php";
	}


	/**
	 * Returns the full template path from root including the filename
	 *
	 * @return string
	 */
	private function _full_path() {
		return Config::get('app_dir') . DS . 'views' . DS . $this->folder . DS . $this->template;
	}
}