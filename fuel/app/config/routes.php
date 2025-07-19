<?php
return array(
	'_root_'  => 'welcome/index',  // The default route
	'_404_'   => 'welcome/404',    // The main 404 route
	
	'hello(/:name)?' => array('welcome/hello', 'name' => 'hello'),
	'gamepaylog/create' => 'gamepaylog/create',
	'gamepaylog/store'  => 'gamepaylog/store',
	'gamepaylog/edit/(:num)'   => 'gamepaylog/edit/$1',
	'gamepaylog/update/(:num)' => 'gamepaylog/update/$1',
	'gamepaylog/delete/(:num)' => 'gamepaylog/delete/$1',
);
