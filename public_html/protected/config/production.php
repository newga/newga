<?php

return CMap::mergeArray(
	require(dirname(__FILE__) . '/main.php'),
	array(
		'params' => array(
			'onlyAllowDataUploadOnce'=>true,
			'dateFormat' => 'j F Y',
			'site' => 'sitename',
			'campaignPrefix' => '',

		),
		'components' => array(

			'db'=>array(
				'connectionString' => 'mysql:host=127.0.0.1;dbname=database_name',
				'emulatePrepare' => true,
				'username' => '',
				'password' => '',
				'charset' => 'utf8',
				'enableProfiling' => false,
			),

			'log' => array(
				'class' => 'CLogRouter',
				'routes' => array(
					// This runs the custom logging class that emails errors
					array(
						'class' => 'CustomLogger',
					),
				),
			),
		),
	)
);

?>