<?php

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.

return array(
	'basePath' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..',
	'name' => 'Application Name',


	//'sourceLanguage' => 'en',
	'language' => 'en',


	// application-level parameters that can be accessed
	// using Yii::app()->params
	'params' => array(
		//'AllowDirtyQuery'=>false,
		'applicationAuthor' => 'Author Name',
		'onlyAllowDataUploadOnce'=>false,
		'applicationAuthorLink' => 'http://example.com',
		'site' => 'sitename',
		'campaignPrefix' => '',

		// send all email to this user instead of the submitted / calculated To address
		'overrideEmailRecipients' => true,
		'ipLoginFiltering' => false, // Enables login IP filtering and banning
		'ipBanMessage' => 'Your IP has been banned for repeated failed login attempts. Please contact the site administrator.',
		'ipWhiteList' => array(),
		'dateFormat' => 'j F Y',
		'encryptionAlgorithm' => 'mcrypt.enigma',
		'decryptionAlgorithm' => 'mdecrypt.enigma',

		// Set the domain to send emails from a example.com
		'insiderEmailDomain' => 'example.com',

		// these are overriden in environment config files for staging and production
		'fromEmail' => 'email@example.com',
		'adminEmail' => 'email@example.com',

		'mailgun' => array(
			'key' => '',
			'public-key' => '', // used for email validate
		),
	),


	// preloading 'log' component
	'preload' => array('log'),

	// autoloading model and component classes
	'import' => array(
		'application.models.*',
		'application.components.*',
		'application.helpers.*',
		'application.extensions.widgets.*',
		'application.extensions.logging.*',
		'application.extensions.mailgun.*',
		'application.vendors.*',
	),

	// application components
	'components' => array(

		// User class for Admin logins
		'user' => array(
			'allowAutoLogin' => true,
			'class' => 'AdminUser',
		),

		// User class for Accession contact logins
		'accessionUser' => array(
			'allowAutoLogin' => true,
			'class' => 'AccessionUser',
		),

		 'request'=>array(
			'enableCsrfValidation' => true,
			'enableCookieValidation' => true,
			'class'=>'HttpRequest',
			'noCsrfValidationRoutes' => array(
				'mailgun/bounce',
				'mailgun/open'
			),
			'csrfCookie'=>array(
				'httpOnly'=>true,
			),

		),

		'session' => array(
			'cookieParams' => array(
				'httponly' => true,
			),
		),

		// uncomment the following to enable URLs in path-format
		'urlManager' => array(
			'showScriptName' => false,
			'urlFormat' => 'path',
			'useStrictParsing' => true,
			'rules' => array(

				// Front end requests
				'' => 'site/homepage',
				'dashboard' => 'site/dashboard',
				'login' => 'site/login',
				'logout' => 'site/logout',
				'reset-password' => 'site/resetPassword',

				'privacy-policy' => 'site/privacy',

				// CONTACTS
				'download-contacts' => 'site/downloadContacts',

				// MAILGUN
				'mailgun' => 'mailgun/index',

				// ORGANISATIONS
				'organisations' => 'organisation/index',
				'organisations/create' => 'organisation/create',
				'organisations/<id:\d+>' => 'organisation/update',

				// VENUES
				'venues' => 'venue/index',
				'venues/create' => 'venue/create',
				'venues/<id:\d+>' => 'venue/update',

				// USERS
				'users' => 'user/index',
				'users/create' => 'user/create',
				'users/<id:\d+>' => 'user/update',
				'your-account' => 'user/updateBasic',


				// QUERIES
				'queries' => 'query/index',
				'queries/create' => 'query/create',
				'queries/<id:\d+>' => 'query/update',
				'queries/<id:\d+>/run' => 'query/run',
				'queries/<id:\d+>/download' => 'query/download',
				'queries/ajax' => 'query/ajax',

				'invites' => 'invite/index', // show previously sent invites
				'invites/create' => 'query/invite', // allow build invite query
				'invites/<campaign_id:\d+>' => 'invite/edit',
				'invites/<campaign_id:\d+>/send' => 'invite/send',
				'invites/<campaign_id:\d+>/intended-recipients' => 'invite/intendedRecipients',

				'invites/<campaign_id:\d+>/view' => 'invite/view',
				'invites/<campaign_id:\d+>/recipients' => 'invite/recipients',
				'invites/<campaign_id:\d+>/rules' => 'invite/rules',

				// CAMPAIGNS
				'campaigns' => 'campaign/index',
				'campaigns/<id:\d+>/export' => 'campaign/export',
				'campaigns/<id:\d+>/run' => 'campaign/run',
				'campaigns/<id:\d+>/<step:\d+>' => 'campaign/createUpdate',
				'campaigns/<id:\d+>' => 'campaign/createUpdate',
				'campaigns/create' => 'campaign/createUpdate',
				'campaigns/<id:\d+>/files/create' => 'campaign/fileUpload', // pdf attachment, not outcome update
				'campaigns/<id:\d+>/files/<file_id:\d+>-<secret:\w+>.<extension:\w{3,4}>' => 'campaign/fileDownload',
				'campaigns/<id:\d+>/results' => 'campaign/results',
				'campaigns/<id:\d+>/download' => 'campaign/download',
				'campaigns/<id:\d+>/upload' => 'campaign/upload', // update all manual outcomes
				'campaigns/<id:\d+>/upload-outcome' => 'campaign/uploadOutcome', // update single manual outcome

				// campaign group
				'campaigns/<campaign_id:\d+>/groups/<id:\d+>/update' => 'campaignGroup/update',
				'campaigns/<campaign_id:\d+>/groups/<id:\d+>/export' => 'campaignGroup/export',

				// campaign group email templates
				'campaigns/<campaign_id:\d+>/groups/<group_id:\d+>/template/create' => 'emailTemplate/create',
				'campaigns/<campaign_id:\d+>/groups/<group_id:\d+>/template/view/<template_id:\d+>' => 'emailTemplate/view',

				// campaign unsubscribe
				// ALSO USED IN commands via console so exists in config/console.php.
				'unsubscribe/campaign/<campaign_id:\d+>-<campaign_hash:\w{6}>-<campaign_contact_id:\d+>-<campaign_contact_hash:\w{6}>' => 'data/campaignUnsubscribe',

				// campaign OUTCOME (automatic)
				'l/<campaign_id:\d+>-<campaign_hash:\w{6}>-<campaign_contact2outcome_id:\d+>-<campaign_contact2outcome_hash:\w{6}>' => 'campaignContact2Outcome/recordAndForward',


				// DATA
				'data'	=> 'data/upload',
				'data-cleaning'	=> 'data/cleaningUpload',
				'data/structure-reminder' => 'data/structureReminder',
				'data-campaign-unsubscribes' => 'data/importCampaignUnsubcribes',
				'data-unsubscribes'	=> 'data/importUnsubcribes',
				'data-extra-emails'	=> 'data/importExtras',
				'data/create-test-data' => 'data/testData',
				'data/suppression-list' => 'data/suppressionList',

				'data/search-contacts' => 'data/searchContacts',

				'data/store-contact/<id:\d+>' => 'data/storeContact',

				'unsubscribe/test' => 'data/unsubscribeTest',

				// Invite unsubscribe
				'unsubscribe/<campaign_id:\d+>-<campaign_hash:[a-zA-Z0-9]{6}>/<invite_id:\d+>-<invite_hash_partial:[a-zA-Z0-9]{8}>' => 'data/inviteUnsubscribe',

				'unsubscribes' => 'data/unsubscribes',

				'accession/start' => 'accession/start',
				'accession/1/<accessionhash:[a-z0-9]{40}>' => 'accession/stepOne',
				'accession/1' => 'accession/stepOne',
				'accession/2/<accessionhash:[a-z0-9]{40}>' => 'accession/stepTwo',
				'accession/3/<accessionhash:[a-z0-9]{40}>' => 'accession/stepThree',
				'accession/4/<accessionhash:[a-z0-9]{40}>' => 'accession/stepFour',
				'accession/5/<accessionhash:[a-z0-9]{40}>' => 'accession/stepFive',
				'accession/6/<accessionhash:[a-z0-9]{40}>' => 'accession/stepSix',
				'accession/7/<accessionhash:[a-z0-9]{40}>' => 'accession/stepSeven',
				'accession/8/<accessionhash:[a-z0-9]{40}>' => 'accession/stepEight',

				'accession/complete/<accessionhash:[a-z0-9]{40}>' => 'accession/complete',

				'accession/invite/<invite_hash:[a-z0-9]{40}>' => 'accession/invite',

				'your-details' => 'accession/updateDetails',


				'mailgun-webhooks-(6ey2gt|nwsr4s)/bounce' => 'mailgun/bounce',
				'mailgun-webhooks-(6ey2gt|nwsr4s)/open' => 'mailgun/open',

				// Catch all for 404's
				'<url:(.*)>' => 'site/notFound',
			),
		),

		'errorHandler' => array(
			// use 'site/error' action to display errors
			'errorAction' => 'site/error',
		),
		'log' => array(
			'class' => 'CLogRouter',
			'routes' => array(
				// This saves a log below the public_html folder in /logs
				array(
					'class' => 'CFileLogRoute',
					'levels' => 'error, warning',
					'logPath' => __dir__ . '/../../../logs',
					'maxFileSize' => 256,
				),
			)
		),
		'cimage' => array(
			'class' => 'application.extensions.cimage.CImageComponent',
			// GD or ImageMagick
			'driver' => 'GD',
			// ImageMagick setup path
			'params' => array('directory'=>'/opt/local/bin'),
		),
		'functions' => array(
			'class' => 'application.extensions.functions.Functions',
		),
		'rss' => array(
			'class' => 'application.extensions.rss.Rss',
		 ),
	),
);