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
 * Utility methods for manipulating the word delimiters, singularity and case of strings.
 */
class Inflector {

	/**
	 * Words whose singular and plural forms are the same
	 */
	private static $_uncountables = array(
		'beef',
		'cotton',
		'data',
		'deer',
		'electricity',
		'entertainment',
		'equipment',
		'fiction',
		'fish',
		'flour',
		'furniture',
		'gold',
		'happiness',
		'homework',
		'ice',
		'information',
		'knowledge',
		'literature',
		'means',
		'milk',
		'money',
		'music',
		'offspring',
		'pork',
		'rice',
		'series',
		'sheep',
		'species',
		'sunshine',
		'tennis',
		'thunder',
		'traffic',
		'weather'
	);


	/**
	 * Words whose singular and plural forms are irregularly related
	 */
	private static $_irregulars = array(
		'alumnus' => 'alumni',
		'analysis' => 'analyses',
		'appendix' => 'appendices',
		'axis' => 'axes',
		'bacterium' => 'bacteria',
		'basis' => 'bases',
		'cactus' => 'cacti',
		'child' => 'children',
		'corpus' => 'corpora',
		'crisis' => 'crises',
		'criterion' => 'criteria',
		'curriculum' => 'curricula',
		'datum' => 'data',
		'diagnosis' => 'diagnoses',
		'ellipsis' => 'ellipses',
		'foot' => 'feet',
		'genus' => 'genera',
		'goose' => 'geese',
		'hypothesis' => 'hypotheses',
		'louse' => 'lice',
		'man' => 'men',
		'medium' => 'media',
		'memorandum' => 'memoranda',
		'mouse' => 'mice',
		'move' => 'moves',
		'nebula' => 'nebulae',
		'nucleus' => 'nuclei',
		'oasis' => 'oases',
		'ox' => 'oxen',
		'paralysis' => 'paralyses',
		'parenthesis' => 'parentheses',
		'person' => 'people',
		'phenomenon' => 'phenomena',
		'radius' => 'radii',
		'sex' => 'sexes',
		'stimulus' => 'stimuli',
		'stratum' => 'strata',
		'synopsis' => 'synopses',
		'synthesis' => 'syntheses',
		'thesis' => 'theses',
		'tooth' => 'teeth',
		'vertebra' => 'vertebrae',
		'vita' => 'vitae',
		'woman' => 'women'
	);


	/**
	 * Extend the list of uncountables
	 *
	 * @param array $uncountables An array of uncountable words
	 * @return void
	 */
	public function uncountables(array $uncountables) {
		foreach ($uncountables as $word) {
			if (!in_array($word, self::$_uncountables)) {
				self::$_uncountables[] = $word;
			}
		}
	}


	/**
	 * Extend the list of irregular plurals
	 *
	 * @param array $relationships An associative array of singular => plural word relationships
	 * @return void
	 */
	public function irregulars(array $relationships) {
		self::$_irregulars = $relationships + self::$_irregulars;
	}


	/**
	* Pluralizes a noun.
	*
	* @param string $text A noun to pluralize
	* @return string Plural noun
	*/
	public static function pluralize($text) {
		$plural_rules = array(
			'/(quiz)$/i' => '\1zes',
			'/^(ox)$/i' => '\1en',
			'/([m|l])ouse$/i' => '\1ice',
			'/(matr|vert|ind)ix|ex$/i' => '\1ices',
			'/(x|ch|ss|sh)$/i' => '\1es',
			'/([^aeiouy]|qu)ies$/i' => '\1y',
			'/([^aeiouy]|qu)y$/i' => '\1ies',
			'/(hive)$/i' => '\1s',
			'/(?:([^f])fe|([lr])f)$/i' => '\1\2ves',
			'/sis$/i' => 'ses',
			'/([ti])um$/i' => '\1a',
			'/(buffal|tomat)o$/i' => '\1oes',
			'/(bu)s$/i' => '\1ses',
			'/(alias|status)/i'=> '\1es',
			'/(octop|vir)us$/i'=> '\1i',
			'/(ax|test)is$/i'=> '\1es',
			'/s$/i'=> 's',
			'/$/'=> 's'
		);

		$lowercased_text = strtolower($text);

		foreach (self::$_uncountables as $uncountable) {
			if (substr($lowercased_text, (-1 * strlen($uncountable))) == $uncountable) {
				return $text;
			}
		}

		foreach (self::$_irregulars as $plural => $singular) {
			if (preg_match('/(' . $plural . ')$/i', $text, $arr)) {
				return preg_replace('/(' . $plural . ')$/i', substr($arr[0], 0, 1) . substr($singular, 1), $text);
			}
		}

		foreach ($plural_rules as $rule => $replacement) {
			if (preg_match($rule, $text)) {
				return preg_replace($rule, $replacement, $text);
			}
		}

		return false;
	}


