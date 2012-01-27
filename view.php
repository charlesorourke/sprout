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
		if (!in_array($this->format, $this->_data_formats) && !file_exists($this->_template_path())) {
			throw new Exception('View template ' . $this->_template_path() . ' does not exist.');
		}
	}


	/**
	 * Getter magic method
	 *
	 * Checks to see if the requested property has an method of the same name and if so, utilizes it
	 * to return its value. Otherwise, it returns the property's value as expected.
	 *
	 * @param string $name The property requested
	 * @return mixed The property value
	 **/
	public function __get($name) {
		switch ($name) {
			case 'full_path':
				$value = $this->$name();
			break;

			default:
				$value = $this->$name;
			break;
		}

		return $value;
	}


	/**
	 * Setter magic method
	 *
	 * Checks to see if the property being set has a method of the same name for setting its value
	 * and if so, uses it to set the value. Otherwise, it sets the property as expected.
	 *
	 * @param string $name The name of the variable being set
	 * @param mixed $value The value to set
	 * @return void
	 **/
	public function __set($name, $value) {
		if (is_callable(array($name, $name))) {
			$value = $this->$name($value);
		}

		$this->$name = $value;
	}


	/**
	 * Renders data into a view template
	 *
	 * Render takes one parameter, an associative array of data and renders it into a view template.
	 * PHP short tags in view templates are automatically converted to fully escaped PHP echo
	 * statements before being parsed by PHP.
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
			$cached_template = $this->_cached_template_path();

			// Start output buffering.
			ob_start();

			// Include the view template.
			require_once $cached_template;

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

		// If the view format requested is the "bare" template markup, separate from the layout, use
		// "html" as the value for format in the filename.
		$format = ($this->format == 'bare') ? 'html' : $this->format;

		return "{$filename}.{$format}.php";
	}


	/**
	 * Returns the full template path from root including the filename
	 *
	 * @return string
	 */
	private function _template_path() {
		return Application::get('path') . DS . 'views' . DS . $this->folder . DS . $this->template;
	}


	/**
	 * Returns the path from root for the current view template's the cache file
	 *
	 * If the template's cache file has not been created yet, it is created here. Once the cached
	 * template exists, its path is returned. Templates are created for each modification of the
	 * corresponding view. Every time a template is cached, expired caches for that template are
	 * removed.
	 *
	 * Before a view template is cached, short PHP open tags are replaced with full PHP open tags
	 * and short echo tags are replaced with PHP echo statements escaped with $this->escape(). This
	 * enables the use of PHP's short tag syntax even if short_open_tag is off in the server's
	 * php.ini, as it should be for greater security. PHP short echo tags echoing $this->* are not
	 * automatically escaped so to not interfere with helper functions that return markup.
	 *
	 * @uses Inflector::underscore
	 * @uses View::_template_path
	 * @see View::render
	 * @return string
	 */
	private function _cached_template_path() {
		$cache_dir = Application::get('cache_dir');
		$stats = stat($this->_template_path());

		// $template: folder_template_file_format
		$template = $this->folder . '_' . substr($this->template, 0, strlen($this->template) - 4);
		$template = Inflector::underscore($template);

		// $template_path: /path/to/cache/folder_template_file_format_timestamp_size.php
		$template_path = $cache_dir . DS . "{$template}_{$stats['mtime']}_{$stats['size']}.php";

		// Create the cache file for the current modification of the view if doesn' exist yet.
		if (!file_exists($template_path)) {

			// Define patterns to replace php short tags with php tags and short echo tags with
			// escaped php echo tags.
			$patterns = array(
				'/\<\?=\s*\$this->(.+?)\s*;?\s*\?>/msx' => '<? echo $this->$1; ?>',
				'/\<\?=\s*(.+?)\s*;?\s*\?>/msx' => '<? echo $this->escape($1); ?>',
				'/\<\?(php)?\s*(.+?)\s*(;)?\s*\?>/msx' => '<?php $2$3 ?>'
			);
			$contents = file_get_contents($this->_template_path());
			$contents = preg_replace(array_keys($patterns), array_values($patterns), $contents);

			// Once the new template cache file is written, remove expired template caches.
			if (file_put_contents($template_path, $contents) !== false) {
				foreach (glob("{$cache_dir}/{$template}_*.php") as $expired_template_path) {
					if ($expired_template_path !== $template_path) {
						unlink($expired_template_path);
					}
				}
			}
		}

		return $template_path;
	}
}