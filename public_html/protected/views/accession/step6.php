<?php print $this->renderPartial('_progressbar', array(
	'progress' => $progress,
)); ?>

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'accession-form-6',
	'enableAjaxValidation'=>false
)); 

?>

<h2>Tell us where you've been and where you'd like to go.</h2>
<hr>
<?php echo $form->errorSummary($Accession, '', '', array('class' => 'alert alert-danger')); ?>

<table class="table table-bordered table-striped venue-table radio-table">
	<tr>
		<th></th>
		<th style="width:17%;">I’ve visited in past 3 years</th>
		<th style="width:17%;">I’ve visited, but not in the past 3 years</th>
		<th style="width:17%;">Never been but I would go</th>
		<th style="width:17%;">Never been and don't plan to</th>
	</tr>
<?php



foreach($Venues as $k => $Venue)
{
	// Error?
	$errorClass = '';
	
	if(isset($_POST['Venues']) && !array_key_exists($Venue->id, $visitedVenues))
	{
		$errorClass = 'has-error';
	}
	
?>
	<tr class="<?php print $errorClass; ?>">
		<td><?php print $Venue->title; ?></td>
<?php
	for($a = 1; $a < 5; $a++)
	{
		$b = $a;
		if ($b==1) {$b=2;}
		else if ($b==2) {$b=1;}


?>
			<td class="center">
				<div class="table-radio <?php print $visitedVenues[$Venue->id] == $b ? ' ticked':''; ?>">
					<label for="venue_<?php print $Venue->id; ?>_<?php print $b; ?>">
						<input id="venue_<?php print $Venue->id; ?>_<?php print $b; ?>" type="radio" name="Venues[<?php print $Venue->id; ?>]" value="<?php print $b; ?>" <?php print $visitedVenues[$Venue->id] == $b ? 'checked="checked"':''; ?>/>
						<span class="tick">
							<img src="/css/assets/pink-tick-90x90.png" height="45" alt="">
						</span>
					</label>
				</div>
			</td>
<?php
	}
?>
		
	</tr>
	
<?php
	// Every 5, print the header row again
	if((($k+1) % 5) === 0)
	{
?>
	<tr>
		<th></th>
		<th style="width:17%;">I’ve visited in past 3 years</th>
		<th style="width:17%;">I’ve visited, but not in the past 3 years</th>
		<th style="width:17%;">Never been but I would go</th>
		<th style="width:17%;">Never been and don't plan to</th>
	</tr>
<?php
	}
}

?>
</table>

<div class="form-actions">
	<div class="form-actions-col">
		<a href="/accession/<?php print $progress - 1; ?>/<?php print $Accession->accession_hash; ?>" class="btn btn-default">Previous</a><br /><span class="text-muted">(Changes on this page will not be saved)</span>
	</div>
	<div class="form-actions-col">
		<?php echo CHtml::submitButton('Next', array('name' => 'submit-venues', 'class' => 'btn pull-right')); ?>
	</div>
</div>

<?php $this->endWidget(); ?>
