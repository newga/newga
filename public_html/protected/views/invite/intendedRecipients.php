<div class="page-header">
	<h1>Invite <em><?php print $Campaign->name; ?></em></h1>
</div>
<?php

$this->renderPartial('_tabs_before_send', array('Campaign' => $Campaign));

?>




<?php



if(sizeof($results) > 1000)
{
?>
<div class="alert alert-danger">This invite has over 1000 recipients so they cannot be displayed here.</div>
<?php
}
else
{
	$Invites = Invite::model()->with('store')->findAll(array(
		'condition' => 'query_id != :qid',
		'params' => array(
			':qid' => $Campaign->query_id,
		),
	));
	$previouslyInvited = array();
	
	foreach($Invites as $Invite)
	{
		$previouslyInvited[$Invite->id] = $Invite->store->email;
	}
	
	// Dupecheck
	$emails = [];
	
	if(sizeof($results))
	{
		foreach($results as $Invite)
		{
			$emails[$Invite->id] = $Invite->store->email;
		}
	}
	
	$arrayIntersect = array_intersect($emails, $previouslyInvited);
	
	foreach($arrayIntersect as $inviteID => $email)
	{
		if($inviteID == 58418)
		{
			foreach($previouslyInvited as $previousInviteID => $previouslyInvitedContact)
			{
				if($previouslyInvitedContact == $email)
				{
					print $previousInviteID . "<br />";
				}
			}
		}
	}
	
	if(count($arrayIntersect) !== 0)
	{
		print '<pre>';
		
		print_r($arrayIntersect);
		
		print '</pre>';
?>
<div class="alert alert-danger">Global duplicates detected - this invite contains contacts who have been previously invited with the same email address</div>
<?php
	}
	else
	{
?>
<div class="alert alert-success">Global duplicates test passed - no duplicates detected who have been previously invited with the same email address</div>
<?php
	}
	
	
	// Check within this invite
	$arrayCount = array_count_values($emails);
	
	if(sizeof($arrayCount) !== sizeof($emails))
	{
?>
<div class="alert alert-danger">Local duplicates detected within this invite</div>
<?php
	}
else
	{
?>
<div class="alert alert-success">Local duplicate check passed - no duplicates detected within this invite</div>
<?php
	}
?>
<p>If this invite was sent, emails would be sent to the following recipients:</p>
<table class="table">
	<tr>
		<th>Warehouse ID</th>
		<th>Email</th>
		<th>First Name</th>
		<th>Last Name</th>
		<th>Organisation</th>
	</tr>
<?php

	if(sizeof($results))
	{
		foreach($results as $Invite)
		{
?>
		<tr <?php print ($arrayCount[$Invite->store->email] > 1 || in_array($Invite->store->email, $arrayIntersect)) ? 'style="background:#f2dede;"':''; ?>>
			<td><?php print $Invite->contact_warehouse_id; ?></td>
			<td><?php print $Invite->store->email; ?></td>
			<td><?php print $Invite->store->first_name; ?></td>
			<td><?php print $Invite->store->last_name; ?></td>
			<td><?php print $Invite->store->origin_organisation_id; ?></td>
		</tr>
<?php
		}
	}

?>
</table>
<?php
}
?>