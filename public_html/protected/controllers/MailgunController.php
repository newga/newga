<?php

class MailgunController extends Controller
{
	public $layout = false;
	
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
				'actions'=>array('bounce', 'open'),
				'users'=>array('*'),
			),

			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}
	
	public function actionBounce()
	{
		// See http://documentation.mailgun.com/user_manual.html#webhooks

		// Mail so we know we have received the bounce webhook
		
		mail('email@example.com', 'Mailgun Bounce', print_r($_POST, true));
		
		// Set up authorisation
		$authString = $_POST['timestamp'] . $_POST['token'];
		
		$authHash = hash_hmac('sha256', $authString, Yii::app()->params['mailgun']['key']);
		
		// Check Auth
		if($authHash === $_POST['signature'])
		{
			// Huzzah! Authorized HTTP POST from Mailgun
			
			$uniques = array();
			
			$StoreModel = new Store;
			
			// Encrypt the email so we can find a match
			$bouncedEmailAddress = $StoreModel->encryptEmail($_POST['recipient']);
			
			$bouncedTimestamp = $_POST['timestamp'];
			
			// Look for this email address in store table
			$StoreRows = Store::model()->with('store2contact')->findAll(array(
				'condition' => 'email = :email',
				'params' => array(
					':email' => $bouncedEmailAddress,
				),
			));



			// collect our warehouse_ids up to match in campaign_contact table.
			$warehouseIDs = [];

			if(sizeof($StoreRows))
			{
				// Save 1 suppression row for every instance of the email address in the store table - use store_id
				foreach($StoreRows as $Store)
				{

					// expired? No store to contact. Skip
					if(is_null($Store->store2contact)) continue;

					$warehouseIDs[] = $Store->store2contact->contact_warehouse_id;

					// Check if the suppression list row already exists
					$SuppressionList = SuppressionList::model()->find(array(
						'condition' => 'warehouse_id IS NULL AND store2contact_id = :s2cid AND store_id = :sid AND type = :type',
						'params' => array(
							':s2cid' => $Store->store2contact->id,
							':sid' => $Store->id,
							':type' => SuppressionList::TYPE_BOUNCE,
						),
					));

					// If not, create it
					if(is_null($SuppressionList))
					{
						$SuppressionList = new SuppressionList;
						$SuppressionList->type = SuppressionList::TYPE_BOUNCE;
						$SuppressionList->warehouse_id = null;
						$SuppressionList->store2contact_id = $Store->store2contact->id;
						$SuppressionList->store_id = $Store->id;
						$SuppressionList->date = date('Y-m-d H:i:s', $bouncedTimestamp);
						
						if(!$SuppressionList->save())
						{
							$errors = print_r($SuppressionList->errors, true);
							
							mail('email@example.com', 'Bounce could not be saved in suppression_list: '. $errors);
						}
					}
				}


				// check for campaign_contacts.
				if(sizeof($warehouseIDs) && is_numeric($_POST['campaign_id']) && is_numeric($_POST['group_id']))
				{
					// it's a bounce of a campaign email. Mark against the row.
					CampaignContact::model()->updateAll(
						array('bounced' => date('Y-m-d H:i:s', $bouncedTimestamp)),
						"campaign_id = :campaign_id AND group_id = :group_id AND warehouse_id IN (" . implode(',', array_filter($warehouseIDs)) . ") AND bounced IS NULL",
						array(':campaign_id' => $_POST['campaign_id'], ':group_id' => $_POST['group_id'])
					);
				}

				header("HTTP/1.0 200 Ok");
				exit;

			}
			else
			{
				header("HTTP/1.0 404 Not Found");
				exit('Not Found');
			}
		}
		else
		{
			// Go away
			
			sleep(5);
			header("HTTP/1.0 401 Unauthorized");
			exit('Unauthorized');
		}
	}


	public function actionOpen()
	{
		// See http://documentation.mailgun.com/user_manual.html#webhooks
		
		// Mail so we know we have received the opened webhook
		
		// Set up authorisation
		$authString = $_POST['timestamp'] . $_POST['token'];
		
		$authHash = hash_hmac('sha256', $authString, Yii::app()->params['mailgun']['key']);
		
		// Check Auth
		if($authHash === $_POST['signature'])
		{
			// Huzzah! Authorized HTTP POST from Mailgun
			
			$uniques = array();
			
			$StoreModel = new Store;
			
			// Encrypt the email so we can find a match
			$openedEmailAddress = $StoreModel->encryptEmail($_POST['recipient']);

			// Look for this email address in store table
			$StoreRows = Store::model()->with('store2contact')->findAll(array(
				'condition' => 'email = :email',
				'params' => array(
					':email' => $openedEmailAddress,
				),
			));

			// collect our warehouse_ids up to match in campaign_contact table.
			$warehouseIDs = [];

			if(sizeof($StoreRows))
			{
				// Save 1 suppression row for every instance of the email address in the store table - use store_id
				foreach($StoreRows as $Store)
				{
					// expired? No store to contact. Skip
					if(!is_null($Store->store2contact))
					{
						$warehouseIDs[] = $Store->store2contact->contact_warehouse_id;
					}
				}

				// check for campaign_contacts.
				if(sizeof($warehouseIDs) && is_numeric($_POST['campaign_id']) && is_numeric($_POST['group_id']))
				{
					// it's a bounce of a campaign email. Mark against the row.
					CampaignContact::model()->updateAll(
						array('opened' => date('Y-m-d H:i:s', $_POST['timestamp'])),
						"campaign_id = :campaign_id AND group_id = :group_id AND warehouse_id IN (" . implode(',', array_filter($warehouseIDs)) . ") AND opened IS NULL",
						array(':campaign_id' => $_POST['campaign_id'], ':group_id' => $_POST['group_id'])
					);
				}

				header("HTTP/1.0 200 Ok");
				exit;

			}
			else
			{
				header("HTTP/1.0 404 Not Found");
				exit('Not Found');
			}
		}
		else
		{
			// Go away
			
			sleep(5);
			header("HTTP/1.0 401 Unauthorized");
			exit('Unauthorized');
		}
	}


}

?>