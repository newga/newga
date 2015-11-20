<?php

class CampaignGroupController extends Controller
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
				'actions'=>array('update', 'export'),
				'expression' => 'Yii::app()->user->role >= ' . User::ROLE_ORGANISATION,
			),

			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}
	


	/**
	 * Update a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id)
	{

		$data = array();
		$CampaignGroup = $this->loadModel($id);

		if(sizeof($_POST['CampaignGroup'])){

			$CampaignGroup->attributes = $_POST['CampaignGroup'];
			$CampaignGroup->save();
			
			$this->refresh();
		}


		$this->pageTitle = 'Update Campaign Group | ' . Yii::app()->name;

		$this->breadcrumbs=array(
			'Campaigns' => array('campaign/index'),
			$CampaignGroup->campaign->name => array('campaign/createUpdate', 'id' => $CampaignGroup->campaign_id),
			'Update Campaign Group',
		);

		$EmailTemplate = new EmailTemplate;

		$this->render('update',array(
			'Campaign' => $CampaignGroup->campaign,
			'CampaignGroup'=>$CampaignGroup,
			'EmailTemplate'=>$EmailTemplate
		));
	}





	public function actionExport($id){

		$Campaign = Campaign::model()->findByPk($_GET['campaign_id']);

		if(!$Campaign->hasBeenRun){
			throw new CHttpException('404', 'Not found');
		}

		// check campaign has been run
		if(!$Campaign->hasBeenRun){
			throw new CHttpException('403', 'Forbidden'); // not possible. Forbidden.
		}


		$CampaignGroup = CampaignGroup::model()->findByAttributes(array(
			'id' => $id,
			'campaign_id' => $Campaign->id,
		));

		if(is_null($CampaignGroup)){
			throw new CHttpException('404', 'Not found');
		}



		$Criteria = new CDbCriteria;
		$Criteria->compare('`t`.`campaign_id`', $Campaign->id);
		$Criteria->compare('`t`.`group_id`', $id);
		$Criteria->with = array(
		 	'contact',
		);

		$Criteria->order = '`t`.`group_id` ASC';

		$CampaignContacts = CampaignContact::model()->findAll($Criteria);


		// build the csv
		$rows = array();
		$outcomeColumns = array();

		foreach($CampaignGroup->outcomes as $Outcome)
		{
											// will be a url or NULL so fine
			$outcomeColumns[$Outcome->name] = $Outcome->url;
		}

		$headings = array(
			'contact_warehouse_id' => 'insider_id',
			'salutation' => 'Prefix',
			'first_name' => 'Forename',
			'last_name' => 'Surname',
			'email' => 'Email',
			'phone' => 'Telephone',
			'mobile' => 'Mobile',
			'dob' => 'Date of Birth',
			'address_line_1' => 'Address Line 1',
			'address_line_2' => 'Address Line 2',
			'address_line_3' => 'Address Line 3',
			'address_line_4' => 'Address Line 4',
			'address_town' => 'Town',
			'address_postcode' => 'Postcode',
			'address_county' => 'County',
			'culture_segment' => 'Culture Segment',
			'level_of_engagement' => 'Level of Engagement',
		);


		// build each row
		foreach($CampaignContacts as $CampaignContact){

			$row = array();

			// use headings to get data
			foreach($headings as $key => $value){

				$row[$value] = $CampaignContact->contact->{$key};

			}

			$rows[] = array_merge($row, array_values($outcomeColumns));

		}


		// stick headers on the start after adding outcome names to it
		array_unshift($rows, array_values(array_merge($headings, array_keys($outcomeColumns))));

		$cleanGroupName = preg_replace(array(
			"@[^a-z0-9\-\s]@i",
			"@\s+@",
			"@\-{2,}@"
		), array(
			"",
			"-",
			"-"
		), $CampaignGroup->name) . '-' . date("Y-m-d");

		$csv = fopen('php://output', 'w');
		
		header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
		header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

		header('Content-Encoding: UTF-8');
		header('Content-type: text/csv; charset=UTF-8');

		// disposition / encoding on response body
		header("Content-Disposition: attachment;filename=" . $cleanGroupName . ".csv");
		header("Content-Transfer-Encoding: binary");
		
		foreach ($rows as $column)
		{
			fputcsv($csv, $column);
		}
		
		fclose($csv);
		
		//echo $csv;
		exit;


	}





	public function loadModel($id)
	{
		$Model = CampaignGroup::model()->findByPk($id);
		if($Model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $Model;
	}	

}

?>