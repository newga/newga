<div class="page-header">
	<h1>Generate Test Data</h1>
</div>

<p>This script will create 100 rows of randomly-generated content for the database</p>

<div class="alert alert-warning">
	<p>You are on the server <em><?php print $_SERVER['HTTP_HOST']; ?></em>, which is the <?php print ENVIRONMENT; ?> environment.</p>
</div>

<p>Your current data is as follows:</p>

<?php

$Store = Store::model()->count();
$Store2Contact = Store2Contact::model()->count();
$Accession = Accession::model()->count();
$CleanWarehouse = Yii::app()->db->createCommand("SELECT COUNT(*) as tot FROM clean_warehouse")->queryRow();
?>

<table class="table table-bordered">
	<tr>
		<th>Table</th>
		<th>Rows</th>
	</tr>
	<tr>
		<td>store</td>
		<td><?php print $Store; ?></td>
	</tr>
	<tr>
		<td>store2contact</td>
		<td><?php print $Store2Contact; ?></td>
	</tr>
	<tr>
		<td>accession</td>
		<td><?php print $Accession; ?></td>
	</tr>
	<tr>
		<td>clean_warehouse (view)</td>
		<td><?php print_r($CleanWarehouse['tot']); ?></td>
	</tr>
</table>

<?php

if(ENVIRONMENT !== 'PRODUCTION' || Yii::app()->user->role >= User::ROLE_SUPERADMIN)
{
?>
<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'testdata',
	'enableAjaxValidation'=>false,
)); ?>
	<input type="submit" name="do_test_data" class="btn btn-large btn-danger" value="Generate 100 rows of data" />
<?php $this->endWidget(); ?>
<?php
}
?>