<?php

class CampaignController extends Controller
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
				'actions'=>array('index', 'createUpdate', 'inviteSend', 'export', 'run', 'fileUpload', 'fileDownload', 'results', 'download', 'upload', 'uploadOutcome'),
				'expression' => 'Yii::app()->user->role >= ' . User::ROLE_ORGANISATION,
			),

			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	/**
	 * This is the default 'view' action that is invoked
	 * when an action is not explicitly requested by users.
	 */
	public function actionIndex()
	{

		$Campaign = new Campaign('search');
		$Campaign->unsetAttributes();  // clear any default values

		//for grid search
		if(isset($_GET['Campaign']))
		{
			$Campaign->attributes = $_GET['Campaign'];
		}


		$this->pageTitle = 'Campaigns | ' . Yii::app()->name;
		$this->breadcrumbs=array(
			'Campaigns'
		);

		$this->render('index',array(
			'Campaign' => $Campaign,
		));

	}



	public function redirectRunCampaign($Campaign)
	{
		if((int)$Campaign->status !== Campaign::STATUS_NOT_RUN)
		{
			$this->redirect(array('campaign/results', 'id' => $Campaign->id));
		}
	}

	public function actionResults($id)
	{
		$Campaign = $this->loadModel($id);

		// Has this campaign been run?
		if(!in_array((int)$Campaign->status, array(
			Campaign::STATUS_HAS_BEEN_RUN,
			Campaign::STATUS_QUEUED
		)))
		{
			$this->redirect(array('campaign/createUpdate', 'id' => $Campaign->id));
		}

		$mailgunApi = new MailgunCampaign(Yii::app()->params['insiderEmailDomain'], Yii::app()->params['mailgun']['key']);

		$outcomeStasCommand = Yii::app()->db->createCommand("


			SELECT co.id AS campaign_outcome_id, co.name, sum(CASE WHEN outcome IS NOT NULL THEN 1 ELSE 0 END) AS positive_outcomes_count
			FROM campaign_outcome co
			LEFT JOIN campaign_contact2outcome cc2o ON cc2o.campaign_outcome_id = co.id
			WHERE co.campaign_id = :campaign_id
			GROUP BY co.id
		");
		$outcomeStasCommand->bindParam(':campaign_id', $Campaign->id);

		$stats = array(
			"opencount" => 0,
			"total_clickcount" => 0,
			"outcomes" => $outcomeStasCommand->queryAll(),
			"sent" => null
		);

		// Check if the campaign ID exists
		try{
			$mailgunCampaign = $mailgunApi->getCampaign(Yii::app()->params['insiderEmailDomain'], $Campaign->id);

			$stats['sent'] = $mailgunCampaign['created_at'];

			try{
				$mailgunContactOpens = $mailgunApi->getCampaignOpensByRecipientCount(Yii::app()->params['insiderEmailDomain'], $Campaign->id);

				$stats['opencount'] = $mailgunContactOpens['count'];

			}
			catch(Exception $e)
			{
				$mailgunContactOpens = array('error' => 'No open data available');
			}
		}
		catch(Exception $e)
		{
			$mailgunCampaign = array('error' => 'No campaign data available');
		}

		$QueryQuestions = QueryQuestion::model()->findAll(array('order'=>'type,id'));

		$this->render('results', array(
			'Campaign' => $Campaign,
			'QueryQuestions' => $QueryQuestions,
			'mailgunCampaign' => $mailgunCampaign,
			'mailgunContactOpens' => $mailgunContactOpens,
			'stats' => $stats
		));
	}


	/**
	 * Create or update a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionCreateUpdate()
	{

		$step = 1;
		if ($_GET['step']) $step = $_GET['step'];

		$Campaign = new Campaign;
		$Campaign->scenario = "insiderCampaign";

		$CampaignFile = new CampaignFile;
		$NewOutcome = new CampaignOutcome;
		$NewGroup = new CampaignGroup;

		$letters = array('A','B','C','D');

		if($_GET['id'])
		{
			$Campaign = $this->loadModel($_GET['id']);

			$this->redirectRunCampaign($Campaign);

			// the following is to cope with groups on various dev environments with no groups
			if(!sizeof($Campaign->groups))
			{
				foreach(array('A','B') as $letter)
				{
					$CampaignGroup = new CampaignGroup;
					$CampaignGroup->campaign_id = $Campaign->id;
					$CampaignGroup->name = $letter;
					$CampaignGroup->fraction = 50;
					$CampaignGroup->save();
				}

				$this->refresh();
			}
		}


		if(!$Campaign->isNewRecord && !is_null($Campaign->query) && (int)$Campaign->query->invite === 1)
		{

			// not allowed here as it's an invite campaign.
			throw new CHttpException('401', 'Forbidden');
		}


		if(!$Campaign->isNewRecord && isset($_GET['add-initial']) && !sizeof($Campaign->outcomes)){

			// add an initial default outcome
			$NewOutcome = new CampaignOutcome;
			$NewOutcome->campaign_id = $Campaign->id;
			$NewOutcome->name = 'An Outcome';
			$NewOutcome->description = 'An outcome description can help explain the desired result of this outcome. Delete this outcome and create your own below.';
			$NewOutcome->save();

			$this->redirect(array('campaign/createUpdate', 'id' => $Campaign->id));

		}

		if(sizeof($_POST['CampaignOutcome'])){

			// adding outcome to campaign
			$NewOutcome = new CampaignOutcome;
			$NewOutcome->campaign_id = $Campaign->id;
			$NewOutcome->name = $_POST['CampaignOutcome']['name'];
			$NewOutcome->description = $_POST['CampaignOutcome']['description'];
			$NewOutcome->url = $_POST['CampaignOutcome']['url'];

			if(!strlen($NewOutcome->url))
			{
				// save null, not
				$NewOutcome->url = null;
			}

			if($NewOutcome->save())
			{
				Yii::app()->user->setFlash('outcome-success', "Successfully created new outcome '" . $NewOutcome->name . "'");
				$this->redirect(array('campaign/createUpdate', 'id' => $Campaign->id,'step'=>2,'#'=>'outcomes'));

			}
			else
			{
				Yii::app()->user->setFlash('outcome-danger', 'Failed to create new outcome. See errors below.');
			}
		}


		if(isset($_GET['remove-outcome']))
		{
			$Criteria = new CDbCriteria;
			$Criteria->compare('id', (int)$_GET['remove-outcome']);
			$Criteria->compare('campaign_id', (int)$_GET['campaign_id']);
			$Outcomes = CampaignOutcome::model()->findAll($Criteria);

			if(sizeof($Outcomes) === 1){
				// deleting one

				$Outcomes[0]->delete();
			}

			Yii::app()->user->setFlash('outcome-success', "Successfully removed the outcome.");
			$this->redirect(array('campaign/createUpdate', 'id' => $Campaign->id,'step'=>2, '#'=>'outcomes'));

		}


		if(isset($_GET['remove-group']))
		{
			$Criteria = new CDbCriteria;
			$Criteria->compare('id', (int)$_GET['remove-group']);
			$Criteria->compare('campaign_id', $Campaign->id);
			CampaignGroup::model()->deleteAll($Criteria);

			Yii::app()->user->setFlash('group-success', "Successfully removed the group.");
			$this->redirect(array('campaign/createUpdate', 'id' => $Campaign->id,'step'=>3, '#'=>'groups'));
		}


			if(sizeof($_POST['CampaignGroup']))
			{

				$step = 3;

				if ($_POST['CampaignGroup']['id']) {

					$CampaignGroup = CampaignGroup::model()->findByPk($_POST['CampaignGroup']['id']);
					$CampaignGroup->setAttributes($_POST['CampaignGroup']);
					if($CampaignGroup->save()) {
						Yii::app()->user->setFlash('group-success', 'Group "' . $CampaignGroup->name . '" updated successfully');
						$this->redirect(array('campaign/createUpdate', 'id' => $Campaign->id,'step'=>3,'#'=>'groups'));

					}
					else {
						$errors = '';
						foreach ($CampaignGroup->errors as $error) {
							$errors .=  $error[0] . ' ';
						}
						Yii::app()->user->setFlash('group-danger', $errors);
					}
				}
				else
				{
					//its new
					$NewGroup->setAttributes($_POST['CampaignGroup']);
					$NewGroup->campaign_id = $Campaign->id;
					if($NewGroup->save())
					{
						Yii::app()->user->setFlash('group-success', 'Group "' . $NewGroup->name . '" updated successfully');
						$this->redirect(array('campaign/createUpdate', 'id' => $Campaign->id,'step'=>3, '#'=>'groups'));
					}

					else {
						$errors = '';
						foreach ($NewGroup->errors as $error) {
							$errors .=  $error[0] . ' ';
						}
						Yii::app()->user->setFlash('group-danger', $errors);
					}

				}
		

		}


		if($_POST['Campaign'])
		{
			$Campaign->setAttributes($_POST['Campaign']);

			if($Campaign->save())
			{
				if(!(int)$_GET['id'])
				{
					// new. Add default 50% groups
					foreach(array('A','B') as $letter)
					{

						$CampaignGroup = new CampaignGroup;
						$CampaignGroup->campaign_id = $Campaign->id;
						$CampaignGroup->name = $letter;
						$CampaignGroup->fraction = 50;
						$CampaignGroup->save();
					}
				}

				Yii::app()->user->setFlash('campaign-success', 'Campaign ' . ((int)$_GET['id'] ? 'updated' : 'created') . ' successfully');
				
				//if it's a new record then go to step 2, otherwise go to step 1
				if($Campaign->isNewRecord) {
					$this->redirect(array('campaign/createUpdate', 'id' => $Campaign->id,'step'=>2, '#'=>'outcomes'));
				}
				else {
					$this->redirect(array('campaign/createUpdate', 'id' => $Campaign->id,'step'=>1));

				}
			}
		}

		// check group totals
		$totalPercentage = 0;
		foreach($Campaign->groups as $CampaignGroup)
		{
			$totalPercentage += $CampaignGroup->fraction;
		}

		if((int)$totalPercentage !== 100)
		{
			Yii::app()->user->setFlash('group-danger', 'The percentage splits of campaign groups must total 100. Currently group percentage splits total ' . $totalPercentage . '.');
		}


		// check for subjectless groups
		$SubjectlessCampaignGroups = $Campaign->groups(array('condition' => 'isnull(`groups`.`subject`) || LENGTH(`groups`.`subject`) < 1'));

		if (!$Campaign->groups) {
			$MissingTemplates = true;
		}
		else {
			$MissingTemplates = false;
		}
			
		// check for templateless groups
		foreach ($Campaign->groups as $Group) {
			if (!$Group->email_template) {
				$MissingTemplates = true;
				break;
			}
		}

		$Queries = Query::model()->findAll(array(
			'condition' => 'invite = 0', // not invites
			'index' => 'id',
			'order' => 'name ASC',
		));

		if(isset($Queries[$Campaign->query_id]))
		{
			$Query = null;
			if(!$Campaign->isNewRecord)
			{
				if($Campaign->size > 0)
				{
					// we have to faff here.
					// we need to add a limit to an existing query as we can't pass it through the run params.
					// so we decode, add it and then encode again so when it's run it has the right limit
					$JSON = json_decode($Queries[$Campaign->query_id]->JSON);
					$JSON->limit = $Campaign->size;
					$Queries[$Campaign->query_id]->JSON = json_encode($JSON);
				}

				$Query = $Queries[$Campaign->query_id]->run();
			}
		}

		$this->pageTitle = ((int)$_GET['id'] ? 'Update' : 'Create') . ' Campaign | ' . Yii::app()->name;

		$this->breadcrumbs=array(
			'Campaigns' => array('index'),
			((int)$_GET['id'] ? 'Update' : 'Create') . ' Campaign'
		);

		$Campaign->refresh();

		$this->render('createUpdate',array(
			'Campaign'=>$Campaign,
			'CampaignFile' => $CampaignFile,
			'Queries' => $Queries,
			'Query' => $Query,
			'NewOutcome' => $NewOutcome,
			'NewGroup' => $NewGroup,
			'step' => $step,
			'totalPercentage' => $totalPercentage,
			'SubjectlessCampaignGroups' => $SubjectlessCampaignGroups,
			'MissingTemplates' => $MissingTemplates
		));
	}


	/* show the group export details and buttons */
	public function actionExport($id)
	{

		$Campaign = $this->loadModel($id);

		$this->pageTitle = 'Export campaign groups | ' . Yii::app()->name;
		$this->breadcrumbs=array(
			'Campaigns' => array('index'),
			$Campaign->name => array('campaign/createUpdate', 'id' => $Campaign->id),
			'Export campaign group data'
		);

		$this->render('export', array(
			'Campaign' => $Campaign,
		));

	}




	/* view the warning and option to run this campaign. Show the download file after. */
	public function actionRun($id)
	{

		$Campaign = $this->loadModel($id);

		$this->redirectRunCampaign($Campaign);

		if(sizeof($Campaign->groups))
		{
			if((int)$Campaign->type === Campaign::TYPE_EMAIL)
			{
				// Email campaigns require a subject for each group

				foreach($Campaign->groups as $Group)
				{
					if(!strlen($Group->subject))
					{
						Yii::app()->user->setFlash('danger', 'Ensure all campaign groups have subjects');
						$this->redirect(array('campaign/createUpdate', 'id' => $Campaign->id));
					}
				}
			}
		}
		else
		{
			// no groups.
			Yii::app()->user->setFlash('danger', 'A campaign requires groups before being run.');
			$this->redirect(array('campaign/createUpdate', 'id' => $Campaign->id));
		}

		if($_POST['Campaign']['id'])
		{
			// doing the run

			// we have to faff here.
			// we need to add a limit to an existing query as we can't pass it through the run params.
			// so we decode, add it and then encode again so when it's run it has the right limit
			$JSON = json_decode($Campaign->query->JSON);
			$JSON->limit = $Campaign->size;
			$Campaign->query->JSON = json_encode($JSON);


			// match users based on query.
			$results = $Campaign->query->runCampaignQuery();

			$Campaign->json = $Campaign->query->JSON;
			$Campaign->save(true, array('json'));

			$results = $results['rows'];

			// shuffle the results
			shuffle($results);

			// groups
			$groups = array();
			$resultsSize = sizeof($results);
			$start = 0;

			foreach($Campaign->groups as $key => $CampaignGroup)
			{
				// where to slice to?
				if(($key+1) === sizeof($Campaign->groups))
				{
					$end = count($results);
				}
				else
				{
					$end = round($resultsSize*($CampaignGroup->fraction/100));
				}
				$groups[$CampaignGroup->id] = array_splice($results, 0, $end);
			}


			// save users to new campaign2cleanwarehouse
			// offer download link of csv

			foreach($groups as $groupId => $results)
			{

				$rows = sizeof($results);
				$loop = 0;
				$Command = Yii::app()->db->schema->commandBuilder;

				foreach($results as $key => $result)
				{
					$CampaignContact = new CampaignContact;
					$CampaignContact->setAttributes(array(
						'campaign_id' => $Campaign->id,
						'group_id' => $groupId,
						'warehouse_id' => $result['contact_warehouse_id'],
						'hash' => $Campaign->generateHash(6),
					));

					$nodes = [];

					if($CampaignContact->save())
					{
						foreach($Campaign->outcomes as $Outcome)
						{
							// create a node for each outcome
							$nodes[] = array(
								'campaign_contact_id' => $CampaignContact->id,
								'campaign_outcome_id' => $Outcome->id,
								'hash' => $Campaign->generateHash(6),
							);
						}

						if(sizeof($nodes))
						{
							// insert our outcomes for this campaign_contact
							$Command->createMultipleInsertCommand('campaign_contact2outcome', $nodes)->execute();
						}
					}
					else
					{
						var_dump($CampaignContact->errors); exit;
					}

					unset($CampaignContact, $nodes);
				}
			}

			// now we've created all the contact rows, set the campaign to queued (or run if not an email)
			if($Campaign->type == Campaign::TYPE_EMAIL)
			{
				$Campaign->status = Campaign::STATUS_QUEUED;
			}
			else
			{
				$Campaign->status = Campaign::STATUS_HAS_BEEN_RUN;
			}

			$Campaign->save(false, array('status'));

			Yii::app()->user->setFlash('success', 'Campaign run successfully. Download query results below.');
			$this->redirect(array('campaign/createUpdate', 'id' => $Campaign->id));
		}


		$this->pageTitle = 'Run Campaign | ' . Yii::app()->name;

		$this->breadcrumbs=array(
			'Campaigns' => array('index'),
			$Campaign->name => array('campaign/createUpdate'),
			'Run Campaign'
		);

		$this->render('run', array(
			'Campaign' => $Campaign,
			'Query' => $Campaign->query,
		));

	}





	public function actionFileUpload($id)
	{

		$Campaign = $this->loadModel($id);

		$this->redirectRunCampaign($Campaign);

		$CampaignFile = new CampaignFile;
		$fileDestination = Yii::app()->basePath . '/../../protected-file-uploads/campaign-files/';

		if(isset($_POST['CampaignFile']))
		{

			$CampaignFile->campaign_id = $Campaign->id;
			$CampaignFile->name = $_POST['CampaignFile']['name'];
			$CampaignFile->newFile = CUploadedFile::getInstance($CampaignFile, 'newFile');


			if($CampaignFile->save())
			{

				// save the actual file.
				$CampaignFile->newFile->saveAs($fileDestination . $CampaignFile->id . '.' . $CampaignFile->newFile->extensionName);

				// send back to campaign with success flash
				Yii::app()->user->setFlash('success', 'New file ' . $CampaignFile->name . ' uploaded successfully.');
				$this->redirect(array('campaign/createUpdate', 'id' => $Campaign->id));
			}
		}


		$this->render('fileUpload', array(
			'CampaignFile' => $CampaignFile,
			'Campaign' => $Campaign,
		));

	}



	public function actionFileDownload($id){

		$Campaign = $this->loadModel($id);

		$this->redirectRunCampaign($Campaign);

		$CampaignFile = CampaignFile::model()->find(array(
			'condition' => "
				id = :id
				AND secret = :secret
			",
			'params' => array(
				'id' => $_GET['file_id'],
				'secret' => $_GET['secret']
			),
		));


		$filePath = Yii::app()->basePath . '/../../protected-file-uploads/campaign-files/' . $CampaignFile->id . '.' . $CampaignFile->extension;


		if(is_null($CampaignFile) || !file_exists($filePath)){
			throw new CHttpException('404', 'File not found');
		}


		// spit the file.
		return Yii::app()->request->sendFile($CampaignFile->name . '.' . $CampaignFile->extension, @file_get_contents($filePath));

	}



	/*
	*	Download Campaign data
	*/
	public function actionDownload($id)
	{
		// no limit for this. See #472
		ini_set('memory_limit', '-1');

		// ensure no timeout occurs during the creation of csv
		ini_set('max_execution_time', '180');

		// do not include log info in generated file
		foreach(Yii::app()->log->routes as $route)
		{
			if ($route instanceof CProfileLogRoute)
			{
				$route->enabled = false;
			}
		}

		$Store = new Store;
		$Campaign = Campaign::model()->findByPk($id, array(
			'select' => array(
				'name'
			),
			'with' => array(
				'contacts' => array(
					'select' => array(
						'id',
						'opened',
						'bounced',
						'warehouse_id',
					),
					'index' => 'warehouse_id',
					'with' => array(
						'contact2outcomes' => array(
							'with' => 'campaign_outcome',
							'select' => array(
								'outcome'
							)
						),
						'group'
					)
				),
				'outcomes' => array(
					'select' => array(
						'id',
						'name'
					)
				)
			)
		));

		$implodedContactKeys = implode(', ', array_keys($Campaign->contacts));


		if(!sizeof($implodedContactKeys)){
			throw new CHttpException('404', 'That campaign has no data to return');
		}

		$Accessions = Accession::model()->findAll(array(
			//'select' => 'culture_segment',
			'condition' => 'warehouse_id IN (' . $implodedContactKeys . ')',
			'index' => 'warehouse_id' // so we can match them back up to the data above
		));

		$Store2Contacts = Store2Contact::model()->findAll(array(
			'condition' => 'contact_warehouse_id IN (' . $implodedContactKeys . ')',
			'index' => 'contact_warehouse_id',
			'with' => array(
				'store'
			)
		));

		unset($implodedContactKeys);

		// build the csv
		$rows = array();
		$outcomeColumns = array();

		$headings = array(
			'campaign_contact_id' => 'campaign_contact_id',
			'salutation' => 'Prefix',
			'first_name' => 'Forename',
			'last_name' => 'Surname',
			'email' => 'Email',
			'culture_segment' => 'Culture Segment',
		);

		$csvHeader = array_values($headings);

		// add each outcome as a column heading
		foreach($Campaign->outcomes as $Outcome)
		{
			$csvHeader[] = 'outcome_' . $Outcome->id . ' - ' . $Outcome->name;
		}

		// add an empty one for
		$csvHeader[] = "Group";
		$csvHeader[] = "Opened";
		$csvHeader[] = "Bounced";

		header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
		header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

		header('Content-Encoding: UTF-8');
		header('Content-type: text/csv; charset=UTF-8');

		// disposition / encoding on response body
		header("Content-Disposition: attachment;filename=Contacts - " . $Campaign->name . ".csv");
		header("Content-Transfer-Encoding: binary");

		flush();

		$csv = fopen('php://output', 'w');

		fputcsv($csv, $csvHeader);

		// build each row
		$loop = 0;
		foreach($Campaign->contacts as $warehouse_id => $CampaignContact)
		{
			$row = array();

			// use headings to get data for basic details
			foreach($headings as $key => $value)
			{
				switch($key){

					case 'campaign_contact_id':
					{
						$row['campaign_contact_id'] = $Campaign->contacts[$warehouse_id]->id;
						break;
					}

					case 'email':
					{
						// already decrypted on find
						$row[$value] = $Store2Contacts[$warehouse_id]->store->{$key};
						break;
					}

					case 'last_name':
					{
						// already decrypted on find
						$row[$value] = $Store2Contacts[$warehouse_id]->store->{$key};
						break;
					}

					case 'culture_segment':
					{
						$row[$value] = $Accessions[$warehouse_id]->culture_segment;
						break;
					}

					default:
					{
						$row[$value] = $Store2Contacts[$warehouse_id]->store->{$key};
					}
				}

			}

			// do outcome row values
			foreach($CampaignContact->contact2outcomes as $Outcome)
			{
				$row[$Outcome->campaign_outcome->name] = $Outcome->outcome;
			}

			$row['Group'] = $CampaignContact->group->name;

			// open and bounce. Once will likely be null / blank.
			$row['Opened'] = $CampaignContact->opened;
			$row['Bounced'] = $CampaignContact->bounced;

			//$rows[] = $row;
			fputcsv($csv, $row, ',', '"');

			if (!($loop++ % 100)) {
				ob_flush();
				flush(); // Attempt to flush output to the browser every 100 lines.
						 // You may want to tweak this number based upon the size of your CSV rows.
			}

			unset($row);
			unset($CampaignContact);
		}

		fclose($csv);

	}


	/*
	*	Upload Campaign outcomes
	*/
	public function actionUpload($id)
	{

		$CampaignOutcomesFile = new CampaignOutcomesFile;
		$Campaign = Campaign::model()->with(array(
			'outcomes' => array(
				'index' => 'id'
			)
		))->findByPk($id);
		$outcomes = [];

		if(!sizeof($Campaign->outcomes))
		{
			$CampaignOutcomesFile->addError('file', 'This campaign has no outcomes and as such cannot be updated via file upload.');
		}

		// check for upload file
		if($_FILES['CampaignOutcomesFile'] && strlen($_FILES['CampaignOutcomesFile']['tmp_name']['file']))
		{
			// if yes
				// check each row matches a contact in this campaign
			//get the csv file 
			$fh = fopen($_FILES['CampaignOutcomesFile']['tmp_name']['file'], "r"); 

			// stores data fails even though we still save
			$fails = [];

			// store number of successes so we can tell rows and individual columns;
			$successes = [];

			//loop through the csv file and insert into database 
			for ($lines = 0; $data = fgetcsv($fh, 0, ",", '"'); $lines++) {

				// skip the headers
				if($lines === 0)
				{
					// correct header row
					if($data[0] === 'campaign_contact_id')
					{
						// save headers for later 
						foreach($data as $columnIndex => $column)
						{
							if(preg_match("@^outcome_([1-9][0-9]*) - \w+@", $column, $outcomeMatch))
							{
								// check the outcome is for this campaign.
								if(!array_key_exists($outcomeMatch[1], $Campaign->outcomes))
								{
									// this outcome doesn't belong to this campaign
									$CampaignOutcomesFile->addError('file', 'The outcome "' . $column . '" does not belong to this campaign.');
								}

								// store each outcome for later.
								$outcomes[$columnIndex] = $outcomeMatch[1];
							}
						}

						if(!sizeof($outcomes))
						{
							// no outcomes found.
							$CampaignOutcomesFile->addError('file', 'Your file upload contains no outcome columns of the format outcome_123 as included in the campaign snapshot download.');
						}

						if($CampaignOutcomesFile->hasErrors()){
							// don't parse it
							break;
						}

						continue;
					}

					// incorrect header row
					$CampaignOutcomesFile->addError('file', 'That file does not appear to match the format provided by campaign snapshot download');
					break;
				}

				// loop any manual outcome columns
				foreach($outcomes as $columnIndex => $outcome)
				{
					// update with either a null or a date.

					// expect a datetime
					$outcomeDateTimeOrNull = null;

					if(trim($data[$columnIndex]) !== '')
					{
						$unixTime = strtotime($data[$columnIndex]);
						if($unixTime < strtotime('1st January 2014')) // allow historic, but be sensible
						{
							// we still save. But show this as a flash later anyway.
							$fails[] = 'campaign_contact_id ' . $data[0] . ' error found on file row ' . ($lines+1) . ': Outcome outcome_' . $outcome . ' column has an invalid date or date prior to 2014-01-01';
							continue;
						}

						$outcomeDateTimeOrNull = date('Y-m-d H:i:s', $unixTime);
					}

					// do update on those rows
					CampaignContact2Outcome::model()->updateAll(array(
						// update outcome
						'outcome' => $outcomeDateTimeOrNull
					),
						// condition
						"campaign_contact_id = :campaign_contact_id
							AND campaign_outcome_id = :campaign_outcome_id
					",
						// params
					array(
						':campaign_contact_id' => (int)$data[0],
						':campaign_outcome_id' => $outcome
					));

					$successes[$lines]++;
				}
			}

			if(sizeof($successes))
			{
				// at least something got updated.
				Yii::app()->user->setFlash('success', sizeof($successes) . ' rows provided ' . (sizeof($fails) ? 'partial or ' : '') . 'complete valid outcome data to allow outcome updates.' . (sizeof($fails) ? '.. However...' : ' No errors found.'));
			}

			if(sizeof($fails))
			{
				// set fails to show too.
				$failsHtml = '&bull; ' . implode('<br />&bull; ', $fails);
				Yii::app()->user->setFlash('danger', 'There were ' . sizeof($fails) . ' errors during the parsing of you campaign file upload. After taking action you can reupload the file without risk of any successfully updated outcomes being affected:<br />' . $failsHtml);
			}

			// reload it - then flashes will be shown.
			$this->refresh();

		}

		$this->pageTitle = 'Upload Campaign Outcomes | ' . Yii::app()->name;

		$this->breadcrumbs=array(
			'Campaigns' => array('index'),
			$Campaign->name => array('campaign/createUpdate', 'id' => $Campaign->id),
			'Upload Campaign Outcome data'
		);

		// show form
		$this->render('upload', array(
			'Campaign' => $Campaign,
			'CampaignOutcomesFile' => $CampaignOutcomesFile
		));

	}


	/*
	*	Upload a single Campaign Outcome
	*/
	public function actionUploadOutcome($id)
	{
		// ensure csv line endings are correctly recognised.
		ini_set('auto_detect_line_endings', true);

		$Campaign = Campaign::model()->with(array(
			'outcomes' => array(
				'index' => 'id'
			)
		))->findByPk($id);

		if(!sizeof($Campaign->outcomes))
		{
			Yii::app()->user->setFlash('Warning', 'This campaign has no outcomes and as such cannot be updated via file upload.');
		}

		// check for upload file
		if(sizeof($Campaign->outcomes) && sizeof($_FILES['file']) && strlen($_FILES['file']['tmp_name']))
		{

			// confirm the organisation.
			if(Yii::app()->user->role < User::ROLE_MANAGER)
			{
				$organisation_id = Yii::app()->user->organisation_id;
			}
			else
			{
				// should have one in post.
				if(!is_numeric($_POST['organisation_id'])) {
					throw new CHttpException('400', 'Bad Request - missing organisation_id');
				}

				$organisation_id = (int)$_POST['organisation_id'];
			}

			$Organisation = Organisation::model()->findByPk($organisation_id);


			// check outcome belongs to campaign
			if(!array_key_exists($_POST['outcome_id'], $Campaign->outcomes))
			{
				// not a valid outcome
				throw new CHttpException('400', 'Bad Request - invalid outcome.');
			}


			//get the csv file 
			$fh = fopen($_FILES['file']['tmp_name'], "r"); 


			// store number of successes so we can tell rows and individual columns;
			$successes = [];


			//loop through the csv file and gather unique ids
			for ($lines = 0; $data = fgetcsv($fh); $lines++) {

				if(strlen($data[0])){
					$uniqueIDs[] = $data[0];
				}
			}

			if(!sizeof($uniqueIDs))
			{
				exit('no uniques');
			}


			$CDbCriteria = new CDbCriteria;
			$CDbCriteria->join = 'INNER JOIN store2contact ON `t`.warehouse_id = `store2contact`.`contact_warehouse_id`';
			$CDbCriteria->addCondition('`t`.`campaign_id` = :campaign_id');
			$CDbCriteria->addCondition('`store2contact`.origin_id = :origin_organisation_id');

			$CDbCriteria->params = array(
				':campaign_id' => $Campaign->id,
				':origin_organisation_id' => (int)$_POST['organisation_id']
			);

			$CDbCriteria->compare('`store2contact`.`origin_unique_id`', $uniqueIDs);
			$CDbCriteria->index = 'id';

			$CampaignContacts = CampaignContact::model()->findAll($CDbCriteria);


			// update all who match against a campaign contact 2 outcome

			if(sizeof($CampaignContacts)){

				// do update on those rows
				CampaignContact2Outcome::model()->updateAll(array(
					// update outcome
					'outcome' => date("Y-m-d H:i:s"),
				),
					// condition
					"campaign_contact_id IN (" . implode(", ", array_keys($CampaignContacts)) . ")
						AND campaign_outcome_id = :campaign_outcome_id
				",
					// params
				array(
					':campaign_outcome_id' => $_POST['outcome_id']
				));
			}


			// at least something got updated.
			Yii::app()->user->setFlash('success', sizeof($CampaignContacts) . ' (of ' . $lines . ') rows provided a matching contact to allow outcome update.');
			$this->refresh();

		}

		$this->pageTitle = 'Upload Campaign Outcome Users | ' . Yii::app()->name;

		$this->breadcrumbs=array(
			'Campaigns' => array('index'),
			$Campaign->name => array('campaign/createUpdate', 'id' => $Campaign->id),
			'Upload Campaign Outcome Users'
		);

		// show form
		$this->render('uploadOutcome', array(
			'Campaign' => $Campaign,
		));

	}




	public function loadModel($id)
	{
		$Model=Campaign::model()->findByPk($id);
		if($Model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $Model;
	}

}

?>