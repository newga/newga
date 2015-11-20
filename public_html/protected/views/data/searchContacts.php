<div class="page-header">
	<h1>Search Contacts</h1>
</div>

<div style="min-height: 500px">
	<div class="row">
		<div class="col-md-6">
	<?php $form=$this->beginWidget('CActiveForm', array(
		'id'=>'search-form',
		'enableAjaxValidation'=>false,
	)); ?>
	
		<h4>Decrypt Email</h4>
		<input type="text" class="form-control" name="decrypt" value="<?php print $_POST['decrypt']; ?>" placeholder="eg. KlcFGXA6cQlclJhk/xZDkz5H1uLeHco4y7GmOcUGC+0=" />
		
<?php

if(strlen($decryptedEmail))
{
?>
		<p class="alert alert-success" style="margin-top:5px;"><?php print $decryptedEmail; ?></p>
<?php
}

?>
		
		
		<h4>Encrypt Email</h4>
		<input type="text" class="form-control" name="encrypt" value="<?php print $_POST['encrypt']; ?>" placeholder="eg. email@domain.com" />
<?php

if(strlen($encryptedEmail))
{
?>
		<p class="alert alert-success" style="margin-top:5px;"><?php print $encryptedEmail; ?></p>
<?php
}

?>
		<div class="form-actions">
			<input type="submit" value="Submit" class="btn btn-primary pull-right" />
		</div>
	<?php $this->endWidget(); ?>
		</div>
	</div>
</div>