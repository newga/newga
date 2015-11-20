<h1>Login</h1>
<hr>
<div>
<?php $form=$this->beginWidget('CActiveForm', array(
	'enableClientValidation'=>true,
	'focus' => array($LoginForm, 'email'),
	'clientOptions'=>array(
		'validateOnSubmit'=>true,
		),
)); ?>
<?php

if(Yii::app()->user->hasFlash('success'))
{
?>
	<div class="alert alert-success">
		<p><?php print Yii::app()->user->getFlash('success'); ?></p>
	</div>
<?php
}

?>
	<?php echo $form->errorSummary($LoginForm, '', '', array('class' => 'alert alert-danger')); ?>
	<div class="login-form row">
		<div class="col-sm-6 col-md-5 col-lg-4">
			<div class="form-group">
				<?php echo $form->labelEx($LoginForm,'email',array()); ?>
				<?php echo $form->textField($LoginForm,'email',array('class'=>'form-control')); ?>
			</div>
		
			<div class="form-group">
				<?php echo $form->labelEx($LoginForm,'password',array()); ?>
				<?php echo $form->passwordField($LoginForm,'password',array('class'=>'form-control','autocomplete' => 'off')); ?>
			</div>
		
			<div class="form-actions" id="login-submit">
				<?php echo CHtml::submitButton('Login', array('class' => 'btn pull-right')); ?>
			</div>
			<p class="center"><a href="/reset-password">Forgotten your password?</a></p>
		</div>
	</div>
<?php $this->endWidget(); ?>

</div><!-- form -->
