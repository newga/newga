

<?php

//store flash messages into array as used in multiple places within form
$flashes = Yii::app()->user->getFlashes();


foreach($flashes as $state => $message)
{
	if (strrpos($state, 'campaign') !== false) 
	{
		?><p class="alert alert-<?= substr($state, strpos($state, "-") + 1) ?>"><?= CHtml::encode($message); ?></p><?php
	}
}?>
<div class="section-header">
	<h1>Step 1: Campaign Details</h1>
</div>
<p class="reveal-link collapsable-toggle campaign-show-link"<?= ($Campaign->isNewRecord ? ' style="display:none;"' : ''); ?> data-toggle="#form-campaign-createupdate, .campaign-hide-link, .campaign-show-link"><a href="#"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> Show campaign details</a></p>
<p class="reveal-link collapsable-toggle campaign-hide-link" style="display:none" data-toggle="#form-campaign-createupdate, .campaign-show-link, .campaign-hide-link"><a href="#"><span class="glyphicon glyphicon-minus" aria-hidden="true"></span> Hide campaign details</a></p>
<?= ($Campaign->hasErrors() ? '<p>' . CHtml::errorSummary($Campaign, null, null, array('class' => 'alert alert-danger')) . '</p>' : ''); ?>

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'form-campaign-createupdate',
	'enableAjaxValidation'=>false,
	'action' => (!$Campaign->isNewRecord) ? array('campaign/createUpdate', 'id' => $Campaign->id,'step'=>1,'#'=>'details') : array(),
	'htmlOptions' => array(
		'style' => ($step!=1 ? 'display:none;' : '') . ' padding-bottom:50px;',
	)
)); ?>


	<div class="row">
		<div class="col-md-6">

			<div class="form-group">
				<?= $form->labelEx($Campaign, 'query_id'); ?>
				<?= $form->dropDownList($Campaign, 'query_id', CHtml::listData($Queries, 'id', 'name'), array('class' => 'form-control')); ?>
			</div>

			<div class="form-group">
				<?= $form->labelEx($Campaign, 'name'); ?>
				<?= $form->textField($Campaign, 'name', array('class' => 'form-control')); ?>
			</div>

			<div class="form-group">
				<?= $form->labelEx($Campaign, 'description'); ?>
				<?= $form->textArea($Campaign, 'description', array('class' => 'form-control')); ?>
			</div>

			<div class="form-group">
				<?= $form->labelEx($Campaign, 'type') ?>
				<?= $form->dropDownList($Campaign, 'type', array(Campaign::TYPE_EMAIL => "Email"), array('class' => 'form-control')); ?>
			</div>

			<div class="form-group">
				<?= CHtml::label('Optional results limit', ''); ?>
				<?= $form->textField($Campaign, 'size', array('class' => 'form-control', 'style' => 'width:150px;', 'placeholder' => 'e.g. 246')); ?>
			</div>

			<div class="form-group">
				<?= CHtml::submitButton($Campaign->isNewRecord ? 'Create' : 'Update', array('class' => 'btn btn-primary')); ?>
				<?= CHtml::link('cancel', array('index'), array('class' => 'btn btn-default')); ?>
			</div>


		</div>
	</div>
<?php $this->endWidget(); ?>

