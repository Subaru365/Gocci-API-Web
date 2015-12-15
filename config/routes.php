<?php
return array(
	'_root_'  => 'welcome/404',  // The default route
	'_404_'   => 'http/404',    // The main 404 route fuel/app/classes/controller/http
	'hello(/:name)?' => array('welcome/hello', 'name' => 'hello'),
);