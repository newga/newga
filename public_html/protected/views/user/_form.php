<?php if(Yii::app()->user->hasFlash('success')) { ?>
<div class="alert alert-success"><?php print Yii::app()->user->getFlash('success'); ?></div>
<?php } ?>



<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'organisation-form',
	'enableAjaxValidation'=>false
)); ?>

	<?php echo $form->errorSummary($User, '', '', array('class' => 'alert alert-danger')); ?>
	<div class="user-form row">
		<div class="col-sm-6 col-md-5 col-lg-4">
			
			<div class="form-group">
				<?php echo $form->labelEx($User,'first_name',array()); ?>
				<?php echo $form->textField($User,'first_name',array('class' => 'form-control')); ?>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($User,'last_name',array()); ?>
				<?php echo $form->textField($User,'last_name',array('class' => 'form-control')); ?>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($User,'email',array()); ?>
				<?php echo $form->textField($User,'email',array('class' => 'form-control')); ?>
			</div>

			

<?php

if($formDetail == 'full')
{
?>
			<div class="form-group">
				<?php echo $form->labelEx($User,'role',array()); ?>
				
				<?php $options = User::model()->roleOptions(); ?>
				<?php echo $form->dropDownList($User,'role', $options, array('class' => 'form-control')); ?>	
			</div>
			
			<div class="form-group organisation">
				<?php echo $form->labelEx($User,'organisation_id',array()); ?>
				
				<?php $options = User::model()->organisationOptions(); ?>
				<?php echo $form->dropDownList($User,'organisation_id', $options, array('class' => 'form-control')); ?>	
			</div>
<?php
}
?>

			<?php if ((!$User->isNewRecord && Yii::app()->user->id == $User->id)){ ?>

			<?php if (!$User->isNewRecord) { ?>
			<div class="form-group">If you wish to update your password, please enter below.</div>
			<?php } ?>
			<div class="form-group">
				<?php echo $form->labelEx($User,'password1',array()); ?>
				<?php echo $form->passwordField($User,'password1',array('class' => 'form-control','autocomplete' => 'off')); ?>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($User,'password2',array()); ?>
				<?php echo $form->passwordField($User,'password2',array('class' => 'form-control','autocomplete' => 'off')); ?>
			</div>


			<?php } ?>




			<div class="form-actions">
				<?php echo CHtml::submitButton($User->isNewRecord ? 'Create' : 'Save', array('class' => 'btn btn-primary')); ?>
			</div>

<?php if(!$User->isNewRecord && Yii::app()->user->role >= User::ROLE_MANAGER) { ?>
			<div class="form-actions">
				<h2>Archive <?= CHtml::encode($User->first_name); ?></h2>
				<p>By archiving <?= CHtml::encode($User->first_name); ?> you will retain all information about the actions they have taken, but they will no longer be able to access the site.</p>
				<?= CHtml::submitButton('I would like to archive ' . $User->first_name, array('name' => 'archive', 'class' => 'btn btn-danger', 'confirm' => 'Are you sure you want to archive ' . CHtml::encode($User->fullName) . "?\n\nThis action cannot currently be reversed.")); ?>
			</div>
<?php } ?>
		</div>
	</div>
	
	

<?php $this->endWidget(); ?>

