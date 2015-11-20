<?php

class unsubsReportCommand extends CConsoleCommand
{
	public function run($args)
	{
		exit('Disabled');
		
		ini_set('memory_limit', '512M');
		
		$Organisations = Organisation::model()->findAll(array(
			'index' => 'id',
		));
		
		/*
		$command = Yii::app()->db->createCommand("
		
		SELECT s2c.id, s.email, o.title, CONCAT(i.contact_warehouse_id,organisation_id) AS conc FROM suppression_list sl
		
		INNER JOIN store2contact s2c ON s2c.store_id = sl.store_id
		
		LEFT JOIN store s ON s.id = s2c.store_id
		
		LEFT JOIN invite i ON s2c.id = i.store2contact_id
		
		LEFT JOIN organisation o ON o.id = i.organisation_id
		
		WHERE sl.`date` >= '2014-12-05' AND s.email IS NOT NULL AND type = 1
		
		GROUP BY conc
		
		");
		*/
		
		$command = Yii::app()->db->createCommand("
		SELECT i.organisation_id, s2c.contact_warehouse_id, sl.*, s2c.id AS store2contact_id FROM suppression_list sl
		
		LEFT JOIN store2contact s2c ON s2c.store_id = sl.store_id
		
		LEFT JOIN invite i ON s2c.id = i.store2contact_id
		
		WHERE sl.`date` >= '2014-12-05'  AND type = 1
		
		GROUP BY sl.store_id
		");
		
		
		$results = $command->queryAll();
		
		//print count($results) . ' unsubscribes' . "\n\n";
		
		$rows = '';
		
		$StoreModel = new Store;
		
		foreach($results as $result)
		{
			$Store2Contact = Store2Contact::model()->with('store')->find(array(
				'condition' => 'contact_warehouse_id = :cwi AND store.origin_organisation_id = :org_id AND store.date_expired IS NULL',
				'params' => array(
					':cwi' => $result['contact_warehouse_id'],
					':org_id' => $result['organisation_id'],
				)
			));
			
			if(!is_null($Store2Contact))
			{
				// If Sent from Seven Stories, but no 
				if($result['organisation_id'] == 6 && $Store2Contact->store->origin_unique_id == 0)
				{
					$organisationName = 'Northern Stage (Sent from Seven Stories)';
				}
				else
				{
					$organisationName = $Organisations[$result['organisation_id']]->title;
				}
				
				$rows .= $Store2Contact->store->origin_unique_id . ',' . $result['store2contact_id'] .',' . $Store2Contact->id.','.$organisationName.','.$Store2Contact->store->email.','.$Store2Contact->store->first_name.','.$Store2Contact->store->last_name."\n";
			}
			else
			{
				print_r($result);
			}
		}
		
		
		
		
		$myFile = Yii::app()->basePath . "/../../protected-file-uploads/misc/unsubs.csv";
		$fh = fopen($myFile, 'w') or die("can't open file");
		fwrite($fh, $rows);
		fclose($fh);
	}
}

?>