<?php

class SiteController extends Controller
{
	public $layout = '//layouts/admin';
	
	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
		);
	}

	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules()
	{
		return array(
			array('allow',
				'actions'=>array('dashboard'),
				'expression' => 'Yii::app()->user->role >= ' . User::ROLE_ORGANISATION,
			),

			array('allow',
				'actions' => array('downloadContacts'),
				'expression' => 'Yii::app()->user->role == ' . User::ROLE_ORGANISATION,
			),

			array('allow',
				'actions'=>array('homepage','error', 'notFound','logout','login','resetpassword','privacy'),
				'users'=>array('*'),
			),
			
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}
	
	public function actionPrivacy()
	{
		$this->layout = '//layouts/accession';
		$this->render('privacy');
	}
	
	public function actionHomepage()
	{
		$this->layout = '//layouts/accession';
		
		$this->pageTitle = Yii::app()->name;
		
		//render the correct homepage based on site name
		$this->render('homepage_' . (isset(Yii::app()->params['site']) ? Yii::app()->params['site']  : 'sitename'));
	}
	/**
	 * This is the default 'view' action that is invoked
	 * when an action is not explicitly requested by users.
	 */
	public function actionDashboard()
	{
		if (Yii::app()->user->isGuest) {
			$this->redirect(array('default/login'));
		}
		
		$this->pageTitle = 'Admin | ' . Yii::app()->name;
		
		// Enable dashboard query?

		if(ENVIRONMENT != 'PRODUCTION')
		{

			// all contacts.
			$Query = new Query;
			$result = $Query->runCampaignQuery(array(
				'contact_warehouse_id',
				'terms_agreed',
				'dob',
				'origin_organisation_id',
				'culture_segment',
			));

			$people = $result['rows'];
		}
		else
		{
			$result = [];
			$people = [];
		}


		// all contacts I have access to download
		if((int)Yii::app()->user->role === User::ROLE_ORGANISATION)
		{

			$SQL = "

SELECT COUNT(`store`.`id`) as total_contacts

FROM `store2contact`
	LEFT JOIN `store` ON `store`.`id` = `store2contact`.`store_id`
WHERE contact_warehouse_id IN (

	SELECT s2c2.contact_warehouse_id
	FROM store2contact s2c2
	WHERE s2c2.origin_id = " . Yii::app()->user->organisation_id . "

)
AND `store2contact`.`origin_id` = 10
";

			$totalContacts = Store::model()->countBySQL($SQL);

		}


		Yii::app()->clientScript->registerScriptFile('https://www.google.com/jsapi');


		$this->render('dashboard',array(
			'Query'=>$Query,
			'people'=>$people,
			//'string'=>$sql_string,
			'downloadResultSize' => $downloadResultSize,
			'totalContacts' => $totalContacts,
		));

	}
	

	public function actionDownloadContacts()
	{
		// contact had a row originating from my organisation

		// disabled all logging so it's not appended to csv
		foreach (Yii::app()->log->routes as $route) {
			if ($route instanceof CWebLogRoute) {
				$route->enabled = false;
			}
		}

		$SQL = "

SELECT -- `store`.`id`, store2contact.origin_id, store2contact.contact_warehouse_id

 --  `store`.`id`
   `store`.`origin_unique_id`,
   `store`.`salutation`,
   `store`.`first_name`,
   `store`.`last_name`,
   `store`.`address_line_1`,
   `store`.`address_line_2`,
   `store`.`address_line_3`,
   `store`.`address_town`,
   `store`.`address_postcode`,
   `store`.`address_county`,
   `store`.`email`,
   
   -- using address_line_4 to store our culture_segment for speeeeed
    `accession`.culture_segment as `address_line_4`

 --   `store`.`origin_organisation_id` AS `origin_organisation_id`,
 --  `store`.`contact_email` AS `contact_email`,
 --  `store`.`contact_sms` AS `contact_sms`,
 --  `store`.`contact_post` AS `contact_post`,
 --  `store`.`date_imported` AS `date_imported`

FROM `store2contact`
	LEFT JOIN `store` ON `store`.`id` = `store2contact`.`store_id`
	LEFT JOIN accession ON `store2contact`.id = accession.store2contact_id
WHERE contact_warehouse_id IN (

	SELECT s2c2.contact_warehouse_id
	FROM store2contact s2c2
	WHERE s2c2.origin_id = " . Yii::app()->user->organisation_id . "

)
AND `store2contact`.`origin_id` = 10
";

		$Contacts = Store::model()->findAllBySQL($SQL);

		header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
		header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

		header('Content-Encoding: UTF-8');
		header('Content-type: text/csv; charset=UTF-8');

		// disposition / encoding on response body
		header("Content-Disposition: attachment;filename=contacts-" . date("Y-m-d") . ".csv");
		header("Content-Transfer-Encoding: binary");

		// create a csv. Unencrypt where necissary;
		$headers = array(
			'Your ID', // CANNOT BE 'id' or 'ID' due to excel bug https://wordpress.org/support/topic/plugin-users-to-csv-bug-excel-error-because-first-column-is-called-id
			'title',
			'first name',
			'last name',
			'address 1',
			'address 2',
			'address 3',
			'town',
			'postcode',
			'email',
			'culture segment',
		);

		$csv = fopen('php://output', 'w');

		fputcsv($csv, $headers);

		foreach ($Contacts as $Contact)
		{
			$column = [];

			$column[] = $Contact->origin_unique_id;
			$column[] = $Contact->salutation;
			$column[] = $Contact->first_name;
			$column[] = $Contact->last_name;
			$column[] = $Contact->address_line_1;
			$column[] = $Contact->address_line_2;
			$column[] = $Contact->address_line_3;
			$column[] = $Contact->address_town;
			$column[] = $Contact->address_postcode;
			$column[] = $Contact->email;

			// culture segment - we use address 4 for speeeeed
			$column[] = $Contact->address_line_4;

			fputcsv($csv, $column);
		}

		fclose($csv);

		//echo $csv;
		exit;

	}
	
	
	public function actionNotFound()
	{
		throw new CHttpException(404, 'Page not found');
	}
	
	public function actionError()
	{
		if($error=Yii::app()->errorHandler->error)
		{
			if(Yii::app()->request->isAjaxRequest)
			{
				echo $error['message'];
			}
			else
			{
				// Accession view or admin view?
				if(preg_match('@^/accession/@', Yii::app()->request->requestUri))
				{
					$this->layout = 'accession';
					$this->render('errorAccession', $error);
				}
				else
				{
					$this->render('error', $error);
				}
			}
		}
	}

	


	/**
	 * Allows password reset
	 */
	public function actionResetPassword() {
		
		$this->layout = '//layouts/accession';
		$this->pageTitle = 'Reset Password | ' . Yii::app()->name;

		if(!Yii::app()->user->isGuest) {
			// can't be here
			$this->redirect(array('site/dashboard'));
		}
		
		$PasswordResetForm = new PasswordResetForm;
		$User = new User();
		
		$User->scenario = 'resetPassword';

		if($_GET['hash']) {

			$User = User::model()->findByAttributes(array(
				'reset_hash' => $_GET['hash'],
			));

			if(!is_null($User))
			{
				if($_POST['PasswordResetForm'])
				{
					$PasswordResetForm->attributes = $_POST['PasswordResetForm'];
					
					if($PasswordResetForm->validate())
					{
						// submitting updated password
						$User->password1 = $_POST['PasswordResetForm']['password'];
						$User->password2 = $_POST['PasswordResetForm']['password_repeat'];
						$User->reset_hash = '';
						$User->verified = 1;

						if($User->save(true, array('password', 'reset_hash', 'verified')))
						{
							Yii::app()->user->setFlash('success', 'We\'ve saved your new password. Please log in below');
							$this->redirect(array('site/login'));
						}
					}
				}

				$User->password2 = '';
				$User->password1 = '';
				
				

			}
			else
			{
				// Check for a contact user
				$Store = $this->getContactStoreByHash($_GET['hash']);
				
				$Accession = $Store->store2contact->accession;
				
				if(!is_null($Store))
				{
					$PasswordResetForm = new PasswordResetForm;
					
					if($_POST['PasswordResetForm'])
					{
						$PasswordResetForm->attributes = $_POST['PasswordResetForm'];
						
						if($PasswordResetForm->validate())
						{
							$Accession->password = hash('sha256', $_POST['PasswordResetForm']['password'] . SHASALT);
							$Accession->reset_hash = '';
							$Accession->save(true, array('password','reset_hash'));
							
							Yii::app()->user->setFlash('success', 'We\'ve saved your new password. Please log in below');
							$this->redirect(array('site/login'));
						}
					}
				}
				else
				{
					$User = new User;
					$User->addError('email', 'That hash is expired or has been used. Please generate a new one below.');
					unset($_GET['hash']);
				}
			}

		}
		elseif($_POST['PasswordResetForm']['email'])
		{
			if(!strlen(trim($_POST['PasswordResetForm']['email'])))
			{
				$User->addError('email', 'A valid email address is required.');
			}
			else
			{
				// trying to reset an email address

				// Check admin users first
				$User = User::model()->findByAttributes(array(
					'email' => $_POST['PasswordResetForm']['email'],
				));

				if(!is_null($User))
				{
					// Admin user found. Send email
					$User->sendPasswordResetEmail();
				}
				else
				{
					$Store = new Store;

					// Check for a contact user
					$Store = $this->getContactStore($Store->encryptEmail($_POST['PasswordResetForm']['email']));
					
					if(!is_null($Store))
					{
						$Store->sendPasswordResetEmail();
					}
					else
					{
						$User = new User;
					}
				}
			}
		}


		$this->render('resetPassword', array(
			'User' => $User,
			'PasswordResetForm' => $PasswordResetForm,
		));

	}
	
	public function getContactStore($email)
	{
		$Criteria = new CDbCriteria;
		$Criteria->condition = "
			email = :email AND 
			origin_organisation_id = :origin_organisation_id AND 
			password IS NOT NULL 
		";
		$Criteria->params = array(
			':email' => $email,
			':origin_organisation_id' => 10
		);

		return Store::model()->with('store2contact','store2contact.accession')->find($Criteria);
	}
	
	public function getContactStoreByHash($hash)
	{
		$Criteria = new CDbCriteria;
		$Criteria->condition = "
			reset_hash = :hash AND 
			origin_organisation_id = :origin_organisation_id AND 
			password IS NOT NULL
		";
		$Criteria->params = array(
			':hash' => $hash,
			':origin_organisation_id' => 10
		);

		return Store::model()->with('store2contact','store2contact.accession')->find($Criteria);
	}
	
	/**
	 * Displays the login page
	 */
	public function actionLogin()
	{
		$this->pageTitle = 'Login | ' . Yii::app()->name;
		
		$this->layout = '//layouts/accession';
		
		$LoginForm = new LoginForm;

		// if it is ajax validation request
		if(isset($_POST['ajax']) && $_POST['ajax'] === 'login-form')
		{
			echo CActiveForm::validate($LoginForm);
			Yii::app()->end();
		}

		// collect user input data
		if(isset($_POST['LoginForm']))
		{
			if(Login::model()->IPBanned())
			{
				// this IP is banned
				$LoginForm->addError('email', Yii::app()->params['ipBanMessage']);
			}
			else
			{
				$LoginForm->attributes = $_POST['LoginForm'];
				
				// validate user input and redirect to the previous page if valid
				if($LoginForm->validate() && $LoginForm->login())
				{
					$User = User::model()->getUser();
					
					if(!is_null($User))
					{
						// Admin user
						$User->reset_hash = null;
						$User->save();
						
						$login = new Login;
						$login->success = 1;
						$login->user_id = $User->id;
						$login->date = date('Y-m-d H:i:s');
						$login->ip = $_SERVER['REMOTE_ADDR'];
						$login->save();
						
						$this->redirect(array('site/dashboard'));
					}
					else
					{
						// Accession user
						// Go straight to their details page
						$this->redirect(array('accession/updateDetails'));
					}
					
					
					
					
					
				}
				else
				{
					// Failed login
					$login = new Login;
					$login->success = 0;
					$login->date = date('Y-m-d H:i:s');
					$login->ip = $_SERVER['REMOTE_ADDR'];
					
					// See if we can find the user
					$User = User::model()->findByAttributes(array('email' => $_POST['LoginForm']['email']));
					
					if($User)
					{
						$login->user_id = $User->id;
					}
					
					$login->save();
					
					// Check how many failed logins we have in last hour
					// If 5, we ban the IP
					if(!in_array($_SERVER['REMOTE_ADDR'], Yii::app()->params['ipWhiteList']))
					{
						$criteria = new CDbCriteria();
						$criteria->condition = "date > :date AND success = 0 AND ip = :ip";
						$criteria->params = array(':ip' => $_SERVER['REMOTE_ADDR'], ':date' => date('Y-m-d H:i:s', strtotime('1 hour ago')));
						
						$logins = Login::model()->findAll($criteria);
						
						

						if(sizeof($logins) >= 5 && !in_array($_SERVER['REMOTE_ADDR'], Yii::app()->params['ipWhiteList']))
						{
							// Ban the ip
							$ipBan = new IpBan;
							$ipBan->ip = $_SERVER['REMOTE_ADDR'];
							$ipBan->save();
							
							$LoginForm->clearErrors();
							$LoginForm->addError('email','Your IP has been banned for repeated failed login attempts. Please contact the site administrator.');
						}
						elseif(sizeof($logins) == 4)
						{
							// Show warning
							$LoginForm->addError('password','You only have 1 login attempt remaining in this hour period. Another failed attempt within an hour and your IP will be banned.');
						}
					}
				}
			}
		}
		// display the login form
		$this->render('login',array('LoginForm'=>$LoginForm));
	}

	/**
	 * Logs out the current user and redirect to homepage.
	 */
	public function actionLogout()
	{
		Yii::app()->user->logout();
		
		$this->redirect(array('site/login'));
	}
}

?>