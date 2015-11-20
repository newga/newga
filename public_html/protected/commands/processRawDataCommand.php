<?php

//Processes data in raw_import

set_time_limit(0);
date_default_timezone_set("Europe/London");
ini_set('auto_detect_line_endings', true);
mb_internal_encoding('UTF-8');
ini_set('memory_limit', '-1');


class processRawDataCommand extends CConsoleCommand
{

	private $fp; 
  	private $totalDupes = 0;
  	private $totalImported = 0;
  	private $totalUpdated = 0;
  	protected $key1;
	protected $key2;
	protected $key3;
	protected $key4;
	protected $key5;
	protected $iv;
	protected $cipher;
	protected $mode;
	protected $rowsToProcess;
	protected $lastRowProcessed;
	protected $time_start;
	protected $completedToNow;
	protected $timeTakenSoFar;


	public function __construct()
	{
		$this->key1 = 'FzJRQR5cpNeLnkDfiQ4nguWzwXtyovDp';
		$this->key2 = 'Gzgigr5cpNeLnkncbe4nguHeuttyovFR';
		$this->key3 = 'JjhgkhyuI767GHOWd289BKWDHkjhwwuW';
		$this->key4 = 'JjgdfghyuI767Geggg28er9BKWDHkjhw';
		$this->key5 = 'Hu29ggIUGuhuhiuheHEO37giuuhwuhwf';
		$this->cipher = MCRYPT_RIJNDAEL_256;
		$this->mode = MCRYPT_MODE_ECB;
		$this->iv = mcrypt_create_iv(mcrypt_get_iv_size($this->cipher, $this->mode), MCRYPT_RAND);

		// loop the next xxxx rows
		$this->rowsToProcess = 600000;

	}
	
	public function run($args)
	{
		exit('Disabled');
		
		$this->time_start = microtime(true); 
		
		//get current id
		$db = new PDO(Yii::app()->params['db']['connectionString'], Yii::app()->params['db']['username'], Yii::app()->params['db']['password'], array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));

		//is it processing already?
		$settings = $db->query('SELECT processing, pointer FROM import_pointer WHERE id = 1');
		$settingsRow = $settings->fetch(PDO::FETCH_ASSOC);
		$this->lastRowProcessed = $settingsRow['pointer'];

		if ($settingsRow['processing']) {
			echo "\n\n".'Already running'."\n\n";
			exit();
		}
		unset($settingsRow);

        $count = $db->query('SELECT count(id) as total FROM raw_import WHERE id > ' . $this->lastRowProcessed);
        $totalRemainingToProcess = $count->fetch(PDO::FETCH_ASSOC);

        if ($totalRemainingToProcess['total'] == 0) {
        	echo "\n\n".'Nothing to process'."\n\n";
        	exit();
        }
       

		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		$headers .= 'From: Raw Import <'.Yii::app()->params['siteEmail'].'>' . "\r\n";

		$message = 'Import started '. date('d M Y H:i:s') . '<br />';
		$message .= 'Total left in import queue is ' . $totalRemainingToProcess['total']; 
		unset($totalRemainingToProcess);

		// Send it
		mail(Yii::app()->params['adminEmail'], 'Import STARTED', $message, $headers);
		unset($headers, $message);

		$db->query('UPDATE import_pointer SET processing = 1 WHERE id = 1');


		// run the import method below with a pointer ID to define first row to process
		$this->import();


		//save id to pointer
		$db->query('UPDATE import_pointer SET processing = 0, pointer = ' . $this->lastRowProcessed . ' WHERE id = 1');

		$execution_time = round((microtime(true) - $this->time_start)/60,2);

		//execution time of the script
		print "\n\n".'Total Execution Time: '.$execution_time.' mins'."\n\n";

		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	
		// Additional headers
		$headers .= 'From: Raw Import <'.Yii::app()->params['siteEmail'].'>' . "\r\n";		
		$message = 'Import completed '.date('d M Y H:i:s') . '<br />';
		$message .= 'Execution time ' . $execution_time . ' mins <br />';
		$message .= 'Total processed ' . $this->totalImported . ' <br />';
		$message .= 'of which were updated ' . $this->totalUpdated . ' <br />';
		$message .= 'Total duplicates in import file ' . $this->totalDupes . ' <br />';

		// Send it
		mail(Yii::app()->params['adminEmail'], 'Import FINISHED', $message, $headers);
		unset($headers, $message);

