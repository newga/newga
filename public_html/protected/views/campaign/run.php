<?php



?>
<div class="page-header">
<?php

if($Query->invite)
{
?>
	<h1>Run Invitation Campaign: <small><?= CHtml::encode($Campaign->name); ?></small></h1>
<?php
}
else
{
?>
	<h1>Run Campaign: <small><?= CHtml::encode($Campaign->name); ?></small></h1>
<?php
}
?>
</div>

<div class="row">
	<div class="col-md-12">

		<div class="panel panel-info">
			<div class="panel-heading">
				<h2 class="panel-title">Run campaign</h2>
			</div>
			<div class="panel-body">
				<p>A campaign can only be run once. Only minimal changes can be made to a campaign once you have run it.</p>
				<p>Are you sure you would like to run the campaign "<?= CHtml::encode($Campaign->name); ?>"?</p>
<?php

$form = $this->beginWidget('CActiveForm', array(
	'id' => 'form-campaign-run',
	'enableAjaxValidation'=>false
));

?>
					<?= CHtml::hiddenField('Campaign[id]', $Campaign->id); ?>
					<button id="run-campaign" type="submit" class="btn btn-primary">Run it now</button>
					&nbsp;&nbsp;&nbsp;<?= CHtml::link('Return to updating the campaign.', array('campaign/createUpdate', 'id' => $Campaign->id)); ?>
<?php $this->endWidget(); ?>
			</div>
		</div>
	</div>
</div>





<div class="modal fade" id="loading_modal" tabindex="-1" role="dialog" aria-labelledby="basicModal" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h2>Building campaign...</h2>
			</div>
			<div class="modal-body">
				<div class="progress">
					<div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%"></div>
				</div>
			</div>
		</div>
	</div>
</div>