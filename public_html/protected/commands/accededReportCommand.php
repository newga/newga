<?php

class accededReportCommand extends CConsoleCommand
{
	public function run($args)
	{
		exit();
		
		$command = Yii::app()->db->createCommand("
		
		SELECT a.id, s.email FROM accession a
		
INNER JOIN store2contact s2c ON s2c.id = a.store2contact_id 

LEFT JOIN store s ON s.id = s2c.store_id

LEFT JOIN suppression_list sl ON sl.store2contact_id = s2c.id

LEFT JOIN suppression_list sl2 ON sl2.warehouse_id = s2c.contact_warehouse_id

WHERE a.terms_agreed IS NOT NULL AND sl.id IS NULL AND sl2.id IS NULL
		
		");
		
		$results = $command->queryAll();
		
		$rows = [];
		
		$Store = new Store;
		
		foreach($results as $result)
		{
			$email = $Store->decryptEmail($result['email']);
			
			if(strlen($email))
			{
				$rows[] = $result['id'] . ',' . $email;
			}
			
		}
		$csv = 'Acceded Report Generated ' . date('Y-m-d H:i:s') . "\n";
		$csv .= implode("\n", $rows);
		
		$myFile = Yii::app()->basePath . "/../../protected-file-uploads/misc/acceded.csv";
		$fh = fopen($myFile, 'w') or die("can't open file");
		fwrite($fh, $csv);
		fclose($fh);
	}
}

?>