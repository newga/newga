<div class="page-header">
	<h1>Create User</h1>
</div>

<div class="alert alert-info">
	<p>Once created, the new user will receive an email inviting them to visit <?php print Yii::app()->name; ?> and set a password</p>
</div>

<?php echo $this->renderPartial('_form', array('User'=>$User,'formDetail'=> $formDetail)); ?>
