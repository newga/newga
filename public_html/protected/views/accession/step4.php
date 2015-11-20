<?php print $this->renderPartial('_progressbar', array(
	'progress' => $progress,
)); ?>

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'accession-form-4',
	'enableAjaxValidation'=>false
)); ?>

<h2>Tell us their ages so we can send you the best invites</h2>
<hr>
<?php echo $form->errorSummary($Accession, '', '', array('class' => 'alert alert-danger')); ?>

<div class="form-horizontal">
<?php

for($i = 0; $i < $Accession->children; $i++)
{
?>
	<div class="form-group">
		<label class="col-sm-2 control-label">Child <?php print ($i + 1); ?> age</label> 
		<div class="col-sm-2">
		
			<input class="form-control" size="3" type="text" name="Child[<?php print $i; ?>]" value="<?php print $childAges[$i]; ?>" placeholder="Years" />
		</div>
	</div>
<?php
}

?>
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
