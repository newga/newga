<?php

class QueryController extends Controller
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
				'actions'=>array('index','create','update','ajax','save_query','invite', 'run', 'download'),
				'expression' => 'Yii::app()->controller->accessFilter()'
			),

			array('allow',
				'actions'=>array('error'),
				'users'=>array('*'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function accessFilter() {
		// is the data base dirty and do they have access?

		return (int)Yii::app()->user->role >=  User::ROLE_ORGANISATION;

	}

	/**
	 * This is the default 'view' action that is invoked
	 * when an action is not explicitly requested by users.
	 */
	public function actionIndex()
	{
		$Query = new Query('search');

		$Query->unsetAttributes();  // clear any default values


		//for grid search
		if(isset($_GET['Query'])) {
        	$Query->attributes=$_GET['Query'];
		}

		$Query->invite = 0;


		$this->pageTitle = 'Queries | ' . Yii::app()->name;
        $this->breadcrumbs=array(
			'Queries'
		);

		$this->render('index',array(
			'Query'=>$Query,
		));

	}


	public function actionRun($id)
	{

		$time_start = microtime(true);


		$Query = $this->loadModel($id);


		//$result = $Query->run(array('contact_warehouse_id'), null, false, false, true, true);
		$result = $Query->runCampaignQuery(array(
			'contact_warehouse_id',
			'terms_agreed',
			'dob',
			'origin_organisation_id',
			'culture_segment',
		));

		$people = $result['rows'];
		$sql_string = $result['sql'];


		if((int)Yii::app()->user->organisation_id)
		{
			$downloadResultSize = 0;
		}
		else
		{
			// same number.
			$downloadResultSize = sizeof($people);
		}


		Yii::app()->clientScript->registerScriptFile('https://www.google.com/jsapi');


		$this->pageTitle = 'Run | Queries | ' . Yii::app()->name;
		$this->breadcrumbs=array(
			'Queries' => array('index'),
			$Query->name => array('query/update', 'id' => $Query->id),
			'Run'
		);

		$this->render('run',array(
			'Query' => $Query,
			'people' => $people,
			'string' => $sql_string,
			'downloadResultSize' => $result['count'],
		));

	}


	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{

		$Query = new Query();
		$Query->JSON = json_encode(array('rows' => array()));

		$queryResults = $Query->run(array(), null, false, false, true);
		$QueryQuestions = QueryQuestion::model()->findAll(array('order'=>'type,id'));

		$this->pageTitle = 'Create | Queries | ' . Yii::app()->name;
		$this->breadcrumbs=array(
			'Queries' => array('index'),
			'Create Query'
		);

		$this->render('create',array(
			'Query'=>$Query,
			'QueryQuestions'=>$QueryQuestions,
			'queryResults' => $queryResults,
		));
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id)
	{

		$Query = $this->loadModel($id);

		//you can only edit a query if you are a manager or higher or if it is your own
		//so send the user to run if it's not
		if (!$Query->canUserEdit)
		{
			$this->redirect(array('query/run','id'=>$Query->id));
			Yii::app()->end();
		}



		$QueryQuestions = QueryQuestion::model()->findAll(array('order'=>'type,id'));

		$queryResults = $Query->run();

		$this->pageTitle = 'Update | Queries | ' . Yii::app()->name;
		$this->breadcrumbs=array(
			'Queries' => array('index'),
			"Update '" . $Query->name . "'"
		);

		$this->render('update',array(
			'Query'=>$Query,
			'QueryQuestions'=>$QueryQuestions,
			'queryResults' => $queryResults
		));
	}


	public function loadModel($id)
	{
		$Model=Query::model()->findByPk($id);
		if($Model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $Model;
	}



	public function actionAjax()
	{
		//this function does one of three things depending on payload
		//would be better in hindsight to send an "action" command



		//if we want to save the query
		if (isset($_POST['save'])) {
			
			//if id is set, there we are updating, else it's a new query

			if (isset($_POST['query_id']) && (int)$_POST['query_id']) {
				//we are updating
				$Query = Query::model()->findByPk($_POST['query_id']);
			}
			else {
				$Query = new Query;
				$Query->created = date("Y-m-d H:i:s");
			}

			// Is this an invite query?
			$Query->invite = isset($_POST['invite']) ? 1 : 0;

			$Query->user_id = Yii::app()->user->id;
			$Query->name = $_POST['Query']['name'];
			$Query->description = $_POST['Query']['description'];
			$Query->JSON = $this->getQueryJSON();

			if($Query->save())
			{
				if(!$_POST['query_id'])
				{
					// Creating a Query or an Invite?
					if($Query->invite)
					{
						// Create a campaign to go with this Query - it has to have one
						$Campaign = new Campaign;
						$Campaign->name = $Query->name;
						$Campaign->description = $Query->description;
						$Campaign->query_id = $Query->id;
						$Campaign->status = Campaign::STATUS_NOT_RUN;
						$Campaign->processing = 0;

						if($Campaign->save())
						{
							$errorOccured = false;

							// Everything is saved ok. Now run the query and get all the contacts
							$queryResults = $Query->runInviteContactQuery();

							// loop array and add each one to invite table
							foreach($queryResults['rows'] as $contact)
							{
								// Create a new Invite model
								$Invite = new Invite;
								$Invite->contact_warehouse_id = $contact['contact_warehouse_id'];
								$Invite->store2contact_id = $contact['store2contact_id'];
								$Invite->store_id = $contact['store_id'];
								$Invite->organisation_id = $contact['origin_organisation_id'];
								$Invite->hash = sha1($contact['contact_warehouse_id'] . $contact['store2contact_id'] . $contact['origin_organisation_id'] . microtime(true) . SHASALT);
								$Invite->date = date('Y-m-d H:i:s');
								$Invite->query_id = $Campaign->query_id;
								$Invite->campaign_id = $Campaign->id;
								$Invite->status = Invite::STATUS_UNSENT;
								$Invite->processing = 0;

								if(!$Invite->save())
								{
									$errorOccured = true;

									$errors = print_r($Invite->errors, true);

									Yii::log('Error saving Invite model: ' . $errors, 'error');
								}
								else
								{
									
								}

								unset($Invite);
							}

							if($errorOccured)
							{
								mail('email@example.com', 'Website Error', 'Invite attempted Invite model could not be saved. See Application logs.');
							}

							$Query->num_contacts = sizeof($queryResults['rows']);
							$Query->save(true, array('num_contacts'));

							// new. set flash then return request to redirect.
							Yii::app()->user->setFlash('success', 'The new invite has been created successfully.');
							$array = array(
								'success' => true,
								'redirect' => $this->createUrl('invite/index'),
							);
						}
						else
						{
							throw new CHttpException('500', 'Error saving campaign');
						}
					}
					else{
						// new. set flash then return request to redirect.
						//Run query to get count to save.
						$queryResults = $Query->runCampaignCountQuery();
						$Query->num_contacts = $queryResults['count'];
						$Query->save(true, array('num_contacts'));
						Yii::app()->user->setFlash('success', 'The new query has been created successfully.');
						$array = array(
							'success' => true,
							'redirect' => $this->createUrl('query/update', array('id' => $Query->id)),
						);
					}

				}
				else
				{

					$queryResults = $Query->runCampaignCountQuery();

					$Query->num_contacts = $queryResults['count'];
					$Query->save(true, array('num_contacts'));

					$array = array(
			    		"success" => true,
		    			'id' => $Query->id,
		    			'resultsTotal' => number_format($queryResults['count']),
					);
				}
			}
			else {
				$array = array(
		    		"errors"    => $Query->getErrors()
				);

			}
			header('Content-Type: application/json');
			print CJSON::encode($array);

		}

		else if (isset($_POST['new-row']))
		{
			$rowNumber = time();



			$Question = QueryQuestion::model()->findByPk($_POST['new']['query_choice']);
			$QueryQuestions = QueryQuestion::model()->findAll(array('order'=>'type,id'));


			header('Content-Type: application/json');

			print json_encode(array(
				'html' => $this->renderPartial('_row',array(
					'Question' => $Question,
					'QueryQuestions'=>$QueryQuestions,
					'and_choice' => $_POST['new']['and_choice'],
					'bool_choice' => $_POST['new']['bool_choice'],
					'query_choice' => $_POST['new']['query_choice'],
					'query_number' => $_POST['new']['query_number'],
					'query_option' => $_POST['new']['query_option'],
					'rowNumber' => $rowNumber,
				), true),

			));

		}


		else if (isset($_POST['render'])) {

			//just render the question options
			//get the query question with that id
			$Question = QueryQuestion::model()->findByPk($_POST['id']);
			//render partial
			$this->renderPartial('_options',array(
				'Question' => $Question,
				'rowNumber' => $_POST['rowNumber']
			));

		}
		elseif(isset($_POST['results']))
		{
			if($_POST['query_id'])
			{
				$Query = Query::model()->findByPk($_POST['query_id']);

				if(is_null($Query))
				{
					throw new CHttpException(404, 'Not found');
				}

				$Query->JSON = $this->getQueryJSON();
			}
			else
			{
				$Query = new Query;
				$Query->JSON = $this->getQueryJSON();
			}

			if(isset($_POST['invite']))
			{
				$queryResults = $Query->runInviteCountQuery();
			}
			else
			{
				$queryResults = $Query->runCampaignCountQuery();
			}

			header('Content-Type: application/json');

			$queryResults['results'] = number_format($queryResults['count']);

			unset($queryResults['rows']); // Don't need rows on query page, just extra HTTP traffic

			print json_encode($queryResults);
		}
		else {
			throw new CHttpException(404,'The requested page does not exist.');
		}
	}

	/*
	 * Looks for $_POST['current'] as the set of current rules and turns them into JSON
	 * @return JSON string
	 */
	public function getQueryJSON()
	{
		$json = array();
		$rows = array();

		if(sizeof($_POST['current']))
		{
			foreach($_POST['current'] as $field => $values)
			{
				foreach($values as $k => $value)
				{
					$rows[$k][$field] = $value;
				}
			}
		}

		$json['rows'] = $rows;

		if(isset($_POST['limit']) && is_integer((int)$_POST['limit']))
		{
			$json['limit'] = (int)$_POST['limit'];
		}

		return CJSON::encode($json);
	}



	//Creates a query but we predefined variables
	public function actionInvite()
	{
		$Query = new Query();

		//hard code email and commonwealth
		$Query->JSON = CJSON::encode(array('rows' => array()));

		$queryResults = $Query->runInviteCountQuery();

		$QueryQuestions = QueryQuestion::model()->findAll(array('order'=>'type,id'));


		$this->pageTitle = 'Invite | ' . Yii::app()->name;
		$this->breadcrumbs=array(
			'Invites' => array('invite/index'),
			'Create Invite Query'
		);

		$this->render('create', array(
			'Query' => $Query,
			'QueryQuestions' => $QueryQuestions,
			'invite' => true,
			'queryResults' => $queryResults,
		));
	}


	public function actionDownload($id)
	{

		// do not include log info in generated file
		foreach(Yii::app()->log->routes as $route)
		{
			if ($route instanceof CProfileLogRoute)
			{
				$route->enabled = false;
			}
		}

		// download (filtered) results of a query
		$Query = Query::model()->findByAttributes(array(
			'id' => $id,
		));

		if(is_null($Query)){
			throw new CHttpException('404', 'File not found');
		}

		$result = $Query->run(array('*'), (int)Yii::app()->user->organisation_id ? (int)Yii::app()->user->organisation_id : null);


		if(!sizeof($result['rows'])){
			throw new CHttpException('404', 'Results not found');
		}

		$cleanQueryName = preg_replace(array(
			"@[^a-z0-9\-\s]@i",
			"@\s+@",
			"@\-{2,}@"
		), array(
			"",
			"-",
			"-"
		), $Query->name) . '-' . date("Y-m-d");


		$csv = fopen('php://output', 'w');

		header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
		header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

		header('Content-Encoding: UTF-8');
		header('Content-type: text/csv; charset=UTF-8');

		// disposition / encoding on response body
		header("Content-Disposition: attachment;filename=" . $cleanQueryName . ".csv");
		header("Content-Transfer-Encoding: binary");


		$columns = array(
			'contact_warehouse_id',
			'salutation',
			'first_name',
			'last_name',
			'email',
			'phone',
			'mobile',
			'address_line_1',
			'address_line_2',
			'address_line_3',
			'address_line_4',
			'address_town',
			'address_postcode',
			'address_county'
		);

		// header row
		fputcsv($csv, $columns);

		$Store = new Store;

		foreach ($result['rows'] as $row)
		{
			$data = array();
			foreach($columns as $column)
			{
				switch($column)
				{
					case 'email':
					{
						$data[$column] = $Store->decryptEmail($row[$column]);
						break;
					}

					case 'last_name':
					{
						$data[$column] = $Store->decryptLastName($row[$column]);
						break;
					}

					case 'phone':
					{
						$data[$column] = $Store->decryptPhone($row[$column]);
						if(strlen($data[$column]) && mb_substr($data[$column], 0, 1, 'utf-8') != 0)
						{
							// add a zero to anything missing one
							$data[$column] = '0' . $data[$column];
						}
						break;
					}

					case 'mobile':
					{
						$data[$column] = $Store->decryptMobile($row[$column]);
						if(strlen($data[$column]) && mb_substr($data[$column], 0, 1, 'utf-8') != 0)
						{
							// add a zero to anything missing one
							$data[$column] = '0' . $data[$column];
						}
						break;
					}

					case 'address_line_1':
					{
						$data[$column] = $Store->decryptAddress1($row[$column]);
						break;
					}

					default:
					{
						$data[$column] = $row[$column];
					}
				}
			}

			fputcsv($csv, $data);
		}

		fclose($csv);
		exit;

	}


}

?>