<div class="page-header">
	<h1>Import extra emails CSV</h1>
</div>

<p>The CSV should only contain 3 columns - the Origin Unique Reference, CCR Org ID, and the email address</p>
<p>If the email is blank, it will be treated as a global unsubscribe at warehouse level</p>

<?php if(Yii::app()->user->hasFlash('success')) { ?>
	<div class="alert alert-success"><?php print Yii::app()->user->getFlash('success'); ?></div>
<?php } ?>

<?php if(Yii::app()->user->hasFlash('error')) { ?>
	<div class="alert alert-danger"><?php print Yii::app()->user->getFlash('error'); ?></div>
<?php } ?>

<?php $form=$this->beginWidget('CActiveForm', array(
		'id'=>'upload-form',
		'enableAjaxValidation'=>false,
		'htmlOptions' => array('enctype' => 'multipart/form-data')
	)); ?>
		
		<label>File</label>
		<input type="file" name="csv" />
		<hr>
		<input type="submit" class="btn btn-primary" name="import" value="Upload and Process">
<?php $this->endWidget(); ?>

<hr>

<p><?php print $noURN; ?> - No URN and cannot be processed</p>
<p><?php print $doesNotExist; ?> - does not exist</p>
<p><?php print $toBeSuppressed; ?> - exist, but will be suppressed</p>
<p><?php print $toSetEmailAddress; ?> - exist, do not have an email currently, so they will have their email set</p>
<p><?php print $toBeResubscribed; ?> - exist, and will be resubscribed</p>
<p><?php print $total; ?> - Total</p>

<hr>

<p><?php print $suppressed; ?> - Total suppressed</p>
<p><?php print $updated; ?> - Contacts updated</p>