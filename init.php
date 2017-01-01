<?php

$path = __DIR__ . '/vendor/facebook-php-sdk';

Route::set('FB-auth', 'facebook/auth')
->defaults(array(
	'controller' => 'facebook',
	'action' => 'callback'
));

require $path.'/src/Facebook/autoload.php';
require $path.'/src/Facebook/polyfills.php';
