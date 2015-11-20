<?php
//command cron

// change the following paths if necessary
$yii=dirname(__FILE__).'/framework/yii.php';

if(preg_match('@^/path/to/site@', dirname(__FILE__)))
{
	$config = dirname(__FILE__) . '/public_html/protected/config/production.php';
}
else // LOCAL
{
	$config = dirname(__FILE__) . '/public_html/protected/config/local.php';
}


require_once($yii);
 
// creating and running console application
Yii::createConsoleApplication($config)->run();

?>