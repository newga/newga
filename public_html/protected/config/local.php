<?php

return CMap::mergeArray(
	require(dirname(__FILE__) . '/main.php'),
	array(
		'name' => 'Application Name',
		'params' => array(
			'onlyAllowDataUploadOnce'=>true,
			'dateFormat' => 'j F Y',
			'site' => 'sitename',
			'campaignPrefix' => 'TEST',
		),
		'components' => array(
			'db'=>array(
				'connectionString' => 'mysql:host=127.0.0.1;dbname=database_name',
				'emulatePrepare' => true,
				'username' => 'root',
				'password' => '',
				'charset' => 'utf8',
				'enableProfiling' => true,
				'enableParamLogging' => true,
			),
			'log' => array(
				'class' => 'CLogRouter',
				'routes' => array(
					// This runs the custom logging class that emails errors
					/*
					array(
						'class' => 'CustomLogger',
					),
					*/
					// This shows the database profiling at the bottom of the page
					// We're checking for local dev environment
					array(
						'class' => 'CProfileLogRoute',
						'levels' => 'profile',
						'enabled' => true,
					),
				),
			),
		),
	)
);

?>