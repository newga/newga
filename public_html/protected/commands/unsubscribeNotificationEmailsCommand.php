<?php

/*
 * Runs regularly to notify organisations of their invite unsubscribes and include a link to download them
 *
 */


// required by Yii
date_default_timezone_set("Europe/London");


class unsubscribeNotificationEmailsCommand extends CConsoleCommand
{


	private $runEveryXDays = 1;
	private $emailSubject = 'Your unsubscribe notifications';

	private $recentUnsubscribeResults;
	private $totalUnsubscribeResults;


	public function run($args)
	{
		if(!(int)$this->runEveryXDays || (int)$this->runEveryXDays > 7){
			throw new Exception('Must be run every 1 -> 7 days');
		}

		$runEveryUnix = (int)$this->runEveryXDays * 24 * 60 * 60;


		// run a query to get each organisation with the unsubscribe total for the last week and ever
		$organisations = Yii::app()->db->createCommand("

			SELECT o.id, o.title, o.email_address

			FROM organisation o

			WHERE o.id != 10 -- ten is the application organisation

		")->queryAll();


		// get a count of recent unsubscribes
		$this->recentUnsubscribeResults = Yii::app()->db->createCommand("

			SELECT origin_organisation_id as id, COUNT(DISTINCT email) as total

			FROM (
			
				SELECT s.email, s.id, s.origin_organisation_id
				FROM suppression_list sl, store s, organisation o
				WHERE sl.store_id IS NOT NULL
					AND s.id = sl.store_id
					AND s.origin_organisation_id != 10
					AND o.id = s.origin_organisation_id
					AND s.email IS NOT NULL
					-- INVITES DO HAVE CAMPAIGN_IDs IN THE SUPPRESSION TABLE >> AND sl.campaign_id IS NULL 
					AND sl.type = 1
					AND sl.`date` >= '" . date("Y-m-d H:i:s", time() - $runEveryUnix) . "'
			)
			AS t

			GROUP BY origin_organisation_id

		")->queryAll();


		// get a count of all unsubscribes
		$this->totalUnsubscribeResults = Yii::app()->db->createCommand("

			SELECT origin_organisation_id as id, COUNT(DISTINCT email) as total

			FROM (
			
				SELECT s.email, s.id, s.origin_organisation_id
				FROM suppression_list sl, store s, organisation o
				WHERE sl.store_id IS NOT NULL
					AND s.id = sl.store_id
					AND s.origin_organisation_id != 10
					AND o.id = s.origin_organisation_id
					AND s.email IS NOT NULL
					-- INVITES DO HAVE CAMPAIGN_IDs IN THE SUPPRESSION TABLE >> AND sl.campaign_id IS NULL
					AND sl.type = 1
					AND sl.`date` > '2015-02-13 16:34:00' -- when the auto suppressions appear to stop and sending bounces start in the live database.
			)
			AS t

			GROUP BY origin_organisation_id

		")->queryAll();


		$basebody = "
Hi,

[[organisation_title]] has had [[recent_unsubscribes]] unsubscribes in the last " . $this->runEveryXDays . " day" . ($this->runEveryXDays == 1 ? "" : "s") . " and [[total_unsubscribes]] unsubscribes in total.

You can download a file containing all unsubscribes with timestamps by logging into Application Name:

[[link_to]]

Thank you,

" . Yii::app()->name . "

";



		$configs = Yii::app()->db->createCommand("SELECT * FROM `config` WHERE `key` IN ('host', 'https')")->queryAll();
		if(sizeof($configs) < 2){
			throw new CException("\n\n\n===\nDomain or HTTPS config not yet set. View any admin area page in a browser to remedy this.\n===\n\n");
		}

		foreach($configs as $config){
			$params[$config['key']] = $config['value'];
		}

		foreach($organisations as $organisation){

			if(!strlen($organisation['email_address'])) continue; // skip

			$to = $organisation['email_address'];

			if(ENVIRONMENT !== 'PRODUCTION'){
				$to = Yii::app()->params['adminEmail'];
			}

			$sendbody = preg_replace(array(

				"@\[\[organisation_title\]\]@",
				"@\[\[recent_unsubscribes\]\]@",
				"@\[\[total_unsubscribes\]\]@",
				"@\[\[link_to\]\]@",

			), array(

				$organisation['title'],
				$this->getRecentUnsubscribesForOrganisation($organisation['id']),
				$this->getTotalUnsubscribesForOrganisation($organisation['id']),
				'http' . ((int)$params['https'] === 1 ? 's' : '') . '://' . $params['host'] . '/unsubscribes',

			), $basebody);

			//exit('to: ' . $to . ' - subject: ' . $this->emailSubject . ' - body length: ' . strlen($sendbody));
			
			$mailgunApi = new MailgunApi(Yii::app()->params['insiderEmailDomain'], Yii::app()->params['mailgun']['key']);
			$message = $mailgunApi->newMessage();
			
			$message->setFrom(Yii::app()->params['fromEmail'], Yii::app()->name);
			$message->addTo($to);
			$message->setSubject($this->emailSubject);
			
			$message->setText($sendbody);
			
			$message->addTag('Unsubscribes');
			
			$message->send();
		}



	}



	private function getRecentUnsubscribesForOrganisation($id){
		foreach($this->recentUnsubscribeResults as $result){
			if((int)$result['id'] === (int)$id){
				return (int)$result['total'];
			}
		}

		return 0;
	}


	private function getTotalUnsubscribesForOrganisation($id){
		foreach($this->totalUnsubscribeResults as $result){
			if((int)$result['id'] === (int)$id){
				return (int)$result['total'];
			}
		}

		return 0;
	}


}

?>