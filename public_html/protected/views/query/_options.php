<?php

if ($Question->option_id == NULL) // No options
{

//var_dump($query_number);
?>


<div class="form-group">
	<input class="form-control query_num_input" name="<?php print $rowNumber ? 'current':'new'; ?>[query_number]<?php print $rowNumber ? '['.$rowNumber.']':''; ?>" <?php if ($query_number) echo 'value="'.$query_number.'"';?> placeholder="e.g. 35" />
	<i class="glyphicon error glyphicon-warning-sign"></i>	
</div>



<?php } else { ?>

<?php

// Get all the data we need for options.

?>

<div class="form-group">
	<select class="form-control query_option" name="<?php print $rowNumber ? 'current':'new'; ?>[query_option]<?php print $rowNumber ? '['.$rowNumber.']':''; ?>" >
			<?php 
			//we need to add the venues from database if the option chosen is VENUE
			
			switch($Question->option_id)
			{

				// Venue
				case QueryQuestion::OPTION_VENUE:

?>
				<option>Select Venue...</option>
<?php

					$Venues = Venue::model()->findAll(array('condition'=>'active = 1', 'order'=>'title ASC'));
					foreach ($Venues as $Venue)
					{
?>
				<option data-id="<?=$Venue->id;?>" value="<?=$Venue->id;?>" <?php if ($query_option==''.$Venue->id) echo 'selected="selected"'; ?>  ><?=$Venue->title;?></option>
<?php
					}
				
				break;


				// Organisation
				case QueryQuestion::OPTION_ORGANISATION:
				
?>
				<option>Select Organisation...</option>
<?php

					$Organisations = Organisation::model()->findAll(array('condition'=>'active = 1', 'order'=>'title ASC'));
					foreach($Organisations as $Organisation)
					{
?>

				<option data-id="<?=$Organisation->id;?>" value="<?=$Organisation->id;?>" <?php if ($query_option==$Organisation->id) echo 'selected="selected"'; ?>  ><?=$Organisation->title;?></option>

<?php
					}
				
				break;


				// Invite
				case QueryQuestion::OPTION_INVITE:

?>
				<option>Select...</option>
<?php

					$InviteQueries = Query::model()->findAll(array('condition'=>'invite = 1'));
					foreach ($InviteQueries as $InviteQuery)
					{
?>

				<option data-id="<?=$InviteQuery->id;?>" value="<?=$InviteQuery->id;?>" <?php if ($query_option==''.$InviteQuery->id) echo 'selected="selected"'; ?>  ><?=$InviteQuery->name;?></option>

<?php
					}
				
				break;


				// Culture Segment
				case QueryQuestion::OPTION_CS:

?>
				<option>Select Culture Segment...</option>
<?php

					$CultureSegments = CultureSegment::model()->findAll(array('index' => 'id'));
					foreach ($CultureSegments as $CultureSegment)
					{
?>

				<option data-id="<?=$CultureSegment->id;?>" value="<?=$CultureSegment->id;?>" <?php if ($query_option==''.$CultureSegment->id) echo 'selected="selected"'; ?>  ><?=$CultureSegment->name;?></option>

<?php
					}
				
				break;


				// Artforms
				case QueryQuestion::OPTION_ARTFORM:

?>
				<option>Select Artform...</option>
<?php

					$Artforms = Artform::model()->findAll(array('index' => 'id', 'order' => 'title ASC'));
					foreach ($Artforms as $Artform)
					{
?>

				<option data-id="<?=$Artform->id;?>" value="<?=$Artform->id;?>" <?php if ($query_option==$Artform->id) echo 'selected="selected"'; ?>  ><?=$Artform->title;?></option>

<?php
					}
				
				break;


				// Level of engagement
				case QueryQuestion::OPTION_LOE:
				
?>
				<option>Select Level of Engagement...</option>
<?php

					$LevelsOfEngagement = QueryQuestion::model()->levelsOfEngagement();
					foreach ($LevelsOfEngagement as $LOEid => $LOEName)
					{
?>

				<option data-id="<?=$LOEid;?>" value="<?=$LOEid;?>" <?php if ($query_option==$LOEid) echo 'selected="selected"'; ?>  ><?=$LOEName;?></option>

<?php
					}
				
				break;


				// Campaign
				case QueryQuestion::OPTION_CAMPAIGN:

?>
				<option>Select Campaign...</option>
<?php

					$Campaigns = Campaign::model()->with('query')->findAll(array(
						'condition' => 'invite = 0',
						'index' => 'id',
					));
					foreach ($Campaigns as $Campaign)
					{
?>

				<option data-id="<?=$Campaign->id;?>" value="<?=$Campaign->id;?>" <?php if ($query_option==$Campaign->id) echo 'selected="selected"'; ?>  ><?=$Campaign->name;?></option>

<?php
					}
				
				break;




				// Outcomes
				case QueryQuestion::OPTION_OUTCOME:

?>
				<option>Select Outcome...</option>
<?php

					$Campaigns = Campaign::model()->with('query')->findAll(array(
						'condition' => 'invite = 0',
						'index' => 'id',
					));
					foreach ($Campaigns as $Campaign)
					{

						// add section with campaign name
						
						if (!empty($Campaign->outcomes)) {

							?>

							<optgroup label="<?=$Campaign->name;?>">						
							
							<?php
							foreach ($Campaign->outcomes as $Outcome)
							{

	?>

							<option data-id="<?=$Outcome->id;?>" value="<?=$Outcome->id;?>" <?php if ($query_option==$Outcome->id) echo 'selected="selected"'; ?>  ><?=$Outcome->name;?></option>

	<?php
							}

							?>
							</optgroup>
						<?php
						}
					}
				
				break;





			}
?>
	</select>
</div>

<?php } ?>