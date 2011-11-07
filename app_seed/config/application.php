<?php
/**
 * Copyright (c) <YEAR>, <YOU OR YOUR ORGANIZATION>
 * All rights reserved.
*/

\Sprout\Config::load(array(
	// 'environment' => 'development',
	// 'app_title' => 'Demo',
	// 'app_basename' => 'demo',
	'app_dir' => dirname(__DIR__),
	'cache_dir' => dirname(__DIR__) . DS . 'cache'
	// 'connections' => array(
	// 	'development' => 'mysql://username:password@host/database'
	// )
));
