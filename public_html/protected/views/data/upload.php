<?php if ($Organisation) { ?>
<h1><?=$Organisation->title;?> - Upload CSV</h1>
<?php } else { ?>
<h1>Upload CSV</h1>
<?php } ?>

<?php

if(Yii::app()->user->hasFlash('success'))
{
?>
	<div class="alert alert-success"><?php print Yii::app()->user->getFlash('success'); ?></div>
<?php
	/*<?php print $form->error($CsvFile, 'data'); ?>*/
}
?>


<?php if (true) { ?>
	
<p class="alert alert-info">CSV uploads are currently disabled at this time.</p>

<?php } 


//if previous uploads and can only upload message the show message instead
else if (isset(Yii::app()->params['onlyAllowDataUploadOnce']) && Yii::app()->params['onlyAllowDataUploadOnce'] && $Uploads) { ?>
	<p>You have already uploaded data and cannot upload again at this time.</p>

<?php } else  { ?>

	<?php $form=$this->beginWidget('CActiveForm', array(
		'id'=>'upload-form',
		'enableAjaxValidation'=>false,
		'htmlOptions' => array('enctype' => 'multipart/form-data')
	)); ?>


	<?php echo $form->errorSummary($CsvFile, '', '', array('class' => 'alert alert-danger')); ?>
		<div class="organisation-form row">
			<div class="col-sm-6 col-md-5 col-lg-4">
				
				<div class="form-group">
					<?php print $form->labelEx($CsvFile, 'data'); ?>
					<?php print $form->fileField($CsvFile, 'data', array('class' => 'form-control')); ?>
<!-- 					<?= isset(Yii::app()->user->organisation_id) ? '<span class="help-block">' . CHtml::link('Download a file containing your required column names', array('data/structureReminder')) . '</span>' : ''; ?>
 -->				</div>

				<?php 
				//if they are part of an organisation, they can only upload to their own
				if (isset(Yii::app()->user->getUser()->organisation->id)) { ?>
					<?php echo $form->hiddenField($CsvFile,'organisation_id',array('value'=>Yii::app()->user->getUser()->organisation->id)); ?>
				<?php } else { ?>

				<div class="form-group">
				<?php
				$dropDownData = Chtml::listData(Organisation::model()->findAll("view_name != ''"), "id", "title");
			
				?>

					<?php echo $form->labelEx($CsvFile,'organisation_id', array()); ?>
					<?php echo $form->dropDownList($CsvFile,'organisation_id', $dropDownData, array('class'=>'form-control')); ?>
				</div>
				<?php } ?>

				<div class="form-actions">
					<?php print CHtml::submitButton('Upload', array('class' => 'btn')); ?>
				</div>
			

			</div>
		</div>
	<?php $this->endWidget(); ?>

<?php } ?>

<?php if($Results != null && !$CsvFile->hasErrors()): ?>
	
	<div class="row csv-results ">
		<div class="col-sm-6 col-md-5 col-lg-4">
			<p>Success!</p>
			<p><span><?php print $Results['count']; ?></span> contacts added.</p>
		</div>
	</div>

<?php endif; ?>

<?php if ($Uploads): ?>
	<h3>Upload history</h3>
	<ul>
	<?php foreach ($Uploads as $upload) { ?>
		<li><?=date(Yii::app()->params['dateFormat'],strtotime($upload->created))?></li>
	<?php } ?>
	</ul>
<?php endif; ?>
