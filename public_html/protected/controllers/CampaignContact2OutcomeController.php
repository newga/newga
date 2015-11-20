<?php

class CampaignContact2OutcomeController extends Controller
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
				'actions'=>array('recordAndForward'),
				'users' => array('*'),
			),

			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}


	public function actionRecordAndForward()
	{

		$CampaignContact2Outcome = CampaignContact2Outcome::model()->find(array(
			'with' => array(
				'campaign_outcome',
				'campaign_contact' => array(
					'with' => 'campaign',
				),
			),
			'condition' => "
				`t`.`id` = :campaign_contact2outcome_id
				AND `t`.`hash` = :campaign_contact2outcome_hash
				AND `campaign`.`id` = :campaign_id
				AND `campaign`.`status` != :campaign_not_run
				AND `campaign`.`hash` = :campaign_hash
				AND LENGTH(campaign_outcome.url) > 0
			",
			'params' => array(
				'campaign_contact2outcome_id' => $_GET['campaign_contact2outcome_id'],
				'campaign_contact2outcome_hash' => $_GET['campaign_contact2outcome_hash'],
				'campaign_id' => $_GET['campaign_id'],
				'campaign_not_run' => Campaign::STATUS_NOT_RUN,
				'campaign_hash' => $_GET['campaign_hash'],
			)
		));

		if(is_null($CampaignContact2Outcome))
		{
			// fail. we don't know where to send them. What do we do?
			throw new CHttpException('404', 'We can\'t find that page');
		}
		else
		{
			// success. Mark as success with a date not an int.
			$CampaignContact2Outcome->outcome = date("Y-m-d H:i:s");
			$CampaignContact2Outcome->save(false, array('outcome'));

			// now forward them
			$this->redirect($CampaignContact2Outcome->campaign_outcome->url);
		}



	}

}