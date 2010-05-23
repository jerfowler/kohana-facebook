<?php defined('SYSPATH') or die('No direct script access.');

// Facebook route for xd_receiver
Route::set('facebook', '<page>', array('page' => 'xd_receiver.htm'))
	->defaults(array(
		'controller' => 'facebook',
		'action' => 'xdreceiver',
		'class' => NULL));
