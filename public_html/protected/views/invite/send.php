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



<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'form-campaign-createupdate',
	'enableAjaxValidation'=>false,
	'htmlOptions' => array(
		'style' => 'min-height: 400px;'
	),
)); ?>



<p><strong>Subject:</strong> <?php print $Campaign->invite_email_subject; ?></p>

<p>
	<strong>Body:</strong><br />
	<?php print nl2br($Campaign->invite_email_body); ?>
</p>
	



		<div class="form-actions">
<?= ($Campaign->hasErrors() ? '<p>' . CHtml::errorSummary($Campaign, null, null, array('class' => 'alert alert-danger')) . '</p>' : ''); ?>
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
			<?= CHtml::submitButton('Send invitations to ' . $Campaign->query->num_contacts . ' contacts', array('name' => 'send', 'class' => 'btn btn-primary pull-right', 'id' => 'sendInvites', 'data-count' => $Campaign->query->num_contacts)); ?> 
<?php
}
?>
			
			Send a test to <?= $form::textField($Campaign, 'email_test_recipient', array('placeholder' => 'email@example.com', 'class' => '')); ?> <?= CHtml::submitButton('Send test', array('name' => 'test', 'class' => 'btn btn-primary')); ?>
			
			
		</div>

	

<?php $this->endWidget(); ?>

<?php
}
?>