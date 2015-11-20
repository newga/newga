
<h1>Upload CSV from the cleaning company</h1>

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


	<?php echo $form->errorSummary($CleaningFile, '', '', array('class' => 'alert alert-danger')); ?>
		<div class="organisation-form row">
			<div class="col-sm-6 col-md-5 col-lg-4">
				
				<div class="form-group">
					<?php print $form->labelEx($CleaningFile, 'data'); ?>
					<?php print $form->fileField($CleaningFile, 'data', array('class' => 'form-control')); ?>
				</div>

				<div class="form-actions">
					<?php print CHtml::submitButton('Upload', array('class' => 'btn')); ?>
				</div>
			

			</div>
		</div>
<?php $this->endWidget(); ?>


<?php if ($Uploads): ?>
	<h3>Upload history</h3>
	<ul>
	<?php foreach ($Uploads as $upload) { ?>
		<li><?=date(Yii::app()->params['dateFormat'],strtotime($upload->created))?></li>
	<?php } ?>
	</ul>
<?php endif; ?>
