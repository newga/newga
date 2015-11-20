<?php

class InviteController extends Controller
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
				'actions'=>array('batchTest','send'),
				'expression' => 'Yii::app()->user->role >= ' . User::ROLE_SUPERADMIN,
			),

			array('allow',
				'actions'=>array('view', 'index', 'recipients','rules','intendedRecipients', 'edit'),
				'expression' => 'Yii::app()->user->role >= ' . User::ROLE_MANAGER,
			),

			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex()
	{
		$this->pageTitle = 'Invites | ' . Yii::app()->name;

		$Campaign = new Campaign('search');
		$Campaign->unsetAttributes();  // clear any default values

		//for grid search
		if(isset($_GET['Campaign'])) {
			$Campaign->attributes=$_GET['Campaign'];
		}


		$this->pageTitle = 'Invites | ' . Yii::app()->name;
		$this->breadcrumbs=array(
			'Invites'
		);

		$this->render('index',array(
			'Campaign' => $Campaign,
		));
	}


	public function actionView()
	{
		if((int)$_GET['campaign_id']){
			// existing sent invite
			$Campaign = Campaign::model()->findByPk($_GET['campaign_id']);
			$Query = $Campaign->query;
		}
		else
		{
			throw new CHttpException(404, 'Page not found.');
		}

		$this->breadcrumbs=array(
			'Invites' => array('index'),
			$Campaign->name
		);

		$this->pageTitle = $Campaign->name . ' | Invites | ' . Yii::app()->name;

		$Invites = Invite::model()->findAll(array(
			'condition' => 'query_id = :query_id',
			'params' => array(
				':query_id' => $Query->id,
			),

		));

		// Get an array of all

		$clickThroughCount = Invite::model()->count(array(
			'condition' => 'query_id = :query_id AND accession_hash IS NOT NULL AND a.accession_hash = hash',
			'join' => 'LEFT JOIN accession a ON contact_warehouse_id = a.warehouse_id',
			'params' => array(
				':query_id' => $Query->id,
			),
		));

		$joinedCount = Invite::model()->count(array(
			'condition' => 'query_id = :query_id AND terms_agreed IS NOT NULL',
			'join' => 'INNER JOIN accession a ON contact_warehouse_id = a.warehouse_id',
			'params' => array(
				':query_id' => $Query->id,
			),
		));

		$this->render('view', array(
			'Campaign' => $Campaign,
			'Query' => $Query,
			'Invites' => $Invites,
			'invitesCount' => count($Invites),
			'clickThroughCount' => $clickThroughCount,
			'joinedCount' => $joinedCount,
		));
	}

	public function actionRecipients()
	{
		if((int)$_GET['campaign_id']){
			// existing sent invite
			$Campaign = Campaign::model()->findByPk($_GET['campaign_id']);
			$Query = $Campaign->query;
		}
		else
		{
			throw new CHttpException(404, 'Page not found.');
		}

		$Invites = Invite::model()->findAll(array(
			'condition' => 'query_id = :query_id',
			'params' => array(
				':query_id' => $Query->id,
			),
			'with' => array('store2contact', 'store2contact.store'),
		));

		$this->breadcrumbs=array(
			'Invites' => array('index'),
			$Campaign->name
		);

		$this->pageTitle = $Campaign->name . ' | Invites | ' . Yii::app()->name;

		// Loop each invite and separate into organisations
		$Organisations = Organisation::model()->findAll(array('condition' => 'id != 10'));
		$organisationsArray = array();

		foreach($Organisations as $Organisation)
		{
			$organisationsArray[$Organisation->id]['title'] = $Organisation->title;
			$organisationsArray[$Organisation->id]['invites'] = array();
		}

		foreach($Invites as $Invite)
		{
			$organisationsArray[$Invite->organisation_id]['invites'][] = $Invite;
		}

		$this->render('recipients', array(
			'Campaign' => $Campaign,
			'organisationsArray' => $organisationsArray,
		));
	}

	public function actionRules()
	{
		if((int)$_GET['campaign_id']){
			// existing sent invite
			$Campaign = Campaign::model()->findByPk($_GET['campaign_id']);
			$Query = $Campaign->query;
		}
		else
		{
			throw new CHttpException(404, 'Page not found.');
		}

		$this->breadcrumbs=array(
			'Invites' => array('index'),
			$Campaign->name
		);

		$this->pageTitle = $Campaign->name . ' | Invites | ' . Yii::app()->name;

		$this->render('rules', array(
			'Campaign' => $Campaign,
		));
	}

	// Shows a list of recipient email addresses based on the results of the invite query
	public function actionIntendedRecipients()
	{
		if((int)$_GET['campaign_id']){
			// existing sent invite
			$Campaign = Campaign::model()->findByPk($_GET['campaign_id']);

			if(is_null($Campaign))
			{
				throw new CHttpException(404, 'Not found');
			}

		}
		else
		{
			throw new CHttpException(404, 'Page not found.');
		}

		$this->breadcrumbs=array(
			'Invites' => array('index'),
			$Campaign->name => array('send', 'campaign_id' => $Campaign->id),
			'Recipients'
		);

		$results = Invite::model()->with("store")->findAll(array(
			'condition' => 'campaign_id = :campaign_id',
			'params' => array(
				':campaign_id' => $Campaign->id,
			),
		));

		$this->render('intendedRecipients', array(
			'results' => $results,
			'Campaign' => $Campaign,
		));
	}

	public function actionEdit()
	{
		if((int)$_GET['campaign_id']){
			// existing sent invite
			$Campaign = Campaign::model()->findByPk($_GET['campaign_id']);

			if(is_null($Campaign))
			{
				throw new CHttpException(404, 'Not found');
			}

			$Query = $Campaign->query;
		}
		else
		{
			throw new CHttpException(404, 'Page not found.');
		}

		if((int)$Campaign->status !== Campaign::STATUS_NOT_RUN)
		{
			// Redirect to view
			$this->redirect(array('invite/view', 'campaign_id' => $Campaign->id));

			exit();
		}

		if(isset($_POST['delete']))
		{
			// Delete all invite rows which match this campaign
			$InviteRows = Invite::model()->findAll(array(
				'condition' => 'campaign_id = :cid AND status = :status',
				'params' => array(
					':cid' => $Campaign->id,
					':status' => Invite::STATUS_UNSENT,
				),
			));

			foreach($InviteRows as $InviteRow)
			{
				$InviteRow->delete();
			}

			// Now delete the query and campaign
			$Campaign->query->delete();
			$Campaign->delete();

			$this->redirect(array('invite/index'));
		}



		// Set scenario for validation
		$Campaign->scenario = 'inviteEdit';

		if(isset($_POST['Campaign']))
		{
			// Mass assign the form values to the Campaign Object.
			$Campaign->attributes = $_POST['Campaign'];

			if($Campaign->save())
			{
				Yii::app()->user->setFlash('success', 'Invite ' . $Campaign->name . ' email content saved');
				$this->refresh();
			}

		}


		$this->breadcrumbs=array(
			'Invites' => array('index'),
			$Campaign->name,
		);


		$this->render('invite', array(
			'Campaign' => $Campaign,
			'Query' => $Query,
			'results' => $results,
		));

	}


	/**
	 * /invites/:id
	 * Allows user to add email content and send invitation emails to contacts
	 */
	public function actionSend()
	{
		if((int)$_GET['campaign_id']){
			// existing sent invite
			$Campaign = Campaign::model()->findByPk($_GET['campaign_id']);

			if(is_null($Campaign))
			{
				throw new CHttpException(404, 'Not found');
			}

			$Query = $Campaign->query;
		}
		else
		{
			throw new CHttpException(404, 'Page not found.');
		}

		if((int)$Campaign->status !== Campaign::STATUS_NOT_RUN)
		{
			// Redirect to view
			$this->redirect(array('invite/view', 'campaign_id' => $Campaign->id));

			exit();
		}

		// Set scenario for validation
		$Campaign->scenario = 'inviteSend';

		if(isset($_POST['Campaign']))
		{
			if($Campaign->validate())
			{
				// Are we testing, or sending for real?
				if(isset($_POST['test']))
				{
					if(!strlen($_POST['Campaign']['email_test_recipient']))
					{
						$Campaign->addError('email_test_recipient', 'Test email recipient cannot be blank');
					}
					else
					{
						$InviteEmail = new InviteEmail;
						$InviteEmail->sendTest($_POST['Campaign']['email_test_recipient'], $Campaign->invite_email_subject, $Campaign->invite_email_body);

						$this->refresh();
					}

				}
				elseif(!in_array($Campaign->status, array(Campaign::STATUS_QUEUED, Campaign::STATUS_HAS_BEEN_RUN)))
				{
					// Set Campaign as queued for sending
					$Campaign->status = Campaign::STATUS_QUEUED;
					$Campaign->save(true, array('status'));

					// Set a flash message
					Yii::app()->user->setFlash('success', 'The invite has been queued for sending');

					$this->redirect(array('invite/index'));
				}

			}

		}


		$this->breadcrumbs=array(
			'Invites' => array('index'),
			$Campaign->name => array('edit', 'campaign_id' => $Campaign->id),
			'Sending Options'
		);


		$this->render('send', array(
			'Campaign' => $Campaign,
			'Query' => $Query,
			'results' => $results,
		));


	}

	public function getOrganisationEmailTemplate($Organisation)
	{
		$template = $Organisation->email_template;

		return $template;
	}


}

?>