<?php

class fixSalutationsCommand extends CConsoleCommand
{
	public function run($args)
	{
		exit('Disabled');
		
		$StoreRows = Store::model()->findAll(array(
			'condition' => "salutation IN ('0','1','2','3','4','5')"
		));
		
		$salutations = array(
			0 => 'Mr',
			1 => 'Mrs',
			2 => 'Ms',
			3 => 'Miss',
			4 => 'Dr',
			5 => 'Prof',
		);
		
		$fixed = 0;
		
		foreach($StoreRows as $Store)
		{
			$wrongSalutation = $Store->salutation;
			$Store->salutation = $salutations[$wrongSalutation];
			
			
			if($Store->save(true, array('salutation')))
			{
				$fixed++;
			}
			
			
			print 'Fixed salutation from ' . $wrongSalutation . ' to ' . $Store->salutation . "\n";
		}
		
		print "\n\n" . 'fixed ' . $fixed . ' of ' . count($StoreRows) . " store rows \n\n";
	}
}


?>