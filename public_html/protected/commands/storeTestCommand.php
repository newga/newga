<?php


class storeTestCommand extends CConsoleCommand
{
	public function run($args)
	{
		exit('Disabled');
		
		$Stores = Store::model()->with('organisation')->findAll();
		
		foreach($Stores as $Store)
		{
			if(strlen($Store->email))
			{
				print $Store->first_name . ',' . $Store->last_name . ',' . $Store->email . ',' . $Store->organisation->title . ',' . $this->getEmailPrefs($Store->contact_email) . "\r\n";
				
				//print $Store->last_name . "\n";
				//print $Store->address_line_1 . "\n\n\n";
				//print $Store->phone . "\n\n\n";
				//print $Store->mobile . "\n\n\n";
			}
		}
		
		exit();
		
		
		/*
		$StoreModel = new Store;
		$email = $StoreModel->encryptEmail($email);
		
		$Store = Store::model()->findAll(array(
			
			'condition' => 'email = :email',
			'params' => array(
				':email' => $email,
			),
			
		));
		
		print_r($Store);
		*/
	}
	
	public function getEmailPrefs($emailPrefs)
	{
		if($emailPrefs == 0)
		{
			return 'No contact by email';
		}
		elseif($emailPrefs == 1)
		{
			return 'ok to contact';
		}
		else
		{
			return 'Unknown';
		}
	}
}



?>