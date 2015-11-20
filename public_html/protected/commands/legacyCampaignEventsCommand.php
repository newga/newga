<?php

class legacyCampaignEventsCommand extends CConsoleCommand
{
	public function run($args)
	{
		exit();
		//already ran, don't do this again!

		$capaignIDs = array(10003,735,734,733,732,731,730,729,728,727);

		$mailgunApi = new MailgunCampaign(Yii::app()->params['insiderEmailDomain'], Yii::app()->params['mailgun']['key']);


		foreach ($capaignIDs as $campaignID) {
			
			
			echo 'Fetching BOUNCED events for campaign ' . $campaignID . "\r\n";
			$page = 1;
			
			while ($data = $mailgunApi->getBouncesByCampaign('example.com', $campaignID, $page)) {
				
				foreach ($data as $bounce) {
				
					$this->addEvent('bounce', $campaignID, $bounce['recipient'], date("Y-m-d H:i:s", strtotime($bounce['timestamp'])));
					
				}
				echo 'Sleeping...';
				sleep(0.5);
				$page ++;
			}

			echo 'Fetching OPEN events for campaign ' . $campaignID . "\r\n";
			$page = 1;
			
			while ($data = $mailgunApi->getOpensByCampaign('example.com', $campaignID, $page)) {
				
				foreach ($data as $open) {
				
					$this->addEvent('open', $campaignID, $open['recipient'], date("Y-m-d H:i:s", strtotime($open['timestamp'])));
					
				}
				echo 'Sleeping...';
				sleep(0.5);
				$page ++;
			}


		}

	}

	private function addEvent($type, $id, $email, $datetime) {
		echo 'Adding ' . strtoupper($type) . ' to campaign ' . $id . ' with date of ' . $datetime . ' and email of ' . $email . "\r\n";

		$StoreModel = new Store;

		$encryptedEmail = $StoreModel->encryptEmail($email);

		// Look for this email address in store table
		$StoreRows = Store::model()->with('store2contact')->findAll(array(
			'condition' => 'email = :email',
			'params' => array(
				':email' => $encryptedEmail,
			),
		));

		// collect our warehouse_ids up to match in campaign_contact table.
		$warehouseIDs = [];

		if(sizeof($StoreRows))
		{
			// Save 1 suppression row for every instance of the email address in the store table - use store_id
			foreach($StoreRows as $Store)
			{
				if ($Store->store2contact != null) {
					$warehouseIDs[] = $Store->store2contact->contact_warehouse_id;
				}
				
			}

			$Contacts = null;

			// check for contact
			if(sizeof($warehouseIDs) && is_numeric($id))
			{
				
				//Bounces
				if ($type === 'bounce') {
					$Contacts = CampaignContact::model()->updateAll(
						array('bounced' => $datetime),
						"campaign_id = :campaign_id AND warehouse_id IN (" . implode(',', $warehouseIDs) . ") AND bounced IS NULL",
						array(':campaign_id' => $id)
					);

				}

				//Opens
				else {
					$Contacts = CampaignContact::model()->updateAll(
						array('opened' => $datetime),
						"campaign_id = :campaign_id AND warehouse_id IN (" . implode(',', $warehouseIDs) . ") AND opened IS NULL",
						array(':campaign_id' => $id)
					);

				}

				
			}

			echo 'Updated ' . sizeof($Contacts) . ' contact';




		}
		else
		{
			echo 'Campaign contact not found' . "\r\n";
		}

		echo "\r\n";

	}
}

?>