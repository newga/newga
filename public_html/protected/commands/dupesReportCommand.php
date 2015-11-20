<?php

class dupesReportCommand extends CConsoleCommand
{
	public function run($args)
	{
		exit('Disabled');
		
		$Organisations = Organisation::model()->findAll(array(
			'index' => 'id',
		));
		
		$command = Yii::app()->db->createCommand("
		
		SELECT i.id, i.status, i.date,i.contact_warehouse_id,organisation_id,store2contact_id,query_id, count(*) as 'dupe_count' 

		FROM invite i
		
		GROUP BY i.contact_warehouse_id, i.organisation_id
		
		HAVING dupe_count > 1
		
		ORDER BY dupe_count DESC
		
		");
		
		$results = $command->queryAll();
		
		$rows = '';
		
		$mismatchedOrg = [];
		
		// 4006
		
		//$results['contact_warehouse_id'] = 1234;
		//$results['organisation_id'] = 1;
		
		foreach($results as $result)
		{
			// All the s2c rows, with store
			
			$Store2Contact = Store2Contact::model()->with('store')->find(array(
				'condition' => 'contact_warehouse_id = :cwi AND store.origin_organisation_id = :org_id AND store.date_expired IS NULL',
				'params' => array(
					':cwi' => $result['contact_warehouse_id'],
					':org_id' => $result['organisation_id'],
				)
			));

			if($result['status'] == 3)
			{
				$acceeded = 'Y';
			}
			else
			{
				$acceeded = 'N';
			}
			
			// If Sent from Seven Stories, but no 
			if($result['organisation_id'] == 6 && $Store2Contact->store->origin_unique_id == 0)
			{
				$organisationName = 'Northern Stage (Sent from Seven Stories)';
			}
			else
			{
				$organisationName = $Organisations[$result['organisation_id']]->title;
			}
			
			$rows .= $Store2Contact->store->origin_unique_id . ',' . $result['store2contact_id'] .',' . $Store2Contact->id.','.$organisationName.','.$Store2Contact->store->email.','.$Store2Contact->store->first_name.','.$Store2Contact->store->last_name.','.$result['date'].','.$acceeded . "\n";

		}

		
$myFile = Yii::app()->basePath . "/../../protected-file-uploads/misc/dupes.csv";
		$fh = fopen($myFile, 'w') or die("can't open file");
		fwrite($fh, $rows);
		fclose($fh);
	}
}

?>