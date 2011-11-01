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

namespace Sprout\HTTP;

/**
 * HTTP response
 */
class Response {

	/**
	 * Response content body
	 *
	 * @var string
	 */
	public $body;


	/**
	 * Response status: 200, 404, 301, etc.
	 *
	 * @var integer
	 */
	public $status;


	/**
	 * Location header - used for redirection
	 *
	 * @var string
	 */
	public $location;


	/**
	 * Content-type header
	 *
	 * @var string
	 */
	public $content_type;


	/**
	 * Charset header
	 *
	 * @var string
	 */
	public $charset = 'utf8';


	/**
	 * Whether the response has been sent to the client
	 *
	 * @var boolean
	 */
	private $_sent = false;


	/**
	 * Base set of mime types indexed by file extension
	 *
	 * @var array
	 */
	private $_mime_type = array(
		'3g2' => 'video/3gpp2',
		'3gp' => 'video/3gp',
		'aac' => 'audio/x-acc',
		'ac3' => 'audio/ac3',
		'ai' => 'application/postscript',
		'aif' => array('audio/x-aiff', 'audio/aiff'),
		'aifc' => 'audio/x-aiff',
		'aiff' => array('audio/x-aiff', 'audio/aiff'),
		'au' => 'audio/x-au',
		'avi' => array('video/x-msvideo', 'video/msvideo', 'video/avi', 'application/x-troff-msvideo'),
		'bin' => array('application/macbinary', 'application/mac-binary', 'application/octet-stream', 'application/x-binary', 'application/x-macbinary'),
		'bmp' => array('image/bmp', 'image/x-windows-bmp'),
		'cer' => array('application/pkix-cert', 'application/x-x509-ca-cert'),
		'class' => 'application/octet-stream',
		'cpt' => 'application/mac-compactpro',
		'crl' => array('application/pkix-crl', 'application/pkcs-crl'),
		'crt' => array('application/x-x509-ca-cert', 'application/x-x509-user-cert', 'application/pkix-cert'),
		'csr' => 'application/octet-stream',
		'css' => 'text/css',
		'csv' => array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel'),
		'dcr' => 'application/x-director',
		'der' => 'application/x-x509-ca-cert',
		'dir' => 'application/x-director',
		'dll' => 'application/octet-stream',
		'dms' => 'application/octet-stream',
		'doc' => 'application/msword',
		'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
		'dvi' => 'application/x-dvi',
		'dxr' => 'application/x-director',
		'eml' => 'message/rfc822',
		'eps' => 'application/postscript',
		'exe' => array('application/octet-stream', 'application/x-msdownload'),
		'f4v' => 'video/mp4',
		'flac' => 'audio/x-flac',
		'gif' => 'image/gif',
		'gpg' => 'application/gpg-keys',
		'gtar' => 'application/x-gtar',
		'gz' => 'application/x-gzip',
		'gzip' => 'application/x-gzip',
		'hqx' => array('application/mac-binhex40', 'application/mac-binhex', 'application/x-binhex40', 'application/x-mac-binhex40'),
		'htm' => 'text/html',
		'html' => 'text/html',
		'jpe' => array('image/jpeg', 'image/pjpeg'),
		'jpeg' => array('image/jpeg', 'image/pjpeg'),
		'jpg' => array('image/jpeg', 'image/pjpeg'),
		'js' => 'application/x-javascript',
		'json' => array('application/json', 'text/json'),
		'kdb' => 'application/octet-stream',
		'lha' => 'application/octet-stream',
		'log' => array('text/plain', 'text/x-log'),
		'lzh' => 'application/octet-stream',
		'm3u' => 'text/plain',
		'm4a' => 'audio/x-m4a',
		'm4u' => 'application/vnd.mpegurl',
		'mid' => 'audio/midi',
		'midi' => 'audio/midi',
		'mif' => 'application/vnd.mif',
		'mov' => 'video/quicktime',
		'movie' => 'video/x-sgi-movie',
		'mp2' => 'audio/mpeg',
		'mp3' => array('audio/mpeg', 'audio/mpg', 'audio/mpeg3', 'audio/mp3'),
		'mp4' => 'video/mp4',
		'mpe' => 'video/mpeg',
		'mpeg' => 'video/mpeg',
		'mpg' => 'video/mpeg',
		'mpga' => 'audio/mpeg',
		'oda' => 'application/oda',
		'ogg' => 'audio/ogg',
		'p10' => array('application/x-pkcs10', 'application/pkcs10'),
		'p12' => 'application/x-pkcs12',
		'p7a' => 'application/x-pkcs7-signature',
		'p7c' => array('application/pkcs7-mime', 'application/x-pkcs7-mime'),
		'p7m' => array('application/pkcs7-mime', 'application/x-pkcs7-mime'),
		'p7r' => 'application/x-pkcs7-certreqresp',
		'p7s' => 'application/pkcs7-signature',
		'pdf' => array('application/pdf', 'application/x-download'),
		'pem' => array('application/x-x509-user-cert', 'application/x-pem-file', 'application/octet-stream'),
		'pgp' => 'application/pgp',
		'php' => 'application/x-httpd-php',
		'php3' => 'application/x-httpd-php',
		'php4' => 'application/x-httpd-php',
		'phps' => 'application/x-httpd-php-source',
		'phtml' => 'application/x-httpd-php',
		'png' => array('image/png', 'image/x-png'),
		'ppt' => array('application/powerpoint', 'application/vnd.ms-powerpoint'),
		'ps' => 'application/postscript',
		'psd' => 'application/x-photoshop',
		'qt' => 'video/quicktime',
		'ra' => 'audio/x-realaudio',
		'ram' => 'audio/x-pn-realaudio',
		'rm' => 'audio/x-pn-realaudio',
		'rpm' => 'audio/x-pn-realaudio-plugin',
		'rsa' => 'application/x-pkcs7',
		'rtf' => 'text/rtf',
		'rtx' => 'text/richtext',
		'rv' => 'video/vnd.rn-realvideo',
		'sea' => 'application/octet-stream',
		'shtml' => 'text/html',
		'sit' => 'application/x-stuffit',
		'smi' => 'application/smil',
		'smil' => 'application/smil',
		'so' => 'application/octet-stream',
		'sst' => 'application/octet-stream',
		'swf' => 'application/x-shockwave-flash',
		'tar' => 'application/x-tar',
		'text' => 'text/plain',
		'tgz' => array('application/x-tar', 'application/x-gzip-compressed'),
		'tif' => 'image/tiff',
		'tiff' => 'image/tiff',
		'txt' => 'text/plain',
		'vlc' => 'application/videolan',
		'wav' => 'audio/x-wav',
		'wbxml' => 'application/wbxml',
		'wmlc' => 'application/wmlc',
		'wmv' => 'video/x-ms-wmv',
		'word' => array('application/msword', 'application/octet-stream'),
		'xht' => 'application/xhtml+xml',
		'xhtml' => 'application/xhtml+xml',
		'xl' => 'application/excel',
		'xls' => array('application/excel', 'application/vnd.ms-excel', 'application/msexcel'),
		'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
		'xml' => 'text/xml',
		'xsl' => 'text/xml',
		'xspf' => 'application/xspf+xml',
		'zip' => array('application/x-zip', 'application/zip', 'application/x-zip-compressed')
	);


	/**
	 * Sends the response to the client
	 *
	 * @return void
	 */
	public function send() {
		if ($this->_sent || headers_sent()) {
			throw new Exception('Response already sent.');
		} else {
			$this->_sent = true;
			echo $this->body;
		}
	}


	/**
	 * Sets the correct mime type based on the extension given
	 *
	 * @param string $extension The string file extension -- i.e. html, json, xml
	 * @return void
	 */
	public function set_mime_type($extension) {
		if (isset($this->_mime_type[$extension])) {
			$this->content_type = $this->_mime_type[$extension];
		} else {
			$this->content_type = $this->_mime_type['txt'];
		}

		if (is_array($this->content_type) && count($this->content_type) > 0) {
			$this->content_type = $this->content_type[0];
		}

		header('Content-type: '. $this->content_type);
	}
}