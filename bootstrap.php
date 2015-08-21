<?php
// Bootstrap the framework DO NOT edit this
require COREPATH.'bootstrap.php';

\Autoloader::add_namespace(
	'Aws', APPPATH.'vendor/aws/aws-sdk-php/src/Aws', true
);


\Autoloader::add_classes(array(
	// Add classes you want to override here
	// Example: 'View' => APPPATH.'classes/view.php',
	'Session' => APPPATH.'classes/session.php',
));

// Register the autoloader
\Autoloader::register();


/**
 * Your environment.  Can be set to any of the following:
 *
 * Fuel::DEVELOPMENT
 * Fuel::TEST
 * Fuel::STAGING
 * Fuel::PRODUCTION
 */
\Fuel::$env = (isset($_SERVER['FUEL_ENV']) ? $_SERVER['FUEL_ENV'] : \Fuel::TEST);

// Initialize the framework with the config file.
\Fuel::init('config.php');
/*
ini_set('default_charset',            Config::get('default_charset'));
ini_set('default_mimetype',           Config::get('default_mimetype'));
ini_set('mbstring.language',          Config::get('mbstring.language'));
ini_set('mbstring.internal_encoding', Config::get('mbstring.internal_encoding'));
ini_set('mbstring.http_input',        Config::get('mbstring.http_input'));
ini_set('mbstring.http_output',       Config::get('mbstring.http_output'));
*/


