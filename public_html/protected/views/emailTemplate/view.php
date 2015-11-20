<?php

if($has_ran){
	$disabledOptions = array('disabled' => 'disabled');
}
else
{
	$disabledOptions = array();
}


?>

<!-- 
<div class="page-header">
	<h1>View Template</h1>
</div> -->


<div class="row">
	<div class="col-md-12">


		<?php if(Yii::app()->user->hasFlash('success')) { ?>
		<div class="alert alert-success">
			<?php echo Yii::app()->user->getFlash('success'); ?>
		</div>
		<?php } ?>


<?php

if($testEmailInvalid === true)
{

?>
		<p class="alert alert-danger">That isn't a valid email address. Please confirm and retry.</p>
<?php

}

?>

		<?php $form=$this->beginWidget('CActiveForm', array(
			'id'=>'form-emailtemplate-delete',
			'enableAjaxValidation'=>false,
			'htmlOptions' => array(
			),
		)); ?>

		<?= CHtml::submitButton('Delete Template', array('name' => 'delete', 'class' => 'btn btn-danger delete-confirm pull-right') + $disabledOptions); ?>

		<?php $this->endWidget(); ?>

		<?php $form=$this->beginWidget('CActiveForm', array(
			'id'=>'form-emailtemplate-test',
			'enableAjaxValidation'=>false,
			'htmlOptions' => array(
			),
		)); ?>

		Send a test to <?= $form::textField($EmailTemplate, 'email_test_recipient', array('placeholder' => 'email@example.com', 'class' => '')); ?> <?= CHtml::submitButton('Send test', array('name' => 'test', 'class' => 'btn btn-primary')); ?>

		<?php $this->endWidget(); ?>


		<hr>


		<?php
		if (sizeof($EmailTemplate->noticeArray)) { ?>
		<div class="alert alert-warning ">
			<ul>
		<?php 
			foreach($EmailTemplate->noticeArray as $message) {
				echo '<li>' . $message . "</li>";
		} ?>
		 	</ul>
		</div>
		<?php } ?>

		<br />
		
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">Subject: <?=$subject;?></h3>
			</div>
			<div class="panel-body">
				<iframe width="100%" height="500" frameBorder="0"  src="data:text/html;charset=utf-8;base64,<?=base64_encode($EmailTemplate->exampleEmail);?> "></iframe>
			</div>
		</div>
		
	



	</div>



</div>


