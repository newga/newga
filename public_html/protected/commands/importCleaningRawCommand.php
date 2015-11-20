<?php

//Command to import raw data from cleaning company and place in raw_import

set_time_limit(0);
date_default_timezone_set("Europe/London");
ini_set('auto_detect_line_endings', true);
mb_internal_encoding('UTF-8');
error_reporting(E_ALL & ~E_NOTICE);

class importCleaningRawCommand extends CConsoleCommand
{
	
	protected $key1;
	protected $key2;
	protected $key3;
	protected $key4;
	protected $key5;
	protected $iv;
	protected $cipher;
	protected $mode;
	
	private $fp; 
	
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
	}
	

	public function run($args)
	{
		exit('Disabled');
		
		$time_start = microtime(true); 
		
		$path = Yii::app()->basePath .'/../../protected-file-uploads/cleaning-company/to-process/'	;

		print "\n\n".'Looking for data in '. $path ."\n\n";


		//$files = glob($path . '*.csv');
		$CFiles  = CleaningFile::model()->findAllByAttributes(
			array('status' => 0)
		);

		if ($CFiles) {


			foreach ($CFiles as $CFile) {

				//$CFile->status = 1;
				//$CFile->save();
				// if (!$CFile->save()) {
				// 	print_r($CFile->getErrors());
				// }
			
				$file = $path.'/'.$CFile->uuid.'.csv';
				
				if(file_exists($file))
				{
					$this->importCSV($file, $CFile->uuid);
					
					// Delete File
					unlink(Yii::app()->basePath .'/../../protected-file-uploads/cleaning-company/to-process/'.$CFile->uuid.'.csv');
					
					$CFile->status = 1;
					$CFile->import_date = date('Y-m-d H:i:s');
					$CFile->save();
				}
				else
				{
					print "\n".'File ' . $file . ' does not exist and was skipped' . "\n";
				}
			}

			
			

		}

		$time_end = microtime(true);

		$execution_time = round(($time_end - $time_start)/60, 2);

		//execution time of the script
		print "\n\n".'Total Execution Time: '.$execution_time.' mins'."\n\n";
		print count($CFiles) . ' files processed' . "\n\n";
		exit();
	}

	private function importCSV($csv,$uuid) 
	{
		
		
		
		//get a row from encrpyted file
		//$EncryptFile = new EncryptFile();
		//$this->fp = $EncryptFile->fileOpen($csv);
 
		$this->fp = fopen($csv, 'r');

		//$header = fgetcsv($this->fp);

		while (($row = fgetcsv($this->fp)) !== FALSE) 
		{
			$command = Yii::app()->db->createCommand("
				INSERT INTO raw_import(
				
					MASTER_CCR_ID,
					CCR_ID,
					CCR_Client_URN,
					CCR_Source,
					CCR_Title,
					CCR_Forename,
					CCR_Surname,
					CCR_Address1,
					CCR_Address2,
					CCR_Address3,
					CCR_Address4,
					CCR_Address5,
					CCR_Address6,
					CCR_Town,
					CCR_County,
					CCR_DPS,
					CCR_Postcode,
					CCR_Country,
					CCR_Phone1,
					CCR_Phone2,
					CCR_Email,
					CCR_PAF,
					CCR_Ind_Set,
					CCR_Ind_Dupe1,
					CCR_Organisation,
					CCR_Email_Allow,
					Cleaning_UUID
				)
					VALUES
				
				(
					:MASTER_CCR_ID,
					:CCR_ID,
					:CCR_Client_URN,
					:CCR_Source,
					:CCR_Title,
					:CCR_Forename,
					:CCR_Surname,
					:CCR_Address1,
					:CCR_Address2,
					:CCR_Address3,
					:CCR_Address4,
					:CCR_Address5,
					:CCR_Address6,
					:CCR_Town,
					:CCR_County,
					:CCR_DPS,
					:CCR_Postcode,
					:CCR_Country,
					:CCR_Phone1,
					:CCR_Phone2,
					:CCR_Email,
					:CCR_PAF,
					:CCR_Ind_Set,
					:CCR_Ind_Dupe1,
					:CCR_Organisation,
					:CCR_Email_Allow,
					:Cleaning_UUID
					
				)
				;
			");
			
			$command->bindValues(array(
				':MASTER_CCR_ID'	=> (int)$row[0],
				':CCR_ID'			=> (int)$row[1],
				':CCR_Client_URN'	=> strlen($row[2]) ? mb_convert_encoding($row[2], 'UTF-8') : null,
				':CCR_Source'		=> (int)$row[3],
				':CCR_Title'		=> strlen($row[4]) ? mb_convert_encoding($row[4], 'UTF-8') : null,
				':CCR_Forename'		=> strlen($row[5]) ? mb_convert_encoding($row[5], 'UTF-8') : null,
				
				// Encrypt surname
				':CCR_Surname'		=> strlen($row[6]) ? trim(base64_encode(mcrypt_encrypt($this->cipher, $this->key3, mb_convert_encoding($row[6], 'UTF-8'), $this->mode, $this->iv))) : null,
				
				// encrypt address 1
				':CCR_Address1'		=> strlen($row[7]) ? trim(base64_encode(mcrypt_encrypt($this->cipher, $this->key2, mb_convert_encoding($row[7], 'UTF-8'), $this->mode, $this->iv))) : null,
				
				':CCR_Address2'		=> strlen($row[8]) ? mb_convert_encoding($row[8], 'UTF-8') : null,
				':CCR_Address3'		=> strlen($row[9]) ? mb_convert_encoding($row[9], 'UTF-8') : null,
				':CCR_Address4'		=> strlen($row[10]) ? mb_convert_encoding($row[10], 'UTF-8') : null,
				':CCR_Address5'		=> strlen($row[11]) ? mb_convert_encoding($row[11], 'UTF-8') : null,
				':CCR_Address6'		=> strlen($row[12]) ? mb_convert_encoding($row[12], 'UTF-8') : null,
				':CCR_Town'			=> strlen($row[13]) ? mb_convert_encoding($row[13], 'UTF-8') : null,
				':CCR_County'		=> strlen($row[14]) ? mb_convert_encoding($row[14], 'UTF-8') : null,
				':CCR_DPS'			=> strlen($row[15]) ? mb_convert_encoding($row[15], 'UTF-8') : null,
				':CCR_Postcode'		=> strlen($row[16]) ? mb_convert_encoding($row[16], 'UTF-8') : null,
				':CCR_Country'		=> strlen($row[17]) ? mb_convert_encoding($row[17], 'UTF-8') : null,
				
				// encrypt phones
				':CCR_Phone1'		=> strlen($row[18]) ? trim(base64_encode(mcrypt_encrypt($this->cipher, $this->key4, (int)preg_replace('@[^0-9]@', '', $row[18]), $this->mode, $this->iv))) : null,
				':CCR_Phone2'		=> strlen($row[19]) ? trim(base64_encode(mcrypt_encrypt($this->cipher, $this->key5, (int)preg_replace('@[^0-9]@', '', $row[19]), $this->mode, $this->iv))) : null,
				
				// encrypt email
				':CCR_Email'		=> strlen($row[20]) ? trim(base64_encode(mcrypt_encrypt($this->cipher, $this->key1, strtolower(mb_convert_encoding($row[20], 'UTF-8')), $this->mode, $this->iv))) : null,
				
				':CCR_PAF'			=> strlen($row[21]) ? mb_convert_encoding($row[21], 'UTF-8') : null,
				':CCR_Ind_Set'		=> (int)$row[24],
				':CCR_Ind_Dupe1'	=> isset($row[25]) ? mb_convert_encoding($row[25], 'UTF-8') : null,
				':CCR_Organisation'	=> mb_convert_encoding($row[22], 'UTF-8'),
				':CCR_Email_Allow'	=> mb_convert_encoding($row[23], 'UTF-8'),
				':Cleaning_UUID' 	=> $uuid,
			));
			
			$command->execute();
			
			unset($command);
		}
		
		/*
		$dbvars = Yii::app()->params['db'];
		
		
		$db = new PDO($dbvars['connectionString'], $dbvars['username'], $dbvars['password'], array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));


		print 'Importing ' . $csv ."\n\r";

		//get a row from encrpyted file
		//$EncryptFile = new EncryptFile();
		//$this->fp = $EncryptFile->fileOpen($csv);
 
		$this->fp = fopen($csv, 'r');

		$header = fgetcsv($this->fp); 

		while (($row = fgetcsv($this->fp)) !== FALSE) 
		{ 
					
			$sql = "INSERT INTO raw_import (MASTER_CCR_ID,CCR_ID,CCR_Client_URN,CCR_Source,CCR_Title,CCR_Forename,CCR_Surname,CCR_Address1,CCR_Address2,CCR_Address3,CCR_Address4,CCR_Address5,CCR_Town,CCR_County,CCR_DPS,CCR_Postcode,CCR_Country,CCR_Phone1,CCR_Phone2,CCR_Email,CCR_PAF,CCR_Ind_Set,CCR_Ind_Dupe1,CCR_Organisation,CCR_Email_Allow,Cleaning_UUID) 
				values ('".addslashes($row[0])."','".addslashes($row[1])."','".addslashes($row[2])."','".addslashes($row[3])."','".addslashes($row[4])."','".addslashes($row[5])."','".addslashes($row[6])."','".addslashes($row[7])."','".addslashes($row[8])."','".addslashes($row[9])."','".addslashes($row[10])."','".addslashes($row[11])."','".addslashes($row[13])."','".addslashes($row[14])."','".addslashes($row[15])."','".addslashes($row[16])."','".addslashes($row[17])."','".addslashes($row[18])."','".addslashes($row[19])."','".addslashes($row[20])."','".addslashes($row[21])."','".addslashes($row[22])."','".addslashes($row[23])."','".addslashes($row[24])."','".addslashes($row[25])."','$uuid')";
			 $result = $db->exec($sql);
			
		}
		*/

	}


	function replace4byte($string) {
		 return preg_replace('%(?:
			   \xF0[\x90-\xBF][\x80-\xBF]{2}	  # planes 1-3
			 | [\xF1-\xF3][\x80-\xBF]{3}		  # planes 4-15
			 | \xF4[\x80-\x8F][\x80-\xBF]{2}	  # plane 16
		 )%xs', '', $string);	 
	}


}

?>