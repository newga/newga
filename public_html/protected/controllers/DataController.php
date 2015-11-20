<?php

class DataController extends Controller
{
	
	public $layout = '/layouts/admin';
	
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
				'actions'=>array('testData','cleaningUpload', 'importCampaignUnsubcribes', 'importUnsubcribes', 'suppressionList', 'importExtras', 'storeContact', 'searchContacts'),
				'expression' => 'Yii::app()->user->role >= ' . User::ROLE_SUPERADMIN,
			),
			// anyone can unsubscribe
			array('allow',
				'actions' => array('InviteUnsubscribe', 'CampaignUnsubscribe'),
				'users' => array('*')
			),

			array('allow',
				'actions'=>array('unsubscribes'),
				'expression' => '(Yii::app()->user->role == ' . User::ROLE_MANAGER . ' || Yii::app()->user->role >= ' . User::ROLE_ORGANISATION . ')',
			),

			array('allow',
				'actions'=>array('upload'),
				'expression' => 'Yii::app()->user->role >= ' . User::ROLE_ORGANISATION,
			),

			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}
	
	public function actionSearchContacts()
	{
		//phpinfo();exit();
		$Store = new Store;
		
		if(isset($_POST['decrypt']))
		{
			$decryptedEmail = $Store::model()->decryptEmail($_POST['decrypt']);
		}
		
		if(isset($_POST['encrypt']))
		{
			$encryptedEmail = $Store::model()->encryptEmail($_POST['encrypt']);
		}
		
		$this->render('searchContacts', array(
			'decryptedEmail' => $decryptedEmail,
			'encryptedEmail' => $encryptedEmail,
		));
	}
	
	public function actionStoreContact()
	{
		$this->breadcrumbs=array(
			'Store'
		);
		
		$Store = Store::model()->findByPk($_GET['id']);
		
		$this->render('storeContact', array(
			'Store' => $Store,
		));
	}
	
	public function actionImportExtras()
	{
		
		$Store = new Store();

		$this->pageTitle = 'Import Extra Emails | ' . Yii::app()->name;
		
		$this->breadcrumbs=array(
			'Import Extra Emails'
		);
		
		ini_set('auto_detect_line_endings', true);
		
		// For encryption
		$StoreModel = new Store;
		
		// Count vars
		$doesNotExist = 0;
		$alreadyHasAnEmail = 0;
		$toBeSuppressed = 0;
		$toSetEmailAddress = 0;
		$noURN = 0;
		$total = 0;
		$emails = array();
		$suppressed = 0;
		$updated = 0;
		$toBeResubscribed = 0;
		
		// org map
		$organisations = array(
			12345 => 1
		);
		
		if(isset($_POST['import']))
		{
			if(!strlen($_FILES['csv']['name']))
			{
				Yii::app()->user->setFlash('error', 'Choose a file');
			}
			else
			{
				if (($handle = fopen($_FILES['csv']['tmp_name'], "r")) !== FALSE)
				{
					while (($data = fgetcsv($handle, 1000, ",")) !== FALSE)
					{
						// Find contact by origin_unique_id
						if(strlen(trim($data[0])))
						{
							$Store = Store::model()->with('store2contact')->find(array(
								'condition' => 'origin_organisation_id = :origin_organisation_id AND t.origin_unique_id = :origin_unique_id AND date_expired IS NULL',
								'params' => array(
									':origin_unique_id' => $data[0],
									':origin_organisation_id' => $organisations[$data[1]],
								),
							));
							
							$email = mb_convert_encoding(trim(strtolower($data[2])), 'UTF-8');
							
							if(is_null($Store))
							{
								$doesNotExist++;
							}
							else
							{
								// Person exists
								
								// Should we suppress them?
								if(!strlen($email))
								{
									
									if(!is_null($Store->store2contact))
									{
										
										
										$toBeSuppressed++;
										
										//Check if they are not already in suppression list
										$SuppressionListCollection = SuppressionList::model()->findAll(array(
											'condition' => 'store2contact_id = :store2contact_id',
											'params' => array(
												':store2contact_id' => $Store->store2contact->id,
											),
										));


										if(!sizeof(($SuppressionListCollection))) {

											$SuppressionList = new SuppressionList;
											
											//$SuppressionList->warehouse_id = $Store->store2contact->contact_warehouse_id;
											$SuppressionList->store_id = $Store->store2contact->store_id;
											$SuppressionList->store2contact_id = $Store->store2contact->id;
											$SuppressionList->date = date('Y-m-d H:i:s');
											$SuppressionList->type = SuppressionList::TYPE_UNSUBSCRIBE;
											
											//if(!$SuppressionList->validate())
											if(!$SuppressionList->save())
											{
												print_r($SuppressionList->errors);
												exit;
											}
											else
											{
												$suppressed++;

											}
										}



									}

									//update email pref
									$Store->contact_email = 0;
									$Store->save(true, array("contact_email"));
								}
								else
								{

									$toSetEmailAddress++;
									
									$Store->email = $email;
									$Store->contact_email = 1;
								
									if(!$Store->save(true, array('email','contact_email')))
									{
										print 'could not save store row<br />' ."\n";
									}
									else
									{
										$updated++;

										//we have updated, also remove from supression list (if in supression list)
										$SuppressionListCollection = SuppressionList::model()->findAll(array(
											'condition' => 'store2contact_id = :store2contact_id',
											'params' => array(
												':store2contact_id' => $Store->store2contact->id,
											),
										));

										if(count($SuppressionListCollection) > 0)
										{
											$toBeResubscribed++;	
										}

										foreach($SuppressionListCollection as $SuppressionListItem) 
										{
											$SuppressionListItem->delete();
										
										}
										
										
										

									}
									
								}
							}
						}
						else
						{
							$noURN++;
						}
						
						$total++;
					}
				}
			}
		}
		
		$this->render('importExtras', array(
			'doesNotExist' => $doesNotExist,
			'toBeSuppressed' => $toBeSuppressed,
			'toSetEmailAddress' => $toSetEmailAddress,
			'total' => $total,
			'noURN' => $noURN,
			'emails' => $emails,
			'suppressed' => $suppressed,
			'updated' => $updated,
			'toBeResubscribed' => $toBeResubscribed,
		));
	}
	
	public function actionSuppressionList()
	{
		$this->pageTitle = ' Suppression List | ' . Yii::app()->name;
		
		$this->breadcrumbs=array(
			'Suppression List'
		);
		
		$SuppressionList = new SuppressionList('search');
		
		$SuppressionList->unsetAttributes();

		if($_GET['SuppressionList']) {
			$SuppressionList->attributes = $_GET['SuppressionList'];
		}
		
		$this->render('suppressionList', array(
			'SuppressionList' => $SuppressionList,
		));
	}


	public function actionImportCampaignUnsubcribes()
	{
		$this->pageTitle = ' Import campaign unsubscribes | ' . Yii::app()->name;
		
		$this->breadcrumbs=array(
			'Import campaign unsubscribes'
		);
		
		ini_set('auto_detect_line_endings', true);
		
		if(isset($_POST['import']))
		{
			//print_r($_FILES);
			//exit();
			
			if(!strlen($_FILES['csv']['name']))
			{
				Yii::app()->user->setFlash('error', 'Choose a file');
			}
			else
			{
				$dupeCount = 0;
				$suppressionCount = 0;
				$emailsChecked = array();
				$emailDupeCount = 0;
				$totalCount = 0;
				$noRecordCount = 0;
				
				if (($handle = fopen($_FILES['csv']['tmp_name'], "r")) !== FALSE)
				{
					while (($data = fgetcsv($handle, 1000, ",")) !== FALSE)
					{
						if(sizeof($data) > 1)
						{
							Yii::app()->user->setFlash('error', 'File should only contain 1 column');
							break;
						}
						else
						{
							$totalCount++;
							$Store = new Store;

							$email = strtolower(trim(mb_convert_encoding($data[0], 'UTF-8')));

							if (in_array($email, $emailsChecked)) {
								$emailDupeCount++;
								continue;
							}

							//add email to emails checked
							$emailsChecked[] = $email;

							// Check for matching email in store table
							$StoreRows = Store::model()->with('store2contact')->findAll(array(
								'condition' => 'origin_organisation_id = :org_id AND email = :email',
								'params' => array(
									':email' => $Store->encryptEmail($email),
									':org_id' => 10, // this is the application organisation id.
								),
							));

							if (sizeof($StoreRows))
							{
								foreach($StoreRows as $Store)
								{
									if (is_null($Store->store2contact))
									{
										continue;
									}

									// Check for existing based on warehouse id
									$Suppression = SuppressionList::model()->find(array(
										'condition' => 'warehouse_id = :warehouse_id',
										'params' => array(
											':warehouse_id' => $Store->store2contact->contact_warehouse_id,
										),
									));

									if(is_null($Suppression))
									{
										$Suppression = new SuppressionList;
										$Suppression->type = SuppressionList::TYPE_UNSUBSCRIBE;

										// always save the store id against this row
										$Suppression->store_id = $Store->id;
										$Suppression->store2contact_id = $Store->store2contact->id;
										$Suppression->warehouse_id = $Store->store2contact->contact_warehouse_id;
										$Suppression->date = date('Y-m-d H:i:s');

										if($Suppression->save())
										{
											$suppressionCount++;
										}

										$Store->contact_email = 0;
										$Store->save(true, array("contact_email"));
									}
									else
									{
										$dupeCount++;
									}
								}
							}
							else
							{
								$noRecordCount ++;
							}
						}
					}

					Yii::app()->user->setFlash('success', $suppressionCount . ' suppression rows saved. ' . $dupeCount . ' were already suppressed. Number of duplicate emails was ' . $emailDupeCount . '. Total number of rows processed was ' . $totalCount . '. We could not find a match for ' . $noRecordCount . '.');
					$this->refresh();
				}
				
				fclose($handle);
			}
		}

		$this->render('importCampaignUnsubcribes');
	}


	public function actionImportUnsubcribes()
	{
		$this->pageTitle = ' Import unsubscribes | ' . Yii::app()->name;
		
		$this->breadcrumbs=array(
			'Import unsubscribes'
		);
		
		ini_set('auto_detect_line_endings', true);
		
		if(isset($_POST['import']))
		{
			if(!strlen($_POST['organisation_id']))
			{
				Yii::app()->user->setFlash('error', 'Choose an organisation');
			}
			else
			{
				//print_r($_FILES);
				//exit();
				
				if(!strlen($_FILES['csv']['name']))
				{
					Yii::app()->user->setFlash('error', 'Choose a file');
				}
				else
				{
					$dupeCount = 0;
					$suppressionCount = 0;
					$emailsChecked = array();
					$emailDupeCount = 0;
					$totalCount = 0;
					$noRecordCount = 0;
					
					if (($handle = fopen($_FILES['csv']['tmp_name'], "r")) !== FALSE)
					{
						while (($data = fgetcsv($handle, 1000, ",")) !== FALSE)
						{
							if(sizeof($data) > 1)
							{
								Yii::app()->user->setFlash('error', 'File should only contain 1 column');
								break;
							}
							else
							{
								
								$totalCount++;

								$Store = new Store;


								$email = strtolower(trim(mb_convert_encoding($data[0], 'UTF-8')));

								if (in_array($email, $emailsChecked)) {
									$emailDupeCount++;
									continue;
								}

								//add email to emails checked
								$emailsChecked[]=$email;
								
								//print $Store->encryptEmail(trim(mb_convert_encoding($data[0], 'UTF-8')));exit();
								
								// Check for matching email in store table
								$StoreRows = Store::model()->with('store2contact')->findAll(array(
									'condition' => 'origin_organisation_id = :org_id AND email = :email',
									'params' => array(
										':email' => $Store->encryptEmail($email),
										':org_id' => (int)$_POST['organisation_id'],
									),
								));

								if (sizeof($StoreRows)) {


									foreach($StoreRows as $Store)
									{

										if (is_null($Store->store2contact)) {
											continue;
										}
								
										
										// Check for existing based on store2contact id
										$Suppression = SuppressionList::model()->find(array(
											'condition' => 'store2contact_id = :store2contact_id',
											'params' => array(
												':store2contact_id' => $Store->store2contact->id,
											),
										));

										if(is_null($Suppression))
										{

											$Suppression = new SuppressionList;
											$Suppression->type = SuppressionList::TYPE_UNSUBSCRIBE;
												
											// always save the store id against this row
											$Suppression->store_id = $Store->id;
											$Suppression->store2contact_id = $Store->store2contact->id;
											

											//We DO NOT need the warehouse id, we're not supressing from everything.
											//$Suppression->warehouse_id = $Store->store2contact->contact_warehouse_id;
											$Suppression->date = date('Y-m-d H:i:s');
											

											if($Suppression->save())
											{
												$suppressionCount++;
											}

											$Store->contact_email = 0;
											$Store->save(true, array("contact_email"));


											
										}
										else
										{

											$dupeCount++;
										}
									}
								}
								else {
									$noRecordCount ++;
								}
							}
						}
						
						Yii::app()->user->setFlash('success', $suppressionCount . ' suppression rows saved. ' . $dupeCount . ' were already suppressed. Number of duplicate emails was ' . $emailDupeCount . '. Total number of rows processed was ' . $totalCount . '. We could not find a match for ' . $noRecordCount . '.');
						$this->refresh();
					}
					
					fclose($handle);
				}
			}
		}
		
		$this->render('importUnsubcribes', array(
		));
	}
	
	public function actionTestData()
	{
		if(isset($_POST['do_test_data']))
		{
			$DataCreator = new DataCreator;
			
			$DataCreator->createTestData();
			
			$this->refresh();
		}
	
		$this->render('test-data');
	}
	


	// download unsubscribes
	public function actionUnsubscribes(){


		if(Yii::app()->user->organisation_id < 1){
			// not from a specific organisation.
			throw new CHttpException('404', 'Page Not Found');
		}

		$Organisation = Organisation::model()->findByPk(Yii::app()->user->organisation_id);

		if(isset($_POST['download'])){

			// offer the unsubscribes for their organisation
			$Store = new Store;

			$Unsubscribers = Store::model()->findAllBySql("

				SELECT DISTINCT s.email

				FROM suppression_list sl, store s

				WHERE sl.store_id IS NOT NULL
					AND s.id = sl.store_id
					AND s.origin_organisation_id = " . (int)$Organisation->id . "
					AND s.email IS NOT NULL
					-- INVITES DO HAVE CAMPAIGN_IDs IN THE SUPPRESSION TABLE >> AND sl.campaign_id IS NULL
					AND sl.type = 1
					-- AND sl.`date` > '2015-02-13 16:34:00'

				GROUP BY s.email
				ORDER BY `date` DESC

			");

			// get our columns headings
			if(sizeof($Unsubscribers)){

				$output = fopen("php://output",'w');

				header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
				header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
				header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

				header('Content-Encoding: UTF-8');
				header('Content-type: text/csv; charset=UTF-8');

				// disposition / encoding on response body
				header("Content-Disposition:attachment;filename=unsubscribes.csv"); 
				header("Content-Transfer-Encoding: binary");

				// build a csv
				$csv = array();
				$columnNames = array();

				foreach($Unsubscribers as $key => $Unsubscriber){

					fputcsv($output, [$Unsubscriber['email']]);

				}

				// disabled all logging so it's not appended to csv
				foreach (Yii::app()->log->routes as $route) {
					if ($route instanceof CWebLogRoute) {
						$route->enabled = false;
					}
				}


				fclose($output);
				exit;

			}

			Yii::app()->end();
		}



		// get a count of recent unsubscribes
		$recentUnsubscribeResults = Yii::app()->db->createCommand("

			SELECT COUNT(DISTINCT s.email) as total

			FROM suppression_list sl, store s, organisation o

			WHERE sl.store_id IS NOT NULL
				AND s.id = sl.store_id
				AND o.id = s.origin_organisation_id
				AND s.origin_organisation_id = " . (int)$Organisation->id . "

				AND date > '" . date("Y-m-d H:i:s", time() - (7*24*60*60)) . "'
				AND s.email IS NOT NULL
				-- INVITES DO HAVE CAMPAIGN_IDs IN THE SUPPRESSION TABLE >> AND sl.campaign_id IS NULL
				AND sl.type = 1

			GROUP BY s.origin_organisation_id

		")->queryAll();

		// get a count of all unsubscribes
		$totalUnsubscribeResults = Yii::app()->db->createCommand("

			SELECT COUNT(DISTINCT s.email) as total

			FROM suppression_list sl, store s, organisation o

			WHERE sl.store_id IS NOT NULL
				AND s.id = sl.store_id
				AND s.origin_organisation_id = " . (int)$Organisation->id . "
				AND o.id = s.origin_organisation_id
				AND s.email IS NOT NULL
				-- INVITES DO HAVE CAMPAIGN_IDs IN THE SUPPRESSION TABLE >> AND sl.campaign_id IS NULL
				AND sl.type = 1
				-- AND sl.`date` > '2015-02-13 16:34:00'

			GROUP BY s.origin_organisation_id

		")->queryAll();


		$this->pageTitle = 'unsubscribes | ' . Yii::app()->name;

		$this->breadcrumbs = array(
			'unsubscribes'
		);

		$this->render('unsubscribes', array(
			'recent' => $recentUnsubscribeResults[0]['total'],
			'total' => $totalUnsubscribeResults[0]['total'],
		));

	}

	// unsubscribe via an invite - so from a single organisation
	public function actionInviteUnsubscribe(){

		// campaign_id -> used to send the invite
		// campaign_hash -> used to check the id isn't manipulated
		// invite_id -> use to store the invite row
		// invite_hash_partial -> used to store the first 8 characters of the much longer 40 char invite hash

		// match the invite to a contact?
		$Invite = Invite::model()->find(array(
			'condition' => 'id = :invite_id AND SUBSTR(hash,1,8) = :invite_hash_partial',
			'params' => array(
				':invite_id' => $_GET['invite_id'],
				':invite_hash_partial' => $_GET['invite_hash_partial'],
			)
		));

		// matches an invite
		if(
			is_null($Invite) ||

				// check the campaign vars match
			(
				$Invite->campaign->id !== $_GET['campaign_id'] ||
				$Invite->campaign->hash !== $_GET['campaign_hash']
			)
		){
			$this->layout = 'vanilla';
			$this->pageTitle = '404 not found';
			$this->render('unsubscribed', array(
				'title' => 'We couldn\'t find that.',
				'message' => 'We weren\'t able to find the page you were looking for. Sorry about that.',
			));
			Yii::app()->end();
		}


		$Store2Contact = Store2Contact::model()->with(array(
			'store' => array(
				'with' => array('organisation')
			)
		))->findByPk($Invite->store2contact_id);

		if(is_null($Store2Contact)){

			$this->layout = 'vanilla';
			$this->pageTitle = '404 not found';
			$this->render('unsubscribed', array(
				'title' => 'We couldn\'t find that.',
				'message' => 'We weren\'t able to find the page you were looking for. Sorry about that.',
			));
			Yii::app()->end();
		}

		// check for existing suppression row. Should only be one row per campaign
		$SuppressionList = SuppressionList::model()->findByAttributes(array(
			'store2contact_id' => $Store2Contact->id,
			'campaign_id' => $Invite->campaign_id,
		));

		// add a suppression row
		if(is_null($SuppressionList)){
			$NewSuppressionList = new SuppressionList;
			
			// Warehouse ID is null because we only want to supress from this single org...
			$NewSuppressionList->warehouse_id = null;
			
			// ... which represented by a single Store row
			$NewSuppressionList->store_id = $Store2Contact->store_id;
			$NewSuppressionList->store2contact_id = $Store2Contact->id;
			$NewSuppressionList->campaign_id = $Invite->campaign_id;
			$NewSuppressionList->type = SuppressionList::TYPE_UNSUBSCRIBE;
			
			if(!$NewSuppressionList->save()){
				var_dump($NewSuppressionList->errors); exit;
			}
		}

		// show the success message page
		$this->layout = 'vanilla';
		$this->pageTitle = 'You have been unsubscribed';
		$this->render('unsubscribed', array(
			'title' => 'You\'ve unsubscribed successfully.',
			'message' => 'We\'re registered your unsubscribe request. You may still receive already queued email correspondance from ' . $Store2Contact->store->organisation->title . '.',
		));
		Yii::app()->end();

	}




	// unsubscribe via an campaign - so from the warehouse
	public function actionCampaignUnsubscribe()
	{

		// find them
		$CampaignContact = CampaignContact::model()->find(array(
			'with' => array(
				'campaign'
			),
			'condition' => "
				`t`.`id` = :campaign_contact_id
				AND `t`.hash = :campaign_contact_hash
				AND `campaign`.id = :campaign_id
				AND `campaign`.hash = :campaign_hash
			",
			'params' => array(
				'campaign_contact_id' => $_GET['campaign_contact_id'],
				'campaign_contact_hash' => $_GET['campaign_contact_hash'],
				':campaign_id' => $_GET['campaign_id'],
				':campaign_hash' => $_GET['campaign_hash'],
			)
		));


		if(is_null($CampaignContact))
		{
			$this->layout = 'vanilla';
			$this->pageTitle = '404 not found';
			$this->render('unsubscribed', array(
				'title' => 'We couldn\'t find that.',
				'message' => 'We weren\'t able to find the page you were looking for. Sorry about that.',
			));
			Yii::app()->end();
		}

		// they exist so unsubscribe them
		$ExistingSuppression = SuppressionList::model()->findByAttributes(array(
			'warehouse_id' => $CampaignContact->warehouse_id,
		));

		if(is_null($ExistingSuppression))
		{
			$SuppressionList = new SuppressionList;
			$SuppressionList->setAttributes(array(
				'warehouse_id' => $CampaignContact->warehouse_id,
				'campaign_id' => $CampaignContact->campaign->id,
				'date' => date('Y-m-d H:i:s'),
				'type' => 1,
			));
			$SuppressionList->save();
		}

		// show them a nice success message
		$this->layout = 'vanilla';
		$this->pageTitle = 'You have been unsubscribed';
		$this->render('unsubscribed', array(
			'title' => 'You\'ve unsubscribed successfully.',
			'message' => 'We\'re registered your unsubscribe request. You may still receive already queued email correspondance.',
		));
		Yii::app()->end();
	}






	/**
	* download a reminder of your organisations data structure file requirements
	* an upload template if you will
	*/
	public function actionStructureReminder(){

		if(Yii::app()->user->organisation_id){

			$Organisation = Organisation::model()->findByPk(Yii::app()->user->organisation_id);
			$View = $Organisation->view_name;
			$dbColumns = Yii::app()->db->schema->getTable($View::tableName())->columns;

			$csv = fopen('php://output', 'w');
			
			header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
			header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
			header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

			header('Content-Encoding: UTF-8');
			header('Content-type: text/csv; charset=UTF-8');

			// disposition / encoding on response body
			header("Content-Disposition: attachment;filename=insider_data_format.csv");
			header("Content-Transfer-Encoding: binary");

			foreach($dbColumns as $dbColumn){

				if($dbColumn->name === 'id') continue;

				$columns[] = $dbColumn->name;

			}

			// add row
			fputcsv($csv, $columns);
			fclose($csv);

			exit;

		}

	}




	/**
	 * This is the default 'view' action that is invoked
	 * when an action is not explicitly requested by users.
	 */
	public function actionUpload()
	{
		

		throw new CHttpException(404, "CSV Upload is currently disabled");

		$CsvFile = new CsvFile;
		$dataLength = null;
	   	$Results =	null;
	   	$Uploads = null;

		if(isset($_POST['CsvFile']))
		{
			$CsvFile->organisation_id = $_POST['CsvFile']['organisation_id'];
			$CsvFile->data = CUploadedFile::getInstance($CsvFile,'data');

			$subPath = 'original/to-process';
			$folderPath = Yii::app()->basePath . '/../../protected-file-uploads/' . $subPath . '/';

			if(!is_writable($folderPath)){
				throw new CHttpException(500, 'Folder "' . $folderPath . '" needs to be made writable (0755).');
			}

			if($CsvFile->save())
			{
				$filename = $folderPath . $CsvFile->uuid.".csv";

				$CsvFile->data->saveAs($filename);

				Yii::app()->user->setFlash('success','Thanks, your CSV has been successfully uploaded and we will process it shortly.');
			}
		}
		$Upload = null;
		$Organisation = null;

		if (isset(Yii::app()->user->getUser()->organisation->id)) {

	   		//if there is an organisation, get previous uploads
			 $Uploads = CsvFile::model()->findAll(array(
				'condition'=>'organisation_id = :organisation_id',
				'params' => array(':organisation_id' => Yii::app()->user->getUser()->organisation->id),
			));

			 $Organisation = Yii::app()->user->getUser()->organisation;

		}
		


		$this->pageTitle = 'Data | ' . Yii::app()->name;

		$this->breadcrumbs = array(
			'Data'
		);

		$this->render('upload', array(
			'Success'=>$Saved,
			'CsvFile'=>$CsvFile,
			'Results'=>$Results,
			'Organisation'=>$Organisation,
			'Uploads' => $Uploads
		));

	}


	/**
	 * This is the default 'view' action that is invoked
	 * when an action is not explicitly requested by users.
	 */
	public function actionCleaningUpload()
	{
		$CleaningFile = new CleaningFile;

		$Uploads = null;
		
		if(isset($_POST['CleaningFile']))
		{
			if($CleaningFile->save())
			{
				$subPath = 'cleaning-company/to-process';
				$filename = Yii::app()->basePath . '/../../protected-file-uploads/' . $subPath . '/'.$CleaningFile->uuid.".csv";
				
				if(!@move_uploaded_file($_FILES['CleaningFile']['tmp_name']['data'], $filename))
				{
					Yii::app()->user->setFlash('error', 'There was a problem uploading the file');
					$CleaningFile->delete();
				}
				else
				{
					Yii::app()->user->setFlash('success','Thanks, your CSV has been successfully uploaded and we will process it shortly.');
					$this->refresh();
				}
			}
		}
	
	
		$Uploads = CleaningFile::model()->findAll();



		$this->pageTitle = 'Cleaning Data | ' . Yii::app()->name;

		$this->breadcrumbs = array(
			'Cleaning Data Upload'
		);

		$this->render('upload-cleaning', array(
			'Success'=>$Saved,
			'CleaningFile'=>$CleaningFile,
			'Uploads' => $Uploads
		));

	}
}

?>