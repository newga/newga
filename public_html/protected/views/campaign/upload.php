<?php

/*

View to allow campaign outcome upload

*/


$dropDownData = CHtml::listData(Organisation::model()->findAll("view_name != ''"), "id", "title");

// loop good and back flashes and show them.
foreach(Yii::app()->user->getFlashes() as $state => $message){

	?><p class="alert alert-<?= $state; ?>"><?= $message; ?></p><?php
}

?>
	<div class="page-header">
		<h2>Upload Campaign Contacts and Outcomes</h2>
	</div>
	<p>Upload a csv containing manual outcome information for campaign contacts for the campaign "<?= $Campaign->name; ?>".</p>
	<p>The format should match the campaign snapshot download file and each row must contain a campaign_contact_id and <span style="font-family:courier;background:#ddd;padding:0 4px;">dd/mm/yyyy hh:mm</span> or <span style="font-family:courier;background:#ddd;padding:0 4px;">yyyy-mm-dd hh:mm</span> style datetime strings for those outcomes to be marked as complete.</p>
	<p>Only outcome data will be recorded and only when a date time is supplied.</p>
<pre>
campaign_contact_id | outcome_44 - Name of outcome | outcome_45 - Second outcome name | outcome_46 - Outcome 3
123                 | 2015-04-02 10:34             | 02/04/15 12:55                   | 03-04-15 19:34
</pre>
<hr>
<h2>Upload results csv</h2>
<?php $form=$this->beginWidget('CActiveForm', array(
	'id' => 'form-upload-outcomes',
	'enableAjaxValidation' => false,
	'htmlOptions' => array('enctype' => 'multipart/form-data')
)); ?>

<?php echo $form->errorSummary($CampaignOutcomesFile, '', '', array('class' => 'alert alert-danger')); ?>

	<div class="form-group">
		<label><?= $Campaign->name . ' results file:'; ?></label>
		<?php print $form->fileField($CampaignOutcomesFile, 'file', array('class' => 'form-control')); ?>
	</div>
<?php 

//if they are part of an organisation, they can only upload to their own

/*if (isset(Yii::app()->user->getUser()->organisation->id)) {

	echo $form->hiddenField($CampaignOutcomesFile,'organisation_id',array('value' => Yii::app()->user->getUser()->organisation->id));

} else {

	<div class="form-group">
		< ? php echo $form->labelEx($CampaignOutcomesFile, 'organisation_id', array()); ? >
		< ? php echo $form->dropDownList($CampaignOutcomesFile, 'organisation_id', $dropDownData, array('class' => 'form-control')); ? >
	</div>
< ? php

}*/

?>

	<div class="">
		<?php print CHtml::submitButton('Upload', array('class' => 'btn')); ?>
	</div>

<?php $this->endWidget(); ?>
