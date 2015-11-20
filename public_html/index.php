<?php

ini_set('memory_limit', '1024M');


// Set up maintenance mode
$maintenanceMode = false;

if($maintenanceMode)
{
	header('HTTP/1.1 503 Service Temporarily Unavailable');
	header('Status: 503 Service Temporarily Unavailable');
	
	include('holding.php');
	
	exit();
}
else
{
	$devIPs = array(
		'127.0.0.1', // LOCALHOST
		'50.56.21.178', // mailgun
		'173.203.37.68', // mailgun 2
	);


	date_default_timezone_set('Europe/London');
	
	// E_ALL now contains E_STRICT so we need a better way
	error_reporting(E_ERROR | E_WARNING | E_PARSE);
	
	// change the following paths if necessary
	$yii=dirname(__FILE__).'/../framework/yii.php';
	
	// Test for environments
	if(preg_match('@^/path/to/live@', dirname(__FILE__)))
	{
		$config = dirname(__FILE__) . '/protected/config/production.php';
		$debug = false;
	
		define('ENVIRONMENT', 'PRODUCTION');
	}
	else // LOCAL
	{
		$config = dirname(__FILE__) . '/protected/config/local.php';
		$debug = true;
	
		define('ENVIRONMENT', 'LOCAL');
		set_time_limit(0);
	}
	
	if($debug)
	{
		// remove the following lines when in production mode
		defined('YII_DEBUG') or define('YII_DEBUG',true);
		// specify how many levels of call stack should be shown in each log message
		defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL',3);
	}
	
	include(dirname(__FILE__) . '/protected/config/global.php');
	
	require_once($yii);
	Yii::createWebApplication($config)->run();
}
?>
