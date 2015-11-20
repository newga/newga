<?php

class campaignQueueCommand extends CConsoleCommand
{
	public function run($args)
	{

		$campaignSuccess = false;

		$configs = Yii::app()->db->createCommand("SELECT * FROM `config` WHERE `key` IN ('host', 'https')")->queryAll();

		if(sizeof($configs) < 2){
			throw new CException("\n\n\n===\nDomain or HTTPS config not yet set. View any admin area page in a browser to remedy this.\n===\n\n");
		}

		foreach($configs as $config){
			$configParams[$config['key']] = $config['value'];
		}


		date_default_timezone_set("Europe/London");

		print "Getting campaigns to process \n";

		$CampaignCollection = Campaign::model()->findAll(array(
			'with' => array(
				'query',
				'groups' => array(
					'with' => array(
						'email_template'
					)
				),
			),
			"condition" => "processing = 0 AND status = :status AND invite = 0",
			"params" => array(":status" => Campaign::STATUS_QUEUED)
		));

		print count($CampaignCollection) . ' campaigns to process' . "\n";

		$campaignIDs = [];

		foreach($CampaignCollection as $Campaign)
		{
			$campaignIDs[] = $Campaign->id;
			print "Will process Campaign ID: ".$Campaign->id." \n";
		}

		$command = Yii::app()->db->createCommand();
		$command->update('campaign', array("processing" => 1), array('in', 'id', $campaignIDs));

		foreach($CampaignCollection as $Campaign)
		{

			/* sending logic */

			/* create mailgun campaign id */
			$mailgunApi = new MailgunCampaign(Yii::app()->params['insiderEmailDomain'], Yii::app()->params['mailgun']['key']);

			// Check if the campaign ID exists
			try{
				$checkCampaignResponse = $mailgunApi->getCampaign(Yii::app()->params['insiderEmailDomain'], $Campaign->id);
			}
			catch(Exception $e)
			{
				$checkCampaignResponse = false;
			}

			if(!$checkCampaignResponse)
			{
				$mailgunCampaignResponse = $mailgunApi->createCampaign(Yii::app()->params['insiderEmailDomain'], array("name" => $Campaign->name , "id" => $Campaign->id ));
			}
			else
			{
				$mailgunCampaignResponse['campaign'] = $checkCampaignResponse;
			}

			/* Example Response
			Array
			(
				[message] => Campaign created
				[campaign] => Array
					(
						[clicked_count] => 0
						[opened_count] => 0
						[submitted_count] => 0
						[unsubscribed_count] => 0
						[bounced_count] => 0
						[id] => 691
						[name] => Hicks 2
						[created_at] => Tue, 24 Feb 2015 13:34:25 GMT
						[delivered_count] => 0
						[complained_count] => 0
						[dropped_count] => 0
					)

			)
			*/
			$Store = new Store;

			/* loop groups */
				/* load & render template (campaign_group) */
			foreach($Campaign->groups as $CampaignGroup)
			{

				$mailgunApi = new MailgunApi(Yii::app()->params['insiderEmailDomain'], Yii::app()->params['mailgun']['key']);
				$mailgunApi->enableTracking();
				$mailgunApi->enableOpensTracking();
				$mailgunApi->enableClicksTracking();
				
				$message = $mailgunApi->newMessage();
				$message->setFrom(Yii::app()->params['fromEmail'], Yii::app()->name);
				$message->setSubject($CampaignGroup->subject);
				$message->setCampaignId($mailgunCampaignResponse['campaign']['id']);
				$message->setHtml($CampaignGroup->email_template->parsedHtml($configParams));

				// send variables to mailgun so that bounces and opens are easier to deal with.
				$message->addVar('campaign_id', $Campaign->id);
				$message->addVar('group_id', $CampaignGroup->id);
				
				//get contacts
				$CampaignContactCollection = CampaignContact::model()->findAll(array(
					'with' => array(
						// 'contact', This is now done separately below for speeeeeed
						'contact2outcomes' => array(
							'index' => 'campaign_outcome_id'
						),
					),
					"condition" => "
						group_id = :groupid
						AND `t`.`status` = 0
					",
					"params" => array(
						":groupid" => $CampaignGroup->id
					),
					'index' => 'warehouse_id' // same id can't be in twice so this is safe to speed up the contact query
				));

				echo sizeof($CampaignContactCollection);

				// get the contact rows for the above campaigncontact rows
				$Criteria = new CDbCriteria;
				$Criteria->index = 'contact_warehouse_id';
				$Contacts = CleanWarehouse::model()->findAllByPk(array_keys($CampaignContactCollection), $Criteria);

				$chunkedCampaignContacts = array_chunk($CampaignContactCollection, 1000, true);

				foreach($chunkedCampaignContacts as $campaign1000)
				{

					//echo 'C';
					$transaction = $Campaign->dbConnection->beginTransaction();
					$campaign1000IDs = array();
					$sentCount = 0;
					$message->resetTo();

					foreach($campaign1000 as $warehouse_id => $CampaignContact)
					{
						//echo '.';
						$campaign1000IDs[] = $CampaignContact->id;
						$thisContact = $Contacts[$warehouse_id];

						$standardTags = array(
							'first_name' => $thisContact->first_name,
							'last_name' => $Store->decryptLastName($thisContact->last_name),
							'email' => $Store->decryptEmail($thisContact->email),
							'insider_unsubscribe' => 'http' . ($configParams['https'] ? 's' : '') . '://' . $configParams['host'] . Yii::app()->urlManager->createUrl('data/campaignUnsubscribe', array('campaign_id' => $Campaign->id, 'campaign_hash' => $Campaign->hash, 'campaign_contact_id' => $CampaignContact->id, 'campaign_contact_hash' => $CampaignContact->hash)),
							'warehouse_id' => $warehouse_id
						);
						
						$campaignTags = $CampaignGroup->email_template->returnOutcomeTagsToUrls($configParams, $Campaign, $CampaignContact->contact2outcomes);
						
						$parsedTagsArray = array_merge($standardTags, $campaignTags);

						if(ENVIRONMENT === 'PRODUCTION')
						{
							$message->addTo($Store->decryptEmail($thisContact->email), $thisContact->first_name . ' ' . $Store->decryptLastName($thisContact->last_name), $parsedTagsArray);
						}
						else
						{
							$message->addTo("email@example.com", $thisContact->first_name . ' ' . $Store->decryptLastName($thisContact->last_name), $parsedTagsArray);
						}

						$sentCount++;
					}
					
					/* send email to mailgun */
					try
					{
						$response = $message->send();

						$command = Yii::app()->db->createCommand();
						$command->update('campaign_contact', array("status" => CampaignContact::STATUS_SENT, "processing" => 0), array('in', 'id', $campaign1000IDs));

						$transaction->commit();

						$campaignSuccess = true;
						echo 'Success';
					}
					catch(Exception $e)
					{

						//print $e->getMessage();
						// mailgun returned an error - we rollback the transaction and remove the number of failed ids from the sent count
						// consider mainatining a fail count??

						$sentCount -= count($campaign1000);

						$command = Yii::app()->db->createCommand();
						$command->update('campaign_contact', array("status" => CampaignContact::STATUS_MAILGUN_ERROR, "processing" => 0), array('in', 'id', $campaign1000IDs));

						$transaction->commit();

						$msg = print_r($e, true);
						$msg .= print_r($message, true);

						mail('email@example.com', 'Mailgun error', $msg);

					}

				}



			}

			if($campaignSuccess)
			{
				$Campaign->status = Campaign::STATUS_HAS_BEEN_RUN;
			}
			else
			{
				$Campaign->status = Campaign::STATUS_ERROR_SEND;
			}

			$Campaign->processing = 0;
			$Campaign->save(true, array("processing", "status"));

			print "Completed processing ".$Campaign->id." \n";
			$Campaign->refresh();
			print "Status of campaign was ".$Campaign->getStatusText() . "\n\n";
		}

	}
}

?>