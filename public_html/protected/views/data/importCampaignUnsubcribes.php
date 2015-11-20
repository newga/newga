<div class="page-header">
	<h1>Import campaign unsubscribe CSV</h1>
</div>

<p>Not to be used to record <?= CHtml::link('invite unsubscribes', array('data/importUnsubcribes')); ?>.</p>

<p>The CSV should only contain 1 column - the email address</p>

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

