<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'accession-form-2',
	'enableAjaxValidation'=>false,
	'focus' => array($Accession, 'salutation')
)); ?>


<?php //echo $form->errorSummary(array($Accession,$Store), '', '', array('class' => 'alert alert-danger')); 

if(sizeof($Store->errors) || sizeof($Accession->errors))
{
?>
<div class="alert alert-danger">
	There were some errors with your details
</div>
<?php
}

?>
<div class="row">
	<div class="col-sm-6 form-horizontal">
		<div class="form-group">
			<?php echo $form->labelEx($Store,'salutation',array('class' => 'col-sm-3 control-label')); ?>
			<div class="col-sm-9">
				<?php echo $form->dropDownList($Store,'salutation',$Store->salutations,array('class' => 'form-control', 'prompt' => '-- Choose --')); ?>
			</div>
		</div>
		<div class="form-group<?php print $Store->hasErrors('first_name') ? ' has-error':''; ?>">
			<?php echo $form->labelEx($Store,'first_name',array('class' => 'col-sm-3 control-label')); ?>
			<div class="col-sm-9">
				<?php echo $form->textField($Store,'first_name',array('class' => 'form-control', 'placeholder' => 'Your first name')); ?>
<?php

if($Store->hasErrors('first_name'))
{
?>
				<span class="help-block"><?php print implode('<br>',$Store->getErrors('first_name')); ?></span>
<?php
}
?>
			</div>
		</div>
		<div class="form-group<?php print $Store->hasErrors('last_name') ? ' has-error':''; ?>">
			<?php echo $form->labelEx($Store,'last_name',array('class' => 'col-sm-3 control-label')); ?>
			<div class="col-sm-9">
				<?php echo $form->textField($Store,'last_name',array('class' => 'form-control', 'placeholder' => 'Your last name')); ?>
<?php

if($Store->hasErrors('last_name'))
{
?>
				<span class="help-block"><?php print implode('<br>',$Store->getErrors('last_name')); ?></span>
<?php
}
?>
			</div>
		</div>
		<div class="form-group<?php print $Store->hasErrors('email') ? ' has-error':''; ?>">
			<?php echo $form->labelEx($Store,'email',array('class' => 'col-sm-3 control-label')); ?>
			<div class="col-sm-9">
				<?php echo $form->emailField($Store,'email',array('class' => 'form-control', 'placeholder' => 'eg. email@domain.com')); ?>
<?php

if($Store->hasErrors('email'))
{
?>
				<span class="help-block"><?php print implode('<br>',$Store->getErrors('email')); ?></span>
<?php
}
?>
			</div>
		</div>
		<div class="form-group<?php print $Store->hasErrors('mobile') ? ' has-error':''; ?>">
			<?php echo $form->labelEx($Store,'mobile',array('class' => 'col-sm-3 control-label')); ?>
			<div class="col-sm-9">
				<?php echo $form->textField($Store,'mobile',array('class' => 'form-control', 'placeholder' => 'eg. 07777 123 456')); ?>
				<p class="help-block">...because some of the best invites will come by text</p>
			</div>
		</div>
		<div class="form-group<?php print $Store->hasErrors('phone') ? ' has-error':''; ?>">
			<?php echo $form->labelEx($Store,'phone',array('class' => 'col-sm-3 control-label')); ?>
			<div class="col-sm-9">
				<?php echo $form->textField($Store,'phone',array('class' => 'form-control', 'placeholder' => 'eg. 0191 123 456')); ?>
				<p class="help-block">...if you don't have a mobile.</p>
			</div>
		</div>
		<div class="form-group<?php print $Accession->hasErrors('age') ? ' has-error':''; ?>">
			<?php echo $form->labelEx($Accession,'age',array('class' => 'col-sm-3 control-label')); ?>
			<div class="col-sm-9">
				<?php echo $form->textField($Accession,'age',array('class' => 'form-control', 'placeholder' => 'eg. 45')); ?>
<?php

if($Accession->hasErrors('age'))
{
?>
				<span class="help-block"><?php print implode('<br>',$Accession->getErrors('age')); ?></span>
<?php
}
else
{
?>
				<p class="help-block">...because some invites will involve alcohol.</p>
<?php
}
?>
			</div>
		</div>
	</div>
	<div class="col-sm-6 form-horizontal<?php print $Store->hasErrors('address_line_1') ? ' has-error':''; ?>">
		<div class="form-group">
			<?php echo $form->labelEx($Store,'address_line_1',array('class' => 'col-sm-3 control-label')); ?>
			<div class="col-sm-9">
				<?php echo $form->textField($Store,'address_line_1',array('class' => 'form-control', 'placeholder' => 'Address line 1')); ?>
