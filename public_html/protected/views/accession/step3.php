<?php print $this->renderPartial('_progressbar', array(
	'progress' => $progress,
)); ?>

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'accession-form-3',
	'enableAjaxValidation'=>false,
	'focus' => array($Accession, 'children'),
)); ?>

<h2>Some invites will be for kids and families. Tell us how many kids you might ever bring.</h2>
<p>We'll then ask for each of their ages, so we can send you the best invites</p>
<hr>
<?php echo $form->errorSummary($Accession, '', '', array('class' => 'alert alert-danger')); ?>

<div class="row">
	<div class="col-sm-12 form-inline">
		<div class="buttons">

<?php

for($i = 0; $i < 6; $i++)
{
?>
			<label for="input<?php print $i; ?>" class="<?php print $Accession->children == $i ? 'ticked':''; ?>">
				<input id="input<?php print $i; ?>" type="radio" name="Accession[children]" value="<?php print $i; ?>" <?php print $Accession->children == $i ? 'checked="checked"':''; ?> />
				<span class="tick">
					<?php print $i; ?><?php print $i == 5 ? '+':''; ?>
				</span>
			</label>
<?php
}

?>
		</div>
	</div>
</div>

<div class="form-actions">
	<div class="form-actions-col">
		<a href="/accession/<?php print $progress - 1; ?>/<?php print $Accession->accession_hash; ?>" class="btn btn-default">Previous</a><br /><span class="text-muted">(Changes on this page will not be saved)</span>
	</div>
	<div class="form-actions-col">
		<?php echo CHtml::submitButton('Next', array('class' => 'btn pull-right')); ?>
	</div>
</div>

<?php $this->endWidget(); ?>
