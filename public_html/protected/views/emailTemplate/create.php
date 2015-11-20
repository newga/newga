<!-- <div class="page-header">
	<h1>Upload Email Template</h1>
</div> -->

<div class="row">
	<div class="col-md-12">
		<p>Upload a zip file of your email template. It should contain only your template.html file and images.</p>
		
	</div>
</div>


<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'form-campaign-createupdate',
	'enableAjaxValidation'=>false,
	'htmlOptions' => array('enctype' => 'multipart/form-data'),
)); ?>

<?php echo $form->errorSummary($EmailTemplate, '', '', array('class' => 'alert alert-danger')); ?>

<div class="row">
	<div class="col-md-6">

		<div class="form-group">
			<?= $form->labelEx($EmailTemplate, 'file'); ?>
			<?= $form->fileField($EmailTemplate, 'file'); ?>
		</div>

		<div class="form-actions">
			<?= CHtml::submitButton('Upload', array('class' => 'btn btn-primary email-template-upload')); ?>
		</div>

	</div>

</div>

<?php $this->endWidget(); ?>

<div class="row">
	<div class="col-md-6">
		<h3>Recipient tags you can use within your template</h3>
		<ul>
			<li>Unsubscribe link (required)<br /><pre>%recipient.insider_unsubscribe%</pre></li>
			<li>First name<br /><pre>%recipient.first_name%</pre></li>
			<li>Last name<br /><pre>%recipient.last_name%</pre></li>
			<li>Email Address<br /><pre>%recipient.email%</pre></li>
			<li>Warehouse ID<br /><pre>%recipient.warehouse_id%</pre></li>
		</ul>
<?php

if(sizeof($Outcomes))
{

?>
		<h3>Outcome url tags you can use within your template</h3>
		<ul>
<?php

	foreach($Outcomes as $Outcome)
	{

?>
			<li><?= $Outcome->name; ?><br /><pre>%recipient.outcome_<?= $Outcome->id; ?>%</pre></li>
<?php

	}

?>
		</ul>
<?php

}

?>
	</div>
</div>

<script>
//prevent user from pressing upload more than once
$(document).on('click','.email-template-upload',function() {
	$('#form-campaign-createupdate').submit();
	$(this).prop('disabled', true).val('Please wait...');
});
</script>


