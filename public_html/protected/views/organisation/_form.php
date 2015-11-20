<?php


if(Yii::app()->user->hasFlash('success'))
{
?>
<div class="alert alert-success"><?php print Yii::app()->user->getFlash('success'); ?></div>
<?php
}
?>


<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'organisation-form',
	'enableAjaxValidation'=>false
)); ?>

	<?php echo $form->errorSummary($Organisation, '', '', array('class' => 'alert alert-danger')); ?>
	<div class="organisation-form row">
		<div class="col-sm-6 col-md-5 col-lg-4">
			
			<div class="form-group">
				<?php echo $form->labelEx($Organisation,'title',array()); ?>
				<?php echo $form->textField($Organisation,'title',array('class' => 'form-control')); ?>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($Organisation,'view_name',array()); ?>
				<?php echo $form->textField($Organisation,'view_name',array('class' => 'form-control')); ?>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($Organisation,'active', array()); ?>
				<?php echo $form->dropDownList($Organisation,'active',array(1 => 'Active',0 => 'Archived'),array('class'=>'form-control')); ?>
			</div>

			<div class="form-actions">
				<?php echo CHtml::submitButton($Organisation->isNewRecord ? 'Create' : 'Save', array('class' => 'btn')); ?>
			</div>
		

		</div>
	</div>
	
	

<?php $this->endWidget(); ?>

