<?php

// This is the configuration for yiic console application.
// Any writable CConsoleApplication properties can be configured here.

if(preg_match('@^/path/for/production@', dirname(__FILE__)))
{
	define('ENVIRONMENT', 'PRODUCTION');
	
	$db = array(
		'connectionString' => 'mysql:host=127.0.0.1;dbname=database_name',
		'emulatePrepare' => true,
		'username' => '',
		'password' => '',
		'charset' => 'utf8',
	);
	
	$params = array(
		'adminEmail' => 'email@example.com',
		'insiderEmailDomain' => 'example.com',
		'fromEmail' => 'email@example.com',
		'db' => $db,
		'campaignPrefix' => '',
	);

}
else // LOCAL
{
	define('ENVIRONMENT', 'LOCAL');

	$db = array(
		'connectionString' => 'mysql:host=127.0.0.1;dbname=database_name',
		'emulatePrepare' => true,
		'username' => 'root',
		'password' => '',
		'charset' => 'utf8',
	);

	$params = array(
		'adminEmail' => 'email@example.com',
		'insiderEmailDomain' => 'example.com',
		'fromEmail' => 'email@example.com',
		'db' => $db,
		'campaignPrefix' => 'TEST',
	);
}

$params['mailgun'] = array(
	'key' => '',
);

return array(

	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'Application Name',
	
	'params' => $params,
	// application components
	'components'=>array(

		'db' => $db,

		// start unsubscribe url creation command stuff
		 'request'=>array(
			'baseUrl' => '',
		 ),

		'urlManager' => array(
			'showScriptName' => false,
			'urlFormat' => 'path',
			'useStrictParsing' => true,
			'rules' => array(

				// campaign unsubscribe
				'unsubscribe/campaign/<campaign_id:\d+>-<campaign_hash:\w{6}>-<campaign_contact_id:\d+>-<campaign_contact_hash:\w{6}>' => 'data/campaignUnsubscribe',
			)
		),
		// end unsubscribe url creation command stuff

	),
	'import' => array(
		'application.models.*',
		'application.components.*',
		'application.helpers.*',
		'application.extensions.widgets.*',
		'application.extensions.logging.*',
		'application.extensions.mailgun.*',
		'application.vendors.*',
		'application.extensions.mailgun.*',
	),
    'commandMap'=>array(
        'migrate'=>array(
            'class'=>'system.cli.commands.MigrateCommand',
            'migrationPath'=>'application.migrations',
            'migrationTable'=>'yii_migration',
            'connectionID'=>'db',
            //'templateFile'=>'application.migrations.template',
        ),
    ),

);