	/**
	* Singularizes an English noun.
	*
	* @param string $text English noun to singularize
	* @return string Singular noun.
	*/
	static function singularize($text) {
		$singular_rules = array (
			'/(quiz)zes$/i' => '\\1',
			'/(matr)ices$/i' => '\\1ix',
			'/(vert|ind)ices$/i' => '\\1ex',
			'/^(ox)en/i' => '\\1',
			'/(alias|status)es$/i' => '\\1',
			'/([octop|vir])i$/i' => '\\1us',
			'/(cris|ax|test)es$/i' => '\\1is',
			'/(shoe)s$/i' => '\\1',
			'/(o)es$/i' => '\\1',
			'/(bus)es$/i' => '\\1',
			'/([m|l])ice$/i' => '\\1ouse',
			'/(x|ch|ss|sh)es$/i' => '\\1',
			'/(m)ovies$/i' => '\\1ovie',
			'/(s)eries$/i' => '\\1eries',
			'/([^aeiouy]|qu)ies$/i' => '\\1y',
			'/([lr])ves$/i' => '\\1f',
			'/(tive)s$/i' => '\\1',
			'/(hive)s$/i' => '\\1',
			'/([^f])ves$/i' => '\\1fe',
			'/(^analy)ses$/i' => '\\1sis',
			'/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i' => '\\1\\2sis',
			'/([ti])a$/i' => '\\1um',
			'/(n)ews$/i' => '\\1ews',
			'/s$/i' => '',
		);

		$lowercased_text = strtolower($text);
		foreach (self::$_uncountables as $uncountable) {
			if (substr($lowercased_text, (-1 * strlen($uncountable))) == $uncountable) {
				return $text;
			}
		}

		foreach (self::$_irregulars as $singular => $plural) {
			if (preg_match('/(' . $plural . ')$/i', $text, $arr)) {
				return preg_replace('/(' . $plural . ')$/i', substr($arr[0], 0, 1) . substr($singular, 1), $text);
			}
		}

		foreach ($singular_rules as $rule => $replacement) {
			if (preg_match($rule, $text)) {
				return preg_replace($rule, $replacement, $text);
			}
		}

		return $text;
	}


	/**
	 * Gets the plural form of $text if first parameter is greater than 1
	 *
	 * @param string $text
	 * @param integer $item_count
	 * @return string Pluralized string when number of items is greater than 1
	 */
	static function conditional_plural($text, $item_count) {
		return $item_count > 1 ? self::pluralize($text) : $text;
	}


	/**
	* Converts an underscored or CamelCase string into a English phrase.
	*
	* The titleize function converts a string like "WelcomePage", "welcome_page" or "welcome page"
	* to this "Welcome Page". If second parameter is set to "first" it will only capitalize the
	* first character of the title.
	*
	* @param string $text Text to format as tile
	* @param string $uppercase If set to 'first' titleize will only uppercase the first character.
	*    Otherwise all words will be capitalized.
	* @return string $text formatted as title
	*/
	static function titleize($text, $uppercase = '') {
		$uppercase = $uppercase == 'first' ? 'ucfirst' : 'ucwords';
		return $uppercase(self::humanize(self::underscore($text)));
	}


	/**
	* Gets the CamelCased form of $text.
	*
	* Converts a phrase like "send_email" to "SendEmail". It will remove non-alphanumeric character
	* from $text, so "who's online" will be converted to "WhoSOnline"
	*
	* @param string $text Text to convert to camel case
	* @return string UpperCamelCasedText
	*/
	static function camelize($text) {
		if (preg_match_all('/\/(.?)/', $text, $got)) {
			foreach ($got[1] as $k=>$v) {
				$got[1][$k] = '::' . strtoupper($v);
			}
			$text = str_replace($got[0], $got[1], $text);
		}
		return str_replace(' ', '', ucwords(preg_replace('/[^A-Z^a-z^0-9^:]+/', ' ', $text)));
	}


