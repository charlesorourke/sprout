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
 * HTML page layout
 **/
class Layout extends View {

	/**
	 * The view template rendered with action data
	 *
	 * @var string
	 **/
	protected $content;


	/**
	 * Alias of the current controller base name
	 *
	 * For example, with a PagesController, $controller_name would be "pages".
	 *
	 * @var string
	 **/
	protected $controller_name;


	/**
	 * Alias of the current action name
	 *
	 * @var string
	 **/
	protected $action_name;


	/**
	 * The page title; defaults to the current action name
	 *
	 * @var string
	 **/
	protected $title;


	/**
	 * The request object that got us here
	 *
	 * @var Request
	 **/
	protected $request;


	/**
	 * The response object used to respond to the client
	 *
	 * @var Response
	 **/
	protected $response;


	/**
	 * Create a new layout object
	 *
	 * @param string $template The layout template filename
	 * @return void
	 */
	function __construct($template) {
		parent::__construct('layouts', $template);
	}


	public function scripts() {}


	public function stylesheets() {}
}