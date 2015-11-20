<?php

class removeDupeInviteEmailCommand extends CConsoleCommand
{
	public function run($args)
	{
		exit('Use no.2 command');
		
		ini_set('memory_limit', '4G');
		
		$start = microtime(true);
		
		print "\n\n";
		print 'Running email de-duperer' . "\n\n";
		
		sleep(1);
		
		$QueuedCampaigns = Campaign::model()->findAll(array(
			"condition" => "processing = 0 AND status = :status",
			"params" => array(":status" => Campaign::STATUS_QUEUED)
		));
		
		$totalInvitesDeleted = 0;
		$invitesDeleted = [];
		
		print sizeof($QueuedCampaigns) . ' campaigns to process' . "\n\n";
		
		foreach($QueuedCampaigns as $QueuedCampaign)
		{
			print 'Looking up Campaign ' . $QueuedCampaign->id . "...\n";
			
			
			$Invites = Invite::model()->with('store')->findAll(array(
				'condition' => 'query_id != :qid',
				'params' => array(
					':qid' => $QueuedCampaign->query_id,
				),
			));
			
			$previouslyInvited = array();
			
			print 'Building invite array...' . "\n";
			
			foreach($Invites as $k => $Invite)
			{
				$previouslyInvited[] = $Invite->store->email;
				
				if(($k % 1000) == 0)
				{
					print '.';
				}
			}
			
			unset($Invites);
			
			sleep(1);
			print " DONE!\n\n";
			sleep(1);
			print sizeof($previouslyInvited) . ' previously invited contacts' . "\n";
			
			// Dupecheck this campaign
			$results = Invite::model()->with("store")->findAll(array(
				'condition' => 'campaign_id = :campaign_id',
				'params' => array(
					':campaign_id' => $QueuedCampaign->id,
				),
			));
			
			
			
			print sizeof($results) . ' contacts in this invite campaign' . "\n";
			
			$emails = [];
			
			if(sizeof($results))
			{
				foreach($results as $Invite)
				{
					$emails[$Invite->id] = $Invite->store->email;
				}
			}
			
			unset($results);
			
			// LOCAL DUPES
			/*
			print 'Finding duplicates within this campaign' . "\n";
			sleep(1);
			$arrayCountValues = array_count_values($emails);
			
			$localDuplicates = 0;
			
			foreach($arrayCountValues as $arrayCount)
			{
				if($arrayCount > 1)
				{
					$localDuplicates++;
				}
			}
			
			print 'Duplicates local to this campaign: ' . $localDuplicates . "\n\n";
			*/
			
			
			// Global dupes
			print 'Finding duplicates in entire invite set' . "\n";
			$arrayIntersect = array_intersect($emails, $previouslyInvited);
			
			unset($previouslyInvited);
			
			print 'Dupes found: ' . sizeof($arrayIntersect) . "\n";
			
			foreach($arrayIntersect as $inviteID => $dupeEmail)
			{
				//print 'Deleting duplicate Invite ID: ' . $inviteID . ' for ' . $dupeEmail . "\n";
				
				// $InviteToDelete = Invite::model()->findByPk($inviteID);
				// $InviteToDelete->delete();
				
				if(!in_array($inviteID, $invitesDeleted))
				{
					$totalInvitesDeleted++;
					
					
					$invitesDeleted[] = $inviteID;
				}
			}
			
			
			print "\n\n\n";
			print 'COMPLETE' . "\n";
			print "\n\n\n";
			
			unset($emails);
			unset($arrayIntersect);
			
			
			

		}
		
		print 'Total invites deleted: ' . $totalInvitesDeleted . "\n\n";
		
		$RemainingInvites = Invite::model()->count(array(
			'condition' => "date > '2015-01-01' AND status = 0",
		));
		
		print 'Invites remaining to be sent: ' . $RemainingInvites . "\n\n";
		
		print 'Peak memory usage: ' . (memory_get_peak_usage(true) / 1024 / 1024)  . "MB\n\n\n";
		
		$end = microtime(true);
		
		print 'Script ran in ' . round(($end - $start), 4) . ' seconds' . "\n\n";
		
		
		
		// Alternate with SQL
		
		$Invites = Yii::app()->db->createCommand("
		
		SELECT i.id, s.email, count(s.email) AS c FROM invite i

		LEFT JOIN store s ON i.store_id = s.id
		
		WHERE i.date > '2015-01-01'
		
		GROUP BY s.email HAVING c > 1
		
		")->queryAll();
		
		print 'Invites to remove using SQL query: ' . sizeof($Invites) . "\n\n";
		
	}
}

?>