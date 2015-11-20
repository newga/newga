<?php print $this->renderPartial('_progressbar', array(
	'progress' => $progress,
)); ?>

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'accession-form-7',
	'enableAjaxValidation'=>false
)); ?>

<h2>Tell us which forms of art you've been to and which you'd like to go.</h2>
<hr>
<?php echo $form->errorSummary($Accession, '', '', array('class' => 'alert alert-danger')); ?>

<table class="table table-bordered table-striped radio-table">
	<tr>
		<th></th>
		<th style="width:17%;">I’ve visited in past 3 years</th>
		<th style="width:17%;">I’ve visited, but not in the past 3 years</th>
		<th style="width:17%;">Never been but I would go</th>
		<th style="width:17%;">Never been and don't plan to</th>
	</tr>
<?php

foreach($Artforms as $k => $Artform)
{
	// Error?
	$errorClass = '';
	
	if(isset($_POST['Artforms']) && !array_key_exists($Artform->id, $visitedArtforms))
	{
		$errorClass = 'has-error';
	}
?>
	<tr class="<?php print $errorClass; ?>">
		<td><?php print $Artform->title; ?></td>
	<?php
	for($a = 1; $a < 5; $a++)
	{

	$b = $a;
	if ($b==1) {$b=2;}
	else if ($b==2) {$b=1;}	


?>
			<td class="center">
				<div class="table-radio <?php print $visitedArtforms[$Artform->id] == $b ? ' ticked':''; ?>">
					<label for="venue_<?php print $Artform->id; ?>_<?php print $b; ?>">
						<input id="venue_<?php print $Artform->id; ?>_<?php print $b; ?>" type="radio" name="Artforms[<?php print $Artform->id; ?>]" value="<?php print $b; ?>" <?php print $visitedArtforms[$Artform->id] == $b ? 'checked="checked"':''; ?>/>
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
<?php } }?>

</table>

<div class="form-actions">
	<div class="form-actions-col">
		<a href="/accession/<?php print $progress - 1; ?>/<?php print $Accession->accession_hash; ?>" class="btn btn-default">Previous</a><br /><span class="text-muted">(Changes on this page will not be saved)</span>
	</div>
	<div class="form-actions-col">
		<?php echo CHtml::submitButton('Finish', array('name' => 'submit-artforms', 'class' => 'btn pull-right')); ?>
	</div?
</div>

<?php $this->endWidget(); ?>