		exit;
	}


	private function import() {

		$db = new PDO(Yii::app()->params['db']['connectionString'], Yii::app()->params['db']['username'], Yii::app()->params['db']['password'], array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));

		$resultRows = $db->query('SELECT * FROM raw_import WHERE id > ' . $this->lastRowProcessed . ' LIMIT ' . $this->rowsToProcess);

		$suppressionCount = 0;

		echo "\n\n".'Pointer is at '.$this->lastRowProcessed . "\n\n";

		while($data = $resultRows->fetch(PDO::FETCH_ASSOC) )
		{
			$StoreView = null;
			$DupeMatch = null;
			$CodeMatch = null;
			$Store2Contact = null;

//20532

			// 1 yes 0 no 2 unknown
			$contact_email = 2; //unkown
			$contact_post = 2;
			$contact_sms = 2;
			$organisation_id = null;


			// create base Store instance.
			$StoreView = new Store();

			$data['CCR_Client_URN'] = $this->emptyToNull($data['CCR_Client_URN']);

			if(is_null($data['CCR_Client_URN'])){

				// no URN no insert
				continue;
			}


			$StoreView['origin_unique_id'] = trim($data['CCR_Client_URN']);

			$StoreView['csv_file_uuid'] = $data['Cleaning_UUID'];
			$StoreView['date_imported'] = date('Y-m-d H:i:s');


			//Get organisation id
			switch ($data['CCR_Organisation']) {

				case 'Example 1':
					$organisation_id = 1;

           			switch ($data['CCR_Email_Allow']) {
						case 'Allow':
							$contact_email = 1;
						break;
						case 'Do Not Allow':
							$contact_email = 0;
						break;
					}

				break;


           		default:
           		break;
			}


			$StoreView['contact_email'] = $contact_email;
			$StoreView['contact_sms'] = $contact_sms;
			$StoreView['contact_post'] = $contact_post;

			$StoreView['origin_organisation_id'] = $organisation_id;

			$StoreView['salutation'] = $this->emptyToNull($data['CCR_Title']);
			$StoreView['first_name'] = $this->emptyToNull($data['CCR_Forename']);
			$StoreView['last_name'] = $this->emptyToNull($data['CCR_Surname']);

			if(!is_null($StoreView['last_name']))
			{
				$StoreView['last_name'] = rtrim(mcrypt_decrypt($this->cipher, $this->key3, base64_decode($StoreView['last_name']), $this->mode, $this->iv));
			}
			
			$StoreView['address_line_1'] = $this->emptyToNull($data['CCR_Address1']);
			
			if(!is_null($StoreView['address_line_1']))
			{
				$StoreView['address_line_1'] = rtrim(mcrypt_decrypt($this->cipher, $this->key2, base64_decode($StoreView['address_line_1']), $this->mode, $this->iv));
			}
			
			$StoreView['address_line_2'] = $this->emptyToNull($data['CCR_Address2']);
			$StoreView['address_line_3'] = $this->emptyToNull($data['CCR_Address3']);
			$StoreView['address_line_4'] = $this->emptyToNull($data['CCR_Address4']);
			$StoreView['address_town'] = $this->emptyToNull($data['CCR_Town']);

			$data['CCR_Postcode'] = trim($data['CCR_Postcode']);

			if ($this->isPostcode($data['CCR_Postcode'])) {

				$StoreView['address_postcode']=$data['CCR_Postcode'];

			}
			else {
				
				$StoreView['address_postcode'] = null;

			}



			$StoreView['address_county'] = $this->emptyToNull($data['CCR_County']);


// phone and mobile

			//regex to to see if provided phone number is mobile
			$phone1 = intval(rtrim(mcrypt_decrypt($this->cipher, $this->key4, base64_decode($data['CCR_Phone1']), $this->mode, $this->iv)));
			$phone2 = intval(rtrim(mcrypt_decrypt($this->cipher, $this->key5, base64_decode($data['CCR_Phone2']), $this->mode, $this->iv)));

			if ($phone1 && $phone2)
			{
				if ($this->isMobile($phone1))
				{
					$StoreView['mobile'] = $phone1;
					$StoreView['phone'] = $phone2;
				}

				if ($this->isMobile($phone2))
				{
					$StoreView['mobile'] = $phone2;
					$StoreView['phone'] = $phone1;
				}
			}
			else if ($phone1)
			{
				if ($this->isMobile($phone1))
				{
					$StoreView['mobile'] = $phone1;
				}
				else 
				{
					$StoreView['phone'] = $phone1;
				}
			}
			else if ($phone2) 
			{
				if ($this->isMobile($phone2))
				{
					$StoreView['mobile'] = $phone2;
				}
				else
				{
					$StoreView['phone'] = $phone2;
				}
			}

// end phone and mobile



// email

			$StoreView->email = $data['CCR_Email'];

			if(!is_null($StoreView->email)){
				$StoreView->email = rtrim(mcrypt_decrypt($this->cipher, $this->key1, base64_decode($StoreView['email']), $this->mode, $this->iv));
			}

			if(!is_null($StoreView['email']) && !filter_var($StoreView->email, FILTER_VALIDATE_EMAIL)){
				$StoreView->email = null;
			}

// end email

			// Save the contact in the store
			if(!$StoreView->validate())
			{
				print '$StoreView->errors:'."\n";
				print_r($StoreView->errors); 
			}
			else
			{

				$this->totalImported++;
				//in dupe set
				$data['CCR_Ind_Set'] = (int)trim($data['CCR_Ind_Set']);


/*


if(this row has (int)CCR_Ind_Set > 0){
	
	select existing 1+ duplicates

	if(this row (int)CCR_Ind_Set matches a ccr_duplicate_id with the same organisation_id){
	
		// mega dupe
		if( existing record has 'Yes' for CCR_Ind_Dupe1 ){
	
			insert and expire this row
		}
		else
		{
			expire existing
			insert this row
		}
	}
	else
	{
		// no other rows in same org
		insert this row
	}
}
else
{
	// not a duplicate
}


*/

				// is this the ccr dupe favoured row?
				$StoreView['ccr_ind_dupe1'] = trim($data['CCR_Ind_Dupe1']) === 'Yes' ? 1 : 0;





				// If CCR has detected a duplicate...
				if ($data['CCR_Ind_Set'] > 0)
				{
					$this->totalDupes++;
					$StoreView->ccr_duplicate_id = $data['CCR_Ind_Set'];

//print "CCR_Ind_Set > 0\n";

					//first of all, let see if the dupe has the same origin id as a previous in the database
					
					$DupeMatches = Store::model()->with(array('store2contact'))->findAllByAttributes(array(
				 			'ccr_duplicate_id' => $data['CCR_Ind_Set'],
				 			'date_expired' => null,
							//'origin_organisation_id'=>$organisation_id
					), array(
						'index' => 'origin_organisation_id'
					));

// print ('starting new loop with dupes');
					if (sizeof($DupeMatches))
					{

// print "size DupeMatches > 0\n";

						$Store2Contact = null;

// print_r(array_keys($DupeMatches));
// print "\nDupeMatch orgs: " . $organisation_id . "\n";

						if(array_key_exists((int)$organisation_id, $DupeMatches))
						{
							// we have a match from the same organisation

// print "org exists in dupes data\n";

							//not do in the correct order, needs to be done with CCR_Ind_Set
						 	//we need to expire the match and update the warehouse record
						  	//we the new one
							//echo 'DupeMatch';

							if($DupeMatches[$organisation_id]->ccr_ind_dupe1)
							{
// print "Alread have trusted row, expire row\n";
								// already have a trusted row. Expire and insert this one.
								$StoreView->date_expired = date('Y-m-d H:i:s');
								$StoreView->ccr_ind_dupe1 = 0; // just in case.
							}
							else
							{
// print "Update store row\n";
								// update store row
								$DupeMatches[$organisation_id]->date_expired = date('Y-m-d H:i:s');
								$DupeMatches[$organisation_id]->save();

								// update any suppression list rows to not have warehouse or store_id. They're old and expired.
								SuppressionList::model()->deleteAll('store2contact_id = ' . (int)$DupeMatches[$organisation_id]->store2contact->id);

								// clone the contact_warehouse_id and ind set. Gets saved below.
								$Store2Contact = $DupeMatches[$organisation_id]->store2contact;
// print 'Store2Contact=';
// print_r ($Store2Contact->attributes);

								$Store2Contact->contact_warehouse_id = $DupeMatches[$organisation_id]->store2contact->contact_warehouse_id;
							}

						}
						else
						{

// print "org does not exist in dupes data\n";

							//is it not a dupe match but someone else has the same dupe id?
							//if so, lets find their Warehouse ID and then add as a new row

							$LastDuplicate = end($DupeMatches);

							$Store2Contact = new Store2Contact;
							$Store2Contact->contact_warehouse_id = $LastDuplicate->store2contact->contact_warehouse_id;

						}

						// the save for dupes is here
						if(!$StoreView->save())
						{
							print_r($StoreView->errors);
						}


						// if we have a store2contact save it here
						if(!is_null($Store2Contact))
						{

							//update store_id to new one
							$Store2Contact->store_id = $StoreView->id;

							$Store2Contact->origin_unique_id = $data['CCR_Client_URN'];
							$Store2Contact->origin_id = $organisation_id;

// print 'saving a (dupe style) store2contact ' . $Store2Contact->id . "\n";

							if(!$Store2Contact->save())
							{
								print_r($Store2Contact->errors);
							}
						}

						// currently required below.
						//unset($Store2Contact);
					}
				}


				// when not a duplicate or there is no other dupe stored yet
				if($data['CCR_Ind_Set'] == 0 || !sizeof($DupeMatches))
				{
					// save for when no duplicates

					if(!$StoreView->save())
					{
						print_r($StoreView->errors);
					}

					$Warehouse = new Warehouse;
					if(!$Warehouse->save())
					{
						print_r($Warehouse->errors);
					}
					
					// Create a new Store2Contact row
					$Store2Contact = new Store2Contact;
					$Store2Contact->store_id = $StoreView->id;
					$Store2Contact->contact_warehouse_id = $Warehouse->id;

					$Store2Contact->origin_unique_id = $data['CCR_Client_URN'];
					$Store2Contact->origin_id = $organisation_id;



					if(!$Store2Contact->save())
					{
						print_r($Store2Contact->errors);
					}

				}

				// Check for non-contactable by email, and add to the supression list
				if($StoreView->contact_email == 0)
				{

					if(!is_null($Store2Contact) && (int)$Store2Contact->id){

						$Suppression = new SuppressionList;
						$Suppression->type = SuppressionList::TYPE_UNSUBSCRIBE;
						
						// always save the store id against this row
						$Suppression->store_id = $StoreView->id;

						// we have a store2contact row for this person. Add to suppression data.
						$Suppression->store2contact_id = $Store2Contact->id;
						
						//We don't need the warehouse id
						//$Suppression->warehouse_id = $Store2Contact->contact_warehouse_id;

						$Suppression->date = date('Y-m-d H:i:s');
						
						if($Suppression->save())
						{
							$suppressionCount++;
						}
					}
				}

			}

			unset($data);
			unset($StoreView);
			unset($Store2Contact);
			unset($DupeMatch);
			unset($CodeMatch);

			$this->lastRowProcessed++;

			if (!($this->lastRowProcessed % 100))
			{
				$this->completedToNow = $this->lastRowProcessed;
				$timeTotal = (microtime(true) - $this->time_start);
				$timeForThis100 = $timeTotal - $this->timeTakenSoFar;
				$this->timeTakenSoFar = $timeTotal;
				$speed = round((1/$timeForThis100)*100, 3);

				$currentExecutionTime = round($timeTotal/60,2);
				echo ($this->lastRowProcessed / 1000) . 'k done in ' . $currentExecutionTime.' mins. ' . $speed . '  rows/s                    ' . "\r";
			}
		}
		
		print "\n\n" . $suppressionCount . ' contacts were added to suppression list - not contactable by email' . "\n\n";
	}


	public function emptyToNull($string) {
		if (in_array($string, array('NULL', 'null', '', 'Unknown'))) return NULL;
		else return $string;
	}

	public function convertEncoding($str)
    {
        $currentEncoding = mb_detect_encoding($str);

        if($currentEncoding == "UTF-8")
        {
            $str = $this->remove_utf8_bom($str);
        }

        return iconv( $currentEncoding, "UTF-8", utf8_encode(trim($str)) );
       
    } 

    public function isMobile($aNumber) {
   	 	return preg_match('/(^7)|(^447)/', $aNumber);
	}

	 public function isPostcode($postcode) {
   	 	return preg_match('@^[A-Z][A-Z]?[0-9][0-9]?[A-Z]? ?[0-9]?[A-Z]?[A-Z]?$@i', $postcode);
	}
  
   
    public function remove_utf8_bom($text)
    {
        $bom = pack('H*','EFBBBF');
        $text = preg_replace("/^$bom/", '', $text);
        
        return $text;
    }


}

?>