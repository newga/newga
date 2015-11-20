<?php


if(Yii::app()->user->hasFlash('success'))
{
?>
<div class="alert alert-success"><?php print Yii::app()->user->getFlash('success'); ?></div>
<?php
}
?>


<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'Venue-form',
	'enableAjaxValidation'=>false
)); ?>

	<?php echo $form->errorSummary($Venue, '', '', array('class' => 'alert alert-danger')); ?>
	<div class="Venue-form row">
		<div class="col-sm-6 col-md-5 col-lg-4">
			
			<div class="form-group">
				<?php echo $form->labelEx($Venue,'title',array()); ?>
				<?php echo $form->textField($Venue,'title',array('class' => 'form-control')); ?>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($Venue,'active', array()); ?>
				<?php echo $form->dropDownList($Venue,'active',array(1 => 'Active',0 => 'Archived'),array('class'=>'form-control')); ?>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($Venue,'organisation_id',array()); ?>
				
				<?php $options = Venue::model()->organisationOptions(); ?>
				<?php echo $form->dropDownList($Venue,'organisation_id', $options, array('class' => 'form-control')); ?>	
			</div>

			<div class="form-actions">
				<?php echo CHtml::submitButton($Venue->isNewRecord ? 'Create' : 'Save', array('class' => 'btn')); ?>
			</div>
		

		</div>
	</div>
	
	

<?php $this->endWidget(); ?>

