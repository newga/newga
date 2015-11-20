<div class="page-header">
	<h1>Invite <em><?php print $Campaign->name; ?></em></h1>
</div>

<?php

$this->renderPartial('_tabs_before_send', array('Campaign' => $Campaign));


/* show invite options and statistics for an invite */

if($Campaign->status == Campaign::STATUS_HAS_BEEN_RUN)
{
?>
<div class="alert alert-warning">This invite has been sent and cannot be edited</div>
<?php
}
else
{
	foreach(Yii::app()->user->getFlashes() as $state => $message){

		?><p class="alert alert-<?= $state; ?>"><?= $message; ?></p><?php
	}

?>

<div class="row">
	<div class="col-md-10">
		<p>Add the subject and body content of the invitation email to send to each contact.</p>
		<p>For each organisation, this content will be added to a themed template to match that organisation's branding.</p>

	</div>
</div>

<hr>

<?= ($Campaign->hasErrors() ? '<p>' . CHtml::errorSummary($Campaign, null, null, array('class' => 'alert alert-danger')) . '</p>' : ''); ?>

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'form-campaign-createupdate',
	'enableAjaxValidation'=>false
)); ?>

<div class="row">
	<div class="col-md-8">

		<div class="form-group">
			<?= CHtml::label('Email Subject', ''); ?>
			<?= $form->textField($Campaign, 'invite_email_subject', array('class' => 'form-control')); ?>
		</div>


		<div class="form-group">
			<?= CHtml::label('Email Body', ''); ?>
			<?= $form->textArea($Campaign, 'invite_email_body', array('rows' => 15, 'class' => 'form-control')); ?>
			<span class="help-block">Template tags available:<br />
			[[firstname]]<br />
			[[lastname]]<br />
			[[invitelink]] - required</span>
		</div>

	</div>

</div>

		<div class="form-actions">
<?php

if(!($Campaign->query->num_contacts))
{
?>
			<p class="alert alert-danger">Cannot send invitation emails - there are no contacts in this invite query</p>
<?php
}
else
{
?>
			<?= CHtml::submitButton('Save invite email content', array('name' => 'send', 'class' => 'btn btn-primary pull-right')); ?> 
<?php
}
?>
			<input type="submit" class="btn btn-danger delete-confirm" value="Delete Invite" name="delete" title="Delete" />
			
			
		</div>

	

<?php $this->endWidget(); ?>

<?php
}
?>