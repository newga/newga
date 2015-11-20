<?php

/*

View to allow campaign outcome upload

*/

// collect up manual outcomes
$Outcomes = array();

// everyone has to choose a specific manual outcome
foreach($Campaign->outcomes as $Outcome){

	// skip auto outcomes
	if(strlen($Outcome->url)) continue;

	$Outcomes[] = $Outcome;

}

// loop good and back flashes and show them.
foreach(Yii::app()->user->getFlashes() as $state => $message){

	?><p class="alert alert-<?= $state; ?>"><?= $message; ?></p><?php
}

?>
	<div class="page-header">
		<h2>Upload Manual Campaign Outcome Contacts</h2>
	</div>
	<p>Upload a csv containing unique user ids of contacts for the campaign "<?= $Campaign->name; ?>".</p>
<hr>
<h2>Upload results csv</h2>

<div class="row">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id' => 'form-upload-outcomes',
	'enableAjaxValidation' => false,
	'htmlOptions' => array(
		'enctype' => 'multipart/form-data',
		'class' => 'col-sm-6',
	)
)); ?>

<?php

if(Yii::app()->user->role >= User::ROLE_MANAGER)
{
	// have to choose organisation
?>
	<div class="form-group">
		<label><?= 'Organisation contacts belong to:'; ?></label>
		<?= CHtml::dropDownList('organisation_id', null, CHtml::listData(Organisation::model()->findAll(array('condition' => "view_name != 'Store'", 'order' => 'title')), 'id', 'title'), array('class' => 'form-control', (sizeof($Outcomes) ? 'not' : '') . 'disabled' => 'disabled')); ?>
	</div>
<?php

} // if manager or super

?>
	<div class="form-group">
		<label><?= 'Manual outcome to update:'; ?></label>
		<?= CHtml::dropDownList('outcome_id', null, CHtml::listData($Outcomes, 'id', 'name'), array('class' => 'form-control', (sizeof($Outcomes) ? 'not' : '') . 'disabled' => 'disabled')); ?>
	</div>

	<div class="form-group">
		<label><?= 'User id csv file:'; ?></label>
		<?= CHtml::fileField('file', '', array('class' => 'form-control', (sizeof($Outcomes) ? 'not' : '') . 'disabled' => 'disabled')); ?>
	</div>

	<div class="">
		<?= CHtml::submitButton('Upload', array('class' => 'btn btn-primary', (sizeof($Outcomes) ? 'not' : '') . 'disabled' => 'disabled')); ?> <?= CHtml::link('cancel', array('campaign/result', 'id' => $Campaign->id), array('class' => 'btn')); ?>
	</div>

<?php $this->endWidget(); ?>

</div>
