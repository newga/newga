
	<div class="primary article">
		<div class="reset-pass">
			<div class="page-header">
				<h1>Reset your password</h1>
			</div>


<?php

	if($PasswordResetForm->hasErrors()) {

?>
<?= CHtml::errorSummary($PasswordResetForm, '', '', array('class' => 'alert alert-danger')); ?>
<?php

	}

if(!$_GET['hash']) {
?>
			
<?php
	if(!$_POST['PasswordResetForm']['email'])
	{

?>
			<?php $form=$this->beginWidget('CActiveForm', array(
				'enableClientValidation'=>true,
				'focus' => array($PasswordResetForm, 'email'),
				'clientOptions'=>array(
					'validateOnSubmit'=>true,
					),
			)); ?>
				<div class="row">
					<div class="col-sm-12">
						<p class="alert alert-info">Enter your email address and click Reset. We will send you an email with a link which will allow you to create a new password. By sending an email to you, we can be sure it's really you.</p>
						
		
					</div>
				</div>
				<div class="reset-form row">
					<div class="col-sm-6">
						<div class="form-group">
							
							<?php echo $form->labelEx($PasswordResetForm,'email',array()); ?>
							
							<?php echo $form->textField($PasswordResetForm,'email',array('class'=>'form-control')); ?>
							
						</div>
			
			
						<div class="form-actions" id="reset-submit">
							
							<?php echo CHtml::submitButton('Reset', array('class' => 'btn')); ?>
							
						</div>
					</div>
				</div>
			<?php $this->endWidget(); ?>

<?php

	}
	else
	{

?>
			<div class="row">
				<div class="col-sm-12">
					<div class=" alert alert-success">
						<h2 style="margin-top: 0;">Thanks!</h2>
						<p>Check your email inbox. If that email exists in the system then we've sent you a link which will allow you to pick a new password.</p>
					</div>
				</div>
			</div>
<?php

	}
}
else
{
	if(!$_POST['PasswordResetForm']['password'])
	{
?>
		
			<div class="col-sm-12 alert alert-success">
				<h2 style="margin-top: 0;">Thanks!</h2>
				<p>Now you can create a new password for your account.</p>
			</div>
<?php
	}
?>
			<div class="row">
				<div class="col-sm-6">
					<?php $form=$this->beginWidget('CActiveForm', array(
						'enableClientValidation'=>true,
						'focus' => array($PasswordResetForm, 'password'),
						'clientOptions'=>array(
							'validateOnSubmit'=>true,
							),
					)); ?>


						<div class="form-group">
							<?php echo $form->labelEx($PasswordResetForm,'password',array()); ?>
							<?php echo $form->passwordField($PasswordResetForm,'password',array('class'=>'form-control','autocomplete' => 'off')); ?>
						</div>
						<div class="form-group">
							<?php echo $form->labelEx($PasswordResetForm,'password_repeat',array()); ?>
							<?php echo $form->passwordField($PasswordResetForm,'password_repeat',array('class'=>'form-control','autocomplete' => 'off')); ?>
						</div>
					
						<div class="form-actions" id="reset-submit">
							<?php echo CHtml::submitButton('Submit', array('class' => 'btn')); ?>
						</div>
					<?php $this->endWidget(); ?>
				</div>
			</div>

<?php

}

?>
		</div>
	</div>