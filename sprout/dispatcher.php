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

use \Sprout\HTTP\Request;

/**
* Dispatch a new HTTP requests
*/
class Dispatcher {

	function dispatch() {
		Session::start();
		$request = new Request;
		?>
		<!DOCTYPE html>
		<html lang="en">
		<head>
			<meta charset="utf-8">
			<title>Sprout</title>
		</head>
		<body>
			<h3>Request tests</h3>
			<ul>
				<li><a href="http://sprout.dev/">http://sprout.dev/</a></li>
				<li><a href="http://sprout.dev/index">http://sprout.dev/index</a></li>
				<li><a href="http://sprout.dev/index.html">http://sprout.dev/index.html</a></li>
				<li><a href="http://sprout.dev/tasks">http://sprout.dev/tasks</a></li>
				<li><a href="http://sprout.dev/tasks.json">http://sprout.dev/tasks.json</a></li>
				<li><a href="http://sprout.dev/store/products/ABC-111222333444">http://sprout.dev/store/products/ABC-111222333444</a></li>
				<li><a href="http://sprout.dev/store/products/ABC-111222333444.partial">http://sprout.dev/store/products/ABC-111222333444.partial</a></li>
				<li><a href="http://sprout.dev/user-accounts/edit_profile/1234/key:value/test:a,b,c,d,e/test:f,g/foo:bar?foo=baz">http://sprout.dev/user-accounts/edit_profile/1234/key:value/test:a,b,c,d,e/test:f,g/foo:bar?foo=baz</a></li>
			</ul>

			<pre>Request = <?=print_r(get_object_vars($request), true)?></pre>
		</body>
		</html>
		<?php
	}
}