<?php

if($Store->hasErrors('address_line_1'))
{
?>
				<span class="help-block"><?php print implode('<br>',$Store->getErrors('address_line_1')); ?></span>
<?php
}
?>
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-9 pull-right">
				<?php echo $form->textField($Store,'address_line_2',array('class' => 'form-control', 'placeholder' => 'Address line 2')); ?>
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-9 pull-right">
				<?php echo $form->textField($Store,'address_line_3',array('class' => 'form-control', 'placeholder' => 'Address line 3')); ?>
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-9 pull-right">
				<?php echo $form->textField($Store,'address_line_4',array('class' => 'form-control', 'placeholder' => 'Address line 4')); ?>
			</div>
		</div>
		<div class="form-group<?php print $Store->hasErrors('address_town') ? ' has-error':''; ?>">
			<?php echo $form->labelEx($Store,'address_town',array('class' => 'col-sm-3 control-label')); ?>
			<div class="col-sm-9">
				<?php echo $form->textField($Store,'address_town',array('class' => 'form-control', 'placeholder' => 'eg. Gateshead')); ?>
			</div>
		</div>
		<div class="form-group<?php print $Store->hasErrors('address_postcode') ? ' has-error':''; ?>">
			<?php echo $form->labelEx($Store,'address_postcode',array('class' => 'col-sm-3 control-label')); ?>
			<div class="col-sm-9">
				<?php echo $form->textField($Store,'address_postcode',array('class' => 'form-control', 'placeholder' => 'eg. NE8 2JR')); ?>
<?php

if($Store->hasErrors('address_postcode'))
{
?>
				<span class="help-block"><?php print implode('<br>',$Store->getErrors('address_postcode')); ?></span>
<?php
}
?>
			</div>
		</div>
		<div class="form-group">
			<?php echo $form->labelEx($Store,'address_county',array('class' => 'col-sm-3 control-label')); ?>
			<div class="col-sm-9">
				<?php echo $form->dropDownList($Store,'address_county',$Store->counties,array('class' => 'form-control', 'prompt' => '-- Choose --')); ?>
			</div>
		</div>
	</div>
</div>

<div class="acc-panel">
<?php

if($passwordIsSet)
{
?>
<p>You've already set a password. Enter a new password below if you wish to change it.</p>
<?php
}
else
{
?>
<p>Optionally add a password, so you can log in and update your details in the future.</p>
<?php
}
?>
	
	<div class="row">
		<div class="col-sm-6 form-horizontal<?php print $Accession->hasErrors('password') ? ' has-error':''; ?>">
			<?php echo $form->labelEx($Accession,'password',array('class' => 'col-sm-3 control-label')); ?>
			<div class="col-sm-7">
				<?php echo $form->passwordField($Accession,'password',array('class' => 'form-control','autocomplete' => 'off')); ?>
<?php

if($Accession->hasErrors('password'))
{
?>
				<span class="help-block"><?php print implode('<br>',$Accession->getErrors('password')); ?></span>
<?php
}
?>
			</div>
		</div>
		<div class="col-sm-6 form-horizontal<?php print $Accession->hasErrors('password_repeat') ? ' has-error':''; ?>">
			<?php echo $form->labelEx($Accession,'password_repeat',array('class' => 'col-sm-5 control-label')); ?>
			<div class="col-sm-7">
				<?php echo $form->passwordField($Accession,'password_repeat',array('class' => 'form-control','autocomplete' => 'off')); ?>
<?php

if($Accession->hasErrors('password_repeat'))
{
?>
				<span class="help-block"><?php print implode('<br>',$Accession->getErrors('password_repeat')); ?></span>
<?php
}
?>
			</div>
		</div>
	</div>
</div>

<div class="form-actions">
<?php

if(isset($updateDetails) && $updateDetails)
{
?>
	<?php echo CHtml::submitButton('Save Details', array('class' => 'btn pull-right')); ?>
<?php
}
else
{
?>
	<?php echo CHtml::submitButton('Next', array('class' => 'btn pull-right')); ?>
<?php
}
?>
</div>

<?php $this->endWidget(); ?>