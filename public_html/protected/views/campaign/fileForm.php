
<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'form-campaign-file-create',
	'enableAjaxValidation'=>false,
	'action' => array('campaign/fileUpload', 'id' => $Campaign->id),
	'htmlOptions' => array(
		'enctype' => 'multipart/form-data',
	),
)); ?>


<?php

// errors?
echo $form->errorSummary($CampaignFile, null, null, array('class' => 'alert alert-danger'));

?>

	<div class="row">
		<div class="col-md-6">

			<h3>Upload a pdf:</h3>
	
			<div class="form-group">
				<?= $form->label($CampaignFile, 'name'); ?>
				<?= $form->textField($CampaignFile, 'name', array('class' => 'form-control')); ?>
			</div>
	
			<div class="form-group">
				<?= $form->label($CampaignFile, 'newFile'); ?>
				<?= $form->fileField($CampaignFile, 'newFile'); ?>
			</div>
	
			<div class="form-actions">
				<?= CHtml::submitButton('Upload pdf', array('class' => 'btn btn-primary')); ?>
<?php

if(Yii::app()->controller->action->id === 'fileUpload'){

			// on file upload own page so allow cancel back to campaign. Be excellent.
			echo '&nbsp&nbsp;&nbsp;' . CHtml::link('cancel', array('campaign/createUpdate', 'id' => $Campaign->id));

}

?>
			</div>
		</div>
	</div>

<?php $this->endWidget(); ?>