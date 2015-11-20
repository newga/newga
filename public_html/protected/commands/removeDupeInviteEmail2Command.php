<?php

class removeDupeInviteEmail2Command extends CConsoleCommand
{
	public $totalInvitesDeleted = 0;
	public $loopCount = 0;
	
	public function run($args)
	{
		exit('use v3');
		
		ini_set('memory_limit', '128M');
		
		$start = microtime(true);
		
		$PendingInvites = Invite::model()->count(array(
			'condition' => "date > '2015-01-01' AND status = 0",
		));
		
		print 'Invites to be sent: ' . $PendingInvites . "\n\n";
		
		print "\n\n";
		
		$this->removeDupes();
		
		print 'Loops completed: ' . $this->loopCount . "\n";
		
		print 'Total invites deleted: ' . $this->totalInvitesDeleted . "\n\n";
		
		$RemainingInvites = Invite::model()->count(array(
			'condition' => "date > '2015-01-01' AND status = 0",
		));
		
		print 'Invites remaining to be sent: ' . $RemainingInvites . "\n\n";
		
		print 'Peak memory usage: ' . (memory_get_peak_usage(true) / 1024 / 1024)  . "MB\n\n\n";
		
		$end = microtime(true);
		
		print 'Script ran in ' . round(($end - $start), 4) . ' seconds' . "\n\n";
	}
	
	public function removeDupes()
	{
		$Invites = $this->getDupeInvites();
		
		if(sizeof($Invites))
		{
			print 'Found ' . count($Invites) .  ' to delete' . "\n";
			
			foreach($Invites as $inviteArray)
			{
				$Invite = Invite::model()->findByPk($inviteArray['id']);
				
				if(is_null($Invite))
				{
					print 'ERROR - Invite ' . $inviteArray['id'] . ' not found' . "\n";
				}
				else
				{
					$Invite->delete();
				
					$this->totalInvitesDeleted++;
				}
			}
			
			$this->loopCount++;
			
			$this->removeDupes(); // recurse
		}
	}
	
	public function getDupeInvites()
	{
		$Invites = Yii::app()->db->createCommand("
		
		SELECT i.id, s.email, count(s.email) AS c FROM invite i

		LEFT JOIN store s ON i.store_id = s.id
		
		WHERE i.date > '2015-01-01'
		
		GROUP BY s.email HAVING c > 1
		
		")->queryAll();
		
		return $Invites;
	}
}

?>