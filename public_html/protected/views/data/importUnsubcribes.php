<div class="page-header">
	<h1>Import unsubscribe CSV</h1>
</div>

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
		<label>Organisation</label><br />
		<?php print CHtml::dropDownList('organisation_id', $_POST['organisation_id'], CHtml::listData(Organisation::model()->findAll(array('condition' => 'id != 10')), 'id', 'title'), array('prompt' => '-- Choose --')); ?>
		<hr>
		
		
		<input type="submit" class="btn btn-primary" name="import" value="Upload and Process">
<?php $this->endWidget(); ?>

