<?php

if($Campaign->hasBeenRun){
	$disabledOptions = array('disabled' => 'disabled');
}
else
{
	$disabledOptions = array();
}


?>
<div class="page-header">
	<h1><?= 'Update Group ' . CHtml::encode($CampaignGroup->name); ?></h1>
</div>
<?= ($CampaignGroup->hasErrors() ? '<p>' . CHtml::errorSummary($CampaignGroup, null, null, array('class' => 'alert alert-danger')) . '</p>' : ''); ?>

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'form-campaigngroup-update',
	'enableAjaxValidation'=>false
)); ?>

<div class="row">
	<div class="col-md-6">
		<h3>Group Details</h3>
		<hr>
		<div class="form-group">
			<?= $form->labelEx($CampaignGroup, 'name'); ?>
			<?= $form->textField($CampaignGroup, 'name', array('class' => 'form-control') + $disabledOptions); ?>
		</div>

		<div class="form-group">
			<?= $form->labelEx($CampaignGroup, 'description'); ?>
			<?= $form->textArea($CampaignGroup, 'description', array('class' => 'form-control') + $disabledOptions); ?>
		</div>

		<div class="form-group">
			<?= $form->labelEx($CampaignGroup, 'subject'); ?>
			<?= $form->textField($CampaignGroup, 'subject', array('class' => 'form-control') + $disabledOptions); ?>
		</div>

		<div class="form-actions<?= $Campaign->hasBeenRun ? ' hide' : ''; ?>">
			<?= CHtml::submitButton('Update', array('class' => 'btn btn-primary')); ?>
			<?= CHtml::link('cancel', array('update', 'campaign_id' => $CampaignGroup->campaign_id, 'id' => $CampaignGroup->id), array('class' => 'btn btn-default')); ?>
		</div>

	</div>

</div>

<br />
<div class="row">
	<div class="col-md-12">

<?php

	$this->renderPartial('//emailTemplate/create',
		array('EmailTemplate'=>$EmailTemplate, 
		'Outcomes' => $CampaignGroup->campaign->outcomes
	));

?>
	</div>

</div>

<!-- <div class="col-md-6">
	<h3>Email Template</h3>
	<hr>
	
	<?php if (!$CampaignGroup->email_template) { ?>
		<?= CHtml::link('Add Template', array('emailTemplate/create', 'campaign_id' => $CampaignGroup->campaign_id, 'group_id' => $CampaignGroup->id), array('class' => 'btn btn-primary') + $disabledOptions); ?>
	<?php } else { ?> 
		<?= CHtml::link('View Template', array('emailTemplate/view', 'template_id' => $CampaignGroup->email_template->id, 'campaign_id'=>$CampaignGroup->campaign_id,'group_id'=>$CampaignGroup->id), array('class' => 'btn btn-primary')); ?>
	<?php } ?>
	</div>
</div> -->

<?php $this->endWidget(); ?>