	/**
	 * Converts text to its controller class version
	 *
	 * Pluralizes a word and adds 'Controller'
	 */
	public static function controllerize($text) {
		$text = str_ireplace('_controller', '', $text);
		$text = str_ireplace('Controller', '', $text);
		return self::camelize(self::unaccent($text)) . 'Controller';
	}


	/**
	* Converts text to "it_s_underscored_version"
	*
	* Converts any "CamelCased" or "ordinary Phrase" to an "underscored_phrase".
	*
	* @param string $text Text to underscore
	* @return string Underscored version of $text
	*/
	static function underscore($text) {
		return trim(
			strtolower(
				preg_replace('/[^A-Z^a-z^0-9^\/]+/', '_',
					preg_replace('/([a-z\d])([A-Z])/', '\1_\2',
						preg_replace('/([A-Z]+)([A-Z][a-z])/', '\1_\2',
							preg_replace('/::/', '/', self::unaccent($text))
						)
					)
				)
			)
		);
	}


	/**
	* Returns a human-readable version of $text.
	*
	* Returns a human-readable version of $text by replacing underscores with spaces and by upper-
	* casing the initial character by default. If you need to uppercase all words, pass in all' as
	* the second parameter.
	*
	* @param string $text String to "humanize"
	* @param string $uppercase If set to 'all' it will uppercase all words.
	* @return string Human-readable version of $text
	*/
	static function humanize($text, $uppercase = '') {
		$uppercase = $uppercase == 'all' ? 'ucwords' : 'ucfirst';
		return $uppercase(str_replace('_', ' ', preg_replace('/_id$/', '', $text)));
	}


	/**
	* Converts a class name to its table name.
	*
	* Returns the lowercased, underscored, plural version of a word. For example, tableize converts
	* "Person" to "people" and "Thing" to "things".
	*
	* @see classify
	* @param string $class_name Class name for getting related table_name.
	* @return string plural_table_name
	*/
	static function tableize($class_name) {
		return self::pluralize(self::underscore($class_name));
	}


	/**
	* Converts a table name to its class name
	*
	* Takes the CamelCased, singular version of a word. Converts "people" to "Person" and "things"
	* to "Thing".
	*
	* @see tableize
	* @param string $table_name Table name for getting related ClassName.
	* @return string SingularClassName
	*/
	static function classify($table_name) {
		return self::camelize(self::singularize($table_name));
	}


	/**
	* Converts number to its ordinal English form.
	*
	* Converts 13 to 13th, 2 to 2nd ...
	*
	* @param integer $number Number to get its ordinal value
	* @return string Ordinal representation of given string.
	*/
	static function ordinalize($number) {
		$suffix = '';

		if (in_array(($number % 100), range(11, 13))) {
			$suffix = 'th';
		} else {
			switch (($number % 10)) {
				case 1:
					$suffix = 'st';
					break;

				case 2:
					$suffix = 'nd';
					break;

				case 3:
					$suffix = 'rd';
					break;

				default:
					$suffix = 'th';
					break;
			}
		}
		return $number . $suffix;
	}


	/**
	 * Transforms a string to its unaccented version.
	 *
	 * @param string $text Text to be converted
	 * @return string Unaccented version of $text
	 */
	static function unaccent($text) {
		return strtr($text,
			'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûüýþÿ',
			'AAAAAAACEEEEIIIIDNOOOOOOUUUUYTsaaaaaaaceeeeiiiienoooooouuuuyty'
		);
	}


	/**
	* Converts text to "it-s-dashed-version"
	*
	* Converts any "CamelCased" or "ordinary Phrase" into a "dashed-phrase".
	*
	* @param string $text Text to underscore
	* @return string Dashed version of $text
	*/
	static function dashify($text) {
		return str_replace('_', '-', trim(self::underscore($text),'_'));
	}


	/**
	* Formats a string for use as a URL
	*
	* Converts any "CamelCased" or "ordinary Phrase" into a "dashed-phrase" and replaces accented
	* characters their unaccented equivalents.
	*
	* @param string $text Text to underscore
	* @return string URL formatted version of $text
	*/
	static function urlize($string) {
		return self::dashify(self::unaccent($string));
	}
}