<?php

class InviteEmail
{

	public function send($Campaign)
	{
		//exit();
		
		$configs = Yii::app()->db->createCommand("SELECT * FROM `config` WHERE `key` IN ('host', 'https')")->queryAll();
		
		if(sizeof($configs) < 2){
			throw new CException("\n\n\n===\nDomain or HTTPS config not yet set. View any admin area page in a browser to remedy this.\n===\n\n");
		}

		foreach($configs as $config){
			$params[$config['key']] = $config['value'];
		}


		// Load contacts for this invite from the invite table
		// They'll be marked with this campaign ID, and status=0 - unsent
		$Invite = Invite::model();
		$transaction = $Invite->dbConnection->beginTransaction();

		try
		{

			$InviteRows = $Invite->with("store")->findAll(array(
	         	"condition" => "campaign_id = :campaign_id AND processing = 0 AND status = :status",
	         	"params" => array(":campaign_id" => $Campaign->id, ":status" => Invite::STATUS_UNSENT)
	     	));

	   		$inviteIDs = [];

			foreach($InviteRows as $Invite)
			{
				$inviteIDs[] = $Invite->id;
			}

			$command = Yii::app()->db->createCommand();
			$command->update('invite', array("processing" => 1), array('in', 'id', $inviteIDs));
			$transaction->commit();

		}
		catch(Exception $e)
		{
		    $transaction->rollback();
		    throw $e;
		}


		set_time_limit(0);

		// Load all organisations, we'll need their info
		$Organisations = Organisation::model()->findAll(array(
			'condition' => 'id IN (1,2,3,4,5,6,7,8,9)',
			'index' => 'id',
		));

		// Group by organisation
		$orgContacts = array();
		foreach($InviteRows as $Invite)
		{
			$orgContacts[$Invite->organisation_id][] = $Invite;
		}


		// Chunk these into arrays of up to 1000 contacts per organisation - this is the Mailgun limit
		$chunkedOrgContacts = array();
		foreach($orgContacts as $orgID => $contactArray)
		{
			$chunkedOrgContacts[$orgID] = array_chunk($orgContacts[$orgID],1000);
		}

		// Parse the email content, replace tags with the Mailgun recipient params
		$parsedContent = $this->parseEmailContent($Campaign->invite_email_subject, $Campaign->invite_email_body, array(
			'first_name' => '%recipient.first_name%',
			'last_name' => '%recipient.last_name%',
			'invite_url' => '%recipient.invite_url%',
			'unsubscribe_url' => '%recipient.unsubscribe_url%',
		));

		// Debug / counter vars
		$sentCount = 0;
		$campaignSuccess = false;
		$toEmails = [];

		Yii::import('application.extensions.*');

		foreach($chunkedOrgContacts as $orgID => $orgChunks)
		{



			// Only set real email info if we're on production server
			// Let's not send emails to everyone in Newcastle before we're live...
			if(ENVIRONMENT === 'PRODUCTION' || ENVIRONMENT === 'PHOENIX')
			{
				// Get the correct email domain for the organisation in question
				$fromEmailDomain = $Organisations[$orgID]->email_domain;
				$fromEmailAddress = $Organisations[$orgID]->email_address;
				$fromEmailName = $Organisations[$orgID]->title;
			}
			else
			{
				$fromEmailDomain = Yii::app()->params['insiderEmailDomain'];
				$fromEmailAddress = 'email@' . $fromEmailDomain;
				$fromEmailName = $Organisations[$orgID]->title;
			}

			$mailgunApi = new MailgunApi($fromEmailDomain, Yii::app()->params['mailgun']['key']);
			$message = $mailgunApi->newMessage();
			$message->setFrom($fromEmailAddress, $fromEmailName);
			$message->setSubject($parsedContent['subject']);
			$message->addTag('Invite'); // Tag with invite so it can be filtered in Mailgun admin
			
			if(ENVIRONMENT !== 'PRODUCTION')
			{
				$message->addTag('Test');
			}


/* TEST MODE */
//$message->enableTestMode();



			
			// Now loop the contacts, which are in chunks (arrays) of up to 1000
			foreach($orgChunks as $upTo1000Invites)
			{
				//start transaction?
				try {
					$transaction = $Invite->dbConnection->beginTransaction();
					$invite1000IDs = [];

					//reset the to fields
					$message->resetTo();

					// Add all the contacts in this chunk, there could be up to 1000 of 'em
					foreach($upTo1000Invites as $Invite)
					{

						$invite1000IDs[] = $Invite->id;

						if(strlen($Invite->store->email))
						{
							$Store2Contact = Store2Contact::model()->findByPk($Invite->store2contact_id);

							if(!is_null($Store2Contact))
							{
								// Check values match
								if($Store2Contact->contact_warehouse_id === $Invite->contact_warehouse_id && $Store2Contact->store_id === $Invite->store_id)
								{
									// Also save the date they were invited to Store2Contact so we can query it easily
									$Store2Contact->most_recent_invite_date = date('Y-m-d H:i:s');
									$Store2Contact->save(true, array('most_recent_invite_date'));
									unset($Store2Contact);

									// Now create the invitation link to pass into the email template
									$inviteURL = ($params['https'] === 1 ? 'https://' : 'http://') . $params['host'] . '/accession/invite/' . $Invite->hash;


									// Only set real email info if we're on production server
									// Let's not send emails to everyone in Newcastle before we're live...
									if(ENVIRONMENT === 'PRODUCTION')
									{
										$toEmail = $Invite->store->email;
									}
									else
									{
										$toEmail = 'email@example.com';
									}
									
									$unsubscribeUrl = $Invite->unsubscribeUrl;
									
									// Check first name
									$firstName = $Invite->store->first_name;
									
									if(!strlen(trim($firstName)))
									{
										$firstName = 'friend';
									}
									
									// Add the recipient to the message object, including the params unique for them
									$message->addTo($toEmail, $Invite->store->first_name . ' ' . $Invite->store->last_name, array(
										'first_name' => $firstName,
										'last_name' => $Invite->store->last_name,
										'invite_url' => $inviteURL,
										'unsubscribe_url' => $unsubscribeUrl,
									));

									//$toEmails[] = 'Email: ' . $toEmail . " - " . $contact['first_name'] . ' ' . $lastName . '('.$fromEmailName.')' . "\n\n";

									// Increment the counter
									$sentCount++;

								}
								else
								{
									$contactInfo = print_r($contact, true);
									mail('email@example.com', 'Invite attempted but store2contact row did not match contact found in query', $contactInfo);
								}
							}
							else
							{
								$contactInfo = print_r($contact, true);
								mail('email@example.com', 'Invite attempted but store2contact could not be found', $contactInfo);
							}
						}
						else // Contact has no email address - shouldn't have been picked up by the query
						{
							$contactInfo = print_r($contact, true);
							mail('email@example.com', 'Invite attempted with no email address', $contactInfo);
						}
					} // foreach upTo1000Invites
				}
				catch(Exception $e)
				{
					$transaction->rollback();
					$campaignSuccess = false;
				}



				// Now we have the data for our 1000 contacts set up, render the template for the organisation
				$Controller = new Controller('foo');
				
				$renderedView = $Controller->renderInternal(Yii::app()->basePath . '/views/mail/organisation-templates/' . $Organisations[$orgID]->email_template . ".php", array(
					'body' => $parsedContent['body']
				), true);

				$message->setHtml($renderedView);
				
				// SEND THE MESSAGE
				if(sizeof($message->getTo()))
				{
					try
					{
						$response = $message->send();

						$command = Yii::app()->db->createCommand();
						$command->update('invite', array("status" => Invite::STATUS_SENT, "processing" => 0), array('in', 'id', $invite1000IDs));

						$transaction->commit();

						$campaignSuccess = true;
					}
					catch(Exception $e)
					{

						print $e->getMessage();
						// mailgun returned an error - we rollback the transaction and remove the number of failed ids from the sent count
						// consider mainatining a fail count??

						$sentCount -= count($invite1000IDs);

						$command = Yii::app()->db->createCommand();
						$command->update('invite', array("status" => Invite::STATUS_MAILGUN_ERROR, "processing" => 0), array('in', 'id', $invite1000IDs));

						$transaction->commit();

						$msg = print_r($e, true);
						$msg .= print_r($message, true);

						mail('email@example.com', 'Mailgun error', $msg);

					}

				}


			} // end of org 1000 chunk


		} // foreach organisations

		if($campaignSuccess)
		{
			$Campaign->status = Campaign::STATUS_HAS_BEEN_RUN;
		}
		else
		{
			$Campaign->status = Campaign::STATUS_ERROR_SEND;
		}

		$Campaign->save(true, array("status"));

	}



