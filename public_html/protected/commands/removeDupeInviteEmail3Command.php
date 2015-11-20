<?php

class removeDupeInviteEmail3Command extends CConsoleCommand
{
	public $totalInvitesDeleted = 0;
	
	public function run($args)
	{
		ini_set('memory_limit', '128M');
		
		$start = microtime(true);
		
		$PendingInvites = Invite::model()->count(array(
			'condition' => "date > '2015-01-01' AND status = 0",
		));
		
		print 'Invites to be sent: ' . $PendingInvites . "\n\n";
		
		print "\n\n";
		
		$this->removeDupes();
		
		
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
					print 'Invite to delete: ' . $Invite->id . "\n";
					
					// Only delete invites from campaigns that have not been sent
					if(in_array($inviteArray['campaignStatus'], array(Campaign::STATUS_NOT_RUN, Campaign::STATUS_QUEUED)))
					{
						// Also make sure we don't delete Invites that have been sent
						if($Invite->status == Invite::STATUS_UNSENT)
						{
							$Invite->delete();
							
							$this->totalInvitesDeleted++;
						}
					}
				}
			}
			
		}
	}
	
	public function getDupeInvites()
	{
		$Invites = Yii::app()->db->createCommand("
		
SELECT t.id, t.email,t.date, SUM(t.beforeCount) AS beforeCountSum, SUM(t.afterCount) AS afterCountSum, t.name, campaignStatus FROM (
	
	SELECT i.id, s.email,  i.date, c.name, c.status AS campaignStatus,
	
	CASE 
		WHEN i.date < '2015-01-01' THEN 1 ELSE 0
	END AS beforeCount,
	
	CASE 
		WHEN i.date >= '2015-01-01' THEN 1 ELSE 0
	END AS afterCount
	
	
	FROM invite i
	
	LEFT JOIN store s ON i.store_id = s.id
	
	LEFT JOIN campaign c ON c.id = i.campaign_id
	
	ORDER BY i.date DESC
	
) as t


GROUP BY t.email 

HAVING
	(afterCountSum > 0 AND beforeCountSum > 0 )
	OR
	(afterCountSum > 1)

		
		")->queryAll();
		
		return $Invites;
	}
}

?>