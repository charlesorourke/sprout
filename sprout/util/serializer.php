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

namespace Sprout\Util;

/**
 * Serialization
 *
 * Encodes array data to a specific format
 **/
class Serializer {

	/**
	 * Serialize an array of data to a given format
	 *
	 * @param mixed $data The source data being serialized
	 * @param string $format The requested format
	 * @return mixed
	 */
	public static function serialize($data, $format) {
		$data = self::to_array($data);
		$to_format = 'to_' . $format;

		return self::$to_format($data);
	}


	/**
	 * Recursively convert an array of data and objects to an array
	 */
	public static function to_array($data) {
		$array = array();
		foreach ($data as $key => $value) {
			if (!is_object($value)) {
				$array[$key] = $value;
			} else {
				if (method_exists($value, 'to_array')) {
					$array[$key] = $value->to_array();
				} else {
					// get what we can out of the object
					$object_array = array();
					$properties = get_object_vars($value);
					foreach ($properties as $property => $val) {
						$object_array[$property] = $val;
					}
					$array[$key] = $array_value;
				}
			}
		}
		return $array;
	}


	/**
	 * Convert an array of data to JSON
	 *
	 * @param array $data Array of source data to be converted
	 * @return string The JSON encoded string of $data
	 */
	public static function to_json($data) {
		return json_encode(self::to_array($data));
	}


	/**
	 * Convert an array of data to XML
	 *
	 * @param array $data Array of source data to be converted
	 * @return string The XML encoded string of $data
	 */
	public static function to_xml($data) {
		return XMLSerializer::xml_encode($data);
	}
}