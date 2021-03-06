<?php

// Define the core paths
// Define them as absolute paths to make sure that require_once works as expected

// DIRECTORY_SEPARATOR is a PHP pre-defined constant
// (\ for Windows, / for Unix)
defined('DS') ? null : define('DS', DIRECTORY_SEPARATOR);

defined('SITE_ROOT') ? null : 
	define('SITE_ROOT', DS.'Users'.DS.'Don'.DS.'Sites'.DS.'CoronaPoint');

defined('LIB_PATH') ? null : define('LIB_PATH', SITE_ROOT.DS.'private');

defined('URL') ? null :define('URL', 'http://local.corona.happycollision.com');

defined('TEMPLATE_PATH') ? null :define('TEMPLATE_PATH', SITE_ROOT.DS.'public'.DS.'templates'.DS );

//setting timezone to central time for all users
date_default_timezone_set('America/Chicago');

// load config file first
require_once(LIB_PATH.DS.'config.php');

// load basic functions next so that everything after can use them
require_once(LIB_PATH.DS.'functions.php');

// load core objects
require_once(LIB_PATH.DS.'session.php');
require_once(LIB_PATH.DS.'database.php');
require_once(LIB_PATH.DS.'database_object.php');
require_once(LIB_PATH.DS.'pagination.php');
require_once(LIB_PATH.DS.'form_error.php');
require_once(LIB_PATH.DS.'error_code.php');
require_once(LIB_PATH.DS.'search.php');

// load database table related classes
require_once(LIB_PATH.DS.'user.php');
require_once(LIB_PATH.DS.'call.php');
require_once(LIB_PATH.DS.'call_edit.php');
require_once(LIB_PATH.DS.'site.php');


?>