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

use \XmlWriter;

/**
 * XML serialization
 */
class XMLSerializer extends Serializer {

	/**
	 * @var XmlWriter
	 */
	private static $_writer;


	/**
	 * Encode an associative array of data to an XML document
	 *
	 * @param array $data Associative array of data
	 * @param boolean $skip_instruct Whether to include the XML instruction
	 * @return string XML string version of $data
	 */
	public static function xml_encode($data, $skip_instruct = false) {
		$data = self::to_array($data);

		self::$_writer = new XmlWriter();
		self::$_writer->openMemory();
		self::$_writer->startDocument('1.0', 'UTF-8');
		self::$_writer->startElement('data');
		self::_write($data);
		self::$_writer->endElement();
		self::$_writer->endDocument();
		$xml = self::$_writer->outputMemory(true);

		// The XML instruction may optionally be left off.  This is useful when concatenating
		// several XML strings together for example.
		if ($skip_instruct == true) {
			$xml = preg_replace('/<\?xml version.*?\?>/', '', $xml);
		}

		return $xml;
	}


	/**
	 * Writes an XML tag and its contents recursively
	 *
	 * Builds the output string stored in self::$_writer's memory.
	 *
	 * @param array $data Associative array of data
	 * @param string $tag (optional) The tag name of a particular element
	 * @return void
	 */
	private function _write($data, $tag = null) {
		foreach ($data as $attr => $value) {
			if ($tag != null)
				$attr = $tag;

			if (is_array($value) || is_object($value)) {
				if (!is_int(key($value))) {
					self::$_writer->startElement($attr);
					self::_write($value);
					self::$_writer->endElement();
				} else {
					self::_write($value, $attr);
				}

				continue;
			}

			self::$_writer->writeElement($attr, $value);
		}
	}
}