<?php if (!$Campaign->isNewRecord) { ?>



<div id="outcomes" class="section-header" data-toggle=".campaign-group-outcomes-management, .outcome-show-link">
	<h1>Step 2: Add outcomes</h1>
</div>
<p class="reveal-link collapsable-toggle outcome-show-link" style="<?=($step==2) ? 'display:none;' : 'display:block;';?>" data-toggle=".campaign-group-outcomes-management, .outcome-show-link, .outcome-hide-link"><a href="#"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> Show outcomes details</a></p>
<p class="reveal-link collapsable-toggle outcome-hide-link" style="<?=($step==2) ? 'display:block;' : 'display:none;';?>" data-toggle=".campaign-group-outcomes-management, .outcome-show-link, .outcome-hide-link"><a href="#"><span class="glyphicon glyphicon-minus" aria-hidden="true"></span> Hide outcomes details</a></p>


<?php

	foreach($flashes as $state => $message)
	{
		if (strrpos($state, 'outcome') !== false)
		{

?>
<p class="alert alert-<?= substr($state, strpos($state, "-") + 1) ?>"><?= CHtml::encode($message); ?></p>
<?php

		}
	}


	?>


<div class="row campaign-group-outcomes-management" style="<?=($step==2) ? 'display:block;' : 'display:none;';?>">
	<div class="col-md-6">
	<?php

	foreach($Campaign->outcomes as $Outcome)
	{

	?>
		<h3><?= $Outcome->name; ?></h3>
		<p><?= $Outcome->description; ?></p>
		<p><?php

		if(!strlen($Outcome->url))
		{
			print 'no url</p>';
		}
		else
		{
																																	// jbg suggested keeping this simple for the end user and us parsing it - hb
			print CHtml::link($Outcome->url, $Outcome->url, array('target' => '_blank')) . '</p><p>Email Campaign url replace tag: <input type="text" value="%recipient.outcome_' . $Outcome->id . '%" style="font-family:courier;"" onclick="this.select()"></p><p class="small">Example usage: <span style="font-family:courier;background-color:#ddd;padding:2px;">&lt;a href="%recipient.outcome_' . $Outcome->id . '%"&gt;click here&lt;/a&gt;</span></p>';
		}

	?></p>
		<p class="text-right">
		<?= CHtml::link('Delete', '?remove-outcome=' . $Outcome->id . '&campaign_id=' . $Campaign->id, array_merge(array('class' => 'btn btn-default'), is_null($Outcome->url) ? array('confirm' => 'Permanently remove this outcome? ') : array('confirm' => 'Removing this outcome will stop it\'s url template tag from working. They should be removed from anywhere they\'ve been implemented'))); ?>
		</p>
		<hr />
	<?php

	}


	// add an outcome form

	?>
		<h3>Add an outcome</h3>


	<?php

	$Form = $this->beginWidget('CActiveForm', array(
		'id' => 'form-outcome-create',
		'enableAjaxValidation'=>false,
		'action' => array('campaign/createUpdate', 'id' => $Campaign->id,'step'=>2,'#'=>'outcomes')
	));

	?>
	<?= ($NewOutcome->hasErrors() ? '<p>' . CHtml::errorSummary($NewOutcome, null, null, array('class' => 'alert alert-danger')) . '</p>' : ''); ?>


		<div class="form-group">
			<label class="control-label">Name</label>
			<?= $Form->textField($NewOutcome, 'name', array('class' => 'form-control')); ?>
		</div>

		<div class="form-group">
			<label class="control-label">Description</label>
			<?= $Form->textArea($NewOutcome, 'description', array('class' => 'form-control')); ?>
		</div>

		<div class="form-group">
			<label class="control-label">Url</label>
				<?= $Form->textField($NewOutcome, 'url', array('class' => 'form-control', 'placeholder' => 'Optional url (for automatic outcomes)')); ?>
		</div>

		<div class="form-group">
			<?= CHtml::submitButton('Add Outcome', array('class' => 'btn btn-primary')); ?>
		</div>
	<?php


	$this->endWidget();
	unset($Form);

	?>

	</div>
</div>





<div id="groups" class="section-header" data-toggle=".campaign-group-management, .group-show-link">
	<h1>Step 3: Groups <small>(Split of <?= sizeof($Query['rows']); ?> query results)</small></h1>
</div>
<p class="reveal-link collapsable-toggle group-show-link" style="<?=($step==3) ? 'display:none;' : 'display:block;';?>" data-toggle=".campaign-group-management, .group-hide-link, .group-show-link"><a href="#"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> Show group details</a></p>
<p class="reveal-link collapsable-toggle group-hide-link" style="<?=($step==3) ? 'display:block;' : 'display:none;';?>" data-toggle=".campaign-group-management, .group-show-link, .group-hide-link"><a href="#"><span class="glyphicon glyphicon-minus" aria-hidden="true"></span> Hide group details</a></p>



	<?php

	foreach($flashes as $state => $message){
		if (strrpos($state, 'group') !== false) {
		?><p class="alert alert-<?= substr($state, strpos($state, "-") + 1) ?>"><?= CHtml::encode($message); ?></p><?php
		}
	}	


	?>






<div class="row campaign-group-management" style="<?=($step==3) ? 'display:block;' : 'display:none;';?>">
	<div class="col-md-6">
<?php
foreach($Campaign->groups as $Group){
	?>

	<div style="padding-top:30px;" id="group<?=$Group->id;?>">

	<?php

	$form=$this->beginWidget('CActiveForm', array(
		'id'=>'form-groups-createupdate',
		'enableAjaxValidation'=>false,
		'action' => array('campaign/createUpdate', 'id' => $Campaign->id,'step'=>3,'#'=>'groups')
	)); 




	?>



		<div class="form-group">
			<?= $form->labelEx($Group, 'name'); ?>
			<?= $form->textField($Group, 'name', array('class' => 'form-control')); ?>
		</div>

		<div class="form-group">
			<?= $form->labelEx($Group, 'description'); ?>
			<?= $form->textArea($Group, 'description', array('class' => 'form-control')); ?>
		</div>

		<div class="form-group">
			<?= $form->labelEx($Group, 'subject'); ?>
			<?= $form->textField($Group, 'subject', array('class' => 'form-control')); ?>
		</div>

		<div class="form-group">
			<?= $form->labelEx($Group, 'fraction'); ?>
			<?= $form->textField($Group, 'fraction', array('class' => 'form-control')); ?>
		</div>

		<?= $form->hiddenField($Group, 'id'); ?>

		<?= CHtml::submitButton('Update', array('class' => 'btn btn-primary')); ?>
		<?php
		
		if (!$Group->email_template) { 
				echo CHtml::link('Add Template', array('emailTemplate/create', 'campaign_id' => $Group->campaign_id, 'group_id' => $Group->id), array('class' => 'btn btn-default','data-toggle'=>'ajaxModal','data-groupid'=>$Group->id,'data-campaignid'=>$Group->campaign_id)); 
		} 
		else {  
				echo CHtml::link('View Template', array('emailTemplate/view', 'template_id' => $Group->email_template->id, 'campaign_id'=>$Group->campaign_id,'group_id'=>$Group->id), array('class' => 'btn btn-default','data-toggle'=>'ajaxModal','data-groupid'=>$Group->id,'data-campaignid'=>$Group->campaign_id)); 
		}
		echo CHtml::link('Delete', '?remove-group=' . $Group->id, array_merge(array('class' => 'btn btn-default pull-right'),array('confirm' => 'Permanently remove this group? '))); 


		$this->endWidget();
		?>
		</div>
		<hr />
		<?php
	}
	
	unset($form);
	
	?>


	<div id="addgroup" style="padding-top:60px;">
		<h3>Add a group</h3>

	<?php

	$Form = $this->beginWidget('CActiveForm', array(
		'id' => 'form-group-create',
		'enableAjaxValidation'=>false,
		'action' => array('campaign/createUpdate', 'id' => $Campaign->id,'step'=>3,'#'=>'addgroup')

	));

	?>
	<?= ($NewGroup->hasErrors() ? '<p>' . CHtml::errorSummary($NewGroup, null, null, array('class' => 'alert alert-danger')) . '</p>' : ''); ?>


		<div class="form-group">
			<?= $Form->labelEx($NewGroup, 'name'); ?>
			<?= $Form->textField($NewGroup, 'name', array('class' => 'form-control')); ?>
		</div>

		<div class="form-group">
			<?= $Form->labelEx($NewGroup, 'description'); ?>
			<?= $Form->textArea($NewGroup, 'description', array('class' => 'form-control')); ?>
		</div>

		<div class="form-group">
			<?= $Form->labelEx($NewGroup, 'subject'); ?>
			<?= $Form->textField($NewGroup, 'subject', array('class' => 'form-control')); ?>
		</div>

		<div class="form-group">
			<?= $Form->labelEx($NewGroup, 'fraction'); ?>
			<?= $Form->textField($NewGroup, 'fraction', array('class' => 'form-control')); ?>
		</div>

		<?= $Form->hiddenField($NewGroup, 'id'); ?>
		<?= CHtml::submitButton('Add Group', array('class' => 'btn btn-primary')); ?>

	<?php

	$this->endWidget();
	unset($Form);

	?>
	</div>
	


	</div>
</div>


<div id="run" class="section-header" data-toggle=".campaign-group-run, .run-show-link">
	<h1>Step 4: Run</h1>
</div>
<p class="reveal-link collapsable-toggle run-show-link" style="<?=($step==4) ? 'display:none;' : 'display:block;';?>" data-toggle=".campaign-group-run, .run-hide-link, .run-show-link"><a href="#"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> Show run details</a></p>
<p class="reveal-link collapsable-toggle run-hide-link" style="<?=($step==4) ? 'display:block;' : 'display:none;';?>" data-toggle=".campaign-group-run, .run-show-link, .run-hide-link"><a href="#"><span class="glyphicon glyphicon-minus" aria-hidden="true"></span> Hide run details</a></p>

<div class="row campaign-group-run" style="<?=($step==4) ? 'display:block;' : 'display:none;';?>">

	<div class="col-md-6">
<?php

if((int)$totalPercentage !== 100 || ((int)$Campaign->type === Campaign::TYPE_EMAIL && (sizeof($SubjectlessCampaignGroups) || $MissingTemplates)))
{

	if((int)$totalPercentage !== 100)
	{
		?><p class="alert alert-danger"><?= CHtml::encode('The percentage splits of campaign groups must total 100. Currently group percentage splits total ' . $totalPercentage . '.'); ?></p><?php
	}

	if((int)$Campaign->type === Campaign::TYPE_EMAIL && sizeof($SubjectlessCampaignGroups))
	{
		?><p class="alert alert-danger"><?= CHtml::encode('All campaign groups of an email campaign require a subject.'); ?></p><?php
	}

	if((int)$Campaign->type === Campaign::TYPE_EMAIL && $MissingTemplates)
	{
		?><p class="alert alert-danger"><?= CHtml::encode('All campaign groups of an email campaign require a template.'); ?></p><?php
	}


	// can't be run due to tests at top
	echo CHtml::button('Run this campaign', array('type' => 'button', 'class' => 'btn btn-primary disabled'));
}
else
{
	// ok to run
	?><p>All required fields are complete.</p><?php
	echo CHtml::link('Run this campaign', array('campaign/run', 'id' => $Campaign->id), array('class' => 'btn btn-primary'));
}

?>
	</div>

</div>


<?php } ?>




<script>

$(document).on('click', '.collapsable-toggle', function(e){

	e.preventDefault();

	//console.log($(this).data('toggle'), $($(this).data('collapsable')))
	$($(this).data('toggle')).toggle();
})

</script>



<div id="form-modal" class="modal fade" >
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h2 class="modal-title">Email Template</h2>
			</div>
			<div class="modal-body">
				<div id="iframe-holder"></div>
			</div>
	 		<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div>