	public function sendTest($toEmail, $subject, $body){

		// In test mode, we should send 9 emails - one from each organisation
		// The subject and body content should be as it is in the form, except we won't have a full URL, so we can use an example

		// Parse content including tags
		$parsedContent = $this->parseEmailContent($subject, $body, array(
			'first_name' => '%recipient.first_name%',
			'last_name' => '%recipient.last_name%',
			'invite_url' => '%recipient.invite_url%',
			'unsubscribe_url' => '%recipient.unsubscribe_url%',
		));

		$responses = array();


		// Let's load all the organisations, we'll need them later
		$Organisations = Organisation::model()->findAll(array(
			'condition' => 'id != :id',
			'params' => array(':id' => 10),
			'index' => 'id',
		));


		$mailgunApi = new MailgunApi(Yii::app()->params['insiderEmailDomain'], Yii::app()->params['mailgun']['key']);

		// Loop the organisations and send an email for each one
		foreach($Organisations as $Organisation)
		{
			if(strlen($Organisation->email_template))
			{
				$message = $mailgunApi->newMessage();
				$message->setFrom('email@' . Yii::app()->params['insiderEmailDomain'], Yii::app()->name);

				$message->addTo($toEmail, Yii::app()->user->first_name . ' ' . Yii::app()->user->last_name, array(
					'first_name' => Yii::app()->user->first_name,
					'last_name' => Yii::app()->user->last_name,
					'invite_url' => 'http://example.com/#-invite-url-',
					'unsubscribe_url' => 'http://example.com/#-unsubscribe-url-',
				));

				$message->setSubject($parsedContent['subject']);
				$message->addTag('test');

				$renderedView = Yii::app()->controller->renderPartial('//mail/organisation-templates/' . $Organisation->email_template, array(
					'body' => $parsedContent['body'],

				), true);

				$message->setHtml($renderedView);

				$response = $message->send();

				if($response['id'])
				{
					$responses[] = $response['id'];
				}
				else
				{
					$errors[] = $response;
				}
			}
			else
			{

			}
		}



		if(!sizeof($errors))
		{
			Yii::app()->user->setFlash('success', sizeof($responses) . ' test emails sent. Check the inbox of ' . $_POST['Campaign']['email_test_recipient']);
		}
		else
		{
			Yii::app()->user->setFlash('error', 'There was an error sending some or all of the emails. ' . sizeof($responses) . ' test were emails sent. '.$errors.' were unset due to errors. Check the inbox of ' . $_POST['Campaign']['email_test_recipient']);
		}

		//$this->refresh();

	}



	public function parseEmailContent($subject, $body, $replacementArray)
	{
		// build the email subject and body with template data
		foreach(array(

			'firstname' => array('requiredInBody' => false, 'subject' => true, 'body' => true, 'attribute' => 'first_name'),
			'lastname' => array('requiredInBody' => false, 'subject' => true, 'body' => true, 'attribute' => 'last_name'),


		) as $key => $replacement){

			// what to replace with?
			//$input = is_null($replacement['attribute']) ? '' : $User->{$replacement['attribute']};

			if($replacement['subject']){
				$subject = preg_replace("@\[\[" . $key . "\]\]@", $replacementArray[$replacement['attribute']], $subject, -1, $count);
			}
			if($replacement['body']){
				$body = preg_replace("@\[\[" . $key . "\]\]@", $replacementArray[$replacement['attribute']], $body, -1, $count);
			}
		}


		// Replace invite link
		$body = preg_replace("@\[\[invitelink\]\]@", '<a href="'.$replacementArray['invite_url'].'">'.$replacementArray['invite_url'].'</a>', $body);

		return array(
			'subject' => $subject,
			'body' => nl2br($body),
		);
	}
}

?>