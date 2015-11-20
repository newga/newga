<?php

/* used for query create and update */

?>


<div class="alerts">
<?php

// print success of create query after redirect?
foreach(Yii::app()->user->getFlashes() as $state => $message){

?>
	<p class="alert alert-<?= $state; ?>"><?= $message; ?></p>
<?php

}

?>
</div>
<?php

$form=$this->beginWidget('CActiveForm', array(
	'id'=>'query-form',
	'enableAjaxValidation'=>false
));

if($invite === true)
{

?>
<input type="hidden" name="invite" value="1" />
<?php

}

?>


<div class="row">
	<div class="col-xs-6">
		<div class="form-group">
			<label for="Query_name" class="required">Name <span class="required">*</span></label>
			<input class="form-control" name="Query[name]" id="query_name" type="text" value="<?=$Query->name;?>" />
		</div>

		<div class="form-group">
			<label for="Query_description" class="required">Description <span class="required">*</span></label>
			<textarea class="form-control" name="Query[description]" id="query_description" type="text"><?=$Query->description;?></textarea>
		</div>
	</div>


	<div class="col-xs-6">
		<div class="query-results" style="text-align:center;width:100%;">
			<p>Currently returns <br /><span id="counter" style="font-size:60px;" class="large total-results"><?php print number_format($queryResults['count']); ?></span><br />contacts<?php print $invite ? ' from Application Name list':''; ?></p>
<?php

if(!$Query->isNewRecord)
{

?>
			<a id="query_detail" href="<?=$this->createUrl('query/run',array('id'=>$Query->id));?>"  class="btn btn-primary">See Query Detail</a>
<?php

}

?>
			<div class="query-loader">
				<img src="/css/assets/loader.gif" alt="" >
			</div>
		</div>
	</div>

</div>
<?php

if($invite === true)
{

?>
<div class="page-header">
	<h3>Add query limit</h3>
</div>

<div class="row">
	<div class="col-xs-3">
		<input id="invite-limit" class="form-control" type="text" value="" placeholder="e.g. 1000" name="limit" />
	</div>
	<div class="col-xs-9">
		<span class="form-inline">This is the maximum number of contacts this invite will be sent to</span>
	</div>
</div>

<?php

}

?>

<div class="page-header">
	<h3>Add rule</h3>
</div>
<?php

if($invite === true)
{

?>
<div class="alert alert-info">
	<p>Invite queries automatically filter contacts to include only:</p>
	<ul>
		<li>Contacts that are not already part of <?php print Yii::app()->name; ?></li>
		<li>Contacts that have an email address</li>
		<li><b>PLEASE NOTE</b> Currently you can not use "part of invitation" or "sent in the last X days". Instead, the system will not inlude any people that have already been invited</li>
	</ul>
</div>
<?php

}

?>
<div class="row build_area">
	<div class="col-xs-2">
		<div class="form-group first">
			<select class="form-control and_choice blue" name="new[and_choice]">
					<option class="and" value="1" selected="selected">And</option>
					<option class="or" value="0">Or</option>
			</select>
			<p class="contact">Each contact</p>
		</div>
	</div>


	<div class="col-xs-2">
		<div class="form-group">
			<select class="form-control bool_choice" name="new[bool_choice]">
				<option class="positive" value="1" selected="selected">is</option>
				<option class="negative" value="0">is not</option>
			</select>
		</div>
	</div>


	<div class="col-xs-3">
		<div class="form-group">
			<select class="form-control query_choice" name="new[query_choice]">
				<option value="" selected="selected"></option>
<?php

$currentType = 0;
foreach ($QueryQuestions as $Question)
{
	//Only show campaign options for managers and above

	if (!(Yii::app()->user->role < User::ROLE_MANAGER && $Question->id == 15))
	{
		$questionTypesToIgnoreOnInvite = array(
			QueryQuestion::TYPE_ACCESSION
		);

		if($invite && in_array($Question->type, $questionTypesToIgnoreOnInvite))
		{
			continue;
		}

		if ($Question->type != $currentType)
		{

?>
				<optgroup label="<?=$Question->TypeName;?>">
<?php
				$currentType = $Question->type;

		}

?>

				<option data-id="<?=$Question->id;?>" data-has-value=<?php print $Question->has_value; ?> data-options="<?=$Question->option_id;?>" data-lang="<?=$Question->lang_type;?>" value="<?=$Question->id;?>"><?=$Question->question;?></option>

<?php
		if ($Question->type != $currentType)
		{
?>
				</optgroup>
<?php

		}
	}
}

?>
			</select>
		</div>
	</div>


	<div class="col-xs-3 query_options"></div>



	<div class="col-xs-2 query_submit">
		<div class="form-group">
			<a href="#" class="query_submit_button btn btn-primary"><span class="glyphicon glyphicon-plus-sign"></span> Add</a><!--  <a href="#" id="reset">Clear</a> -->
		</div>
	</div>
</div>



<div class="page-header">
	<h3>Current rules</h3>
</div>

<div class="row results_area">
	<div class="col-xs-12">
		<ul>
<?php

if ($Query->JSON)
{
	//for each row in the query builder create the correct choice
	$json = json_decode($Query->JSON);

	foreach ($json->rows as $k => $row)
	{
		//print the row

		$Question = QueryQuestion::model()->findByPk($row->query_choice);

		//render partial
		$this->renderPartial('_row',array(
			'Question' => $Question,
			'QueryQuestions'=>$QueryQuestions,
			'and_choice' => $row->and_choice,
			'bool_choice' => $row->bool_choice,
			'query_choice' => $row->query_choice,
			'query_number' => $row->query_number,
			'query_option' => $row->query_option,
			'disabled' => $row->disabled,
			'rowNumber' =>  $k,

		));

	}
}

?>

		</ul>
	</div>
</div>

<hr />

<div class="row">
	<div class="col-xs-12">
		<div class="pull-left">

			<!--<input class="delete_query btn btn-danger btn <?=($Query->id ? '' : 'hidden');?>"  type="submit" name="delete" value="Delete" />-->

		</div>
		<div class="pull-right">

			<input type="hidden" name="query_id" value="<?= $Query->id; ?>" />

			<a href="/queries" class="btn btn-default">Cancel</a>
			<input id="save-query" data-id="<?=$Query->id;?>" type="submit" class="save_query btn btn-primary" value="<?=($Query->id ? 'Update' : 'Save');?>" />
		</div>
	</div>
</div>

<?php $this->endWidget(); ?>


<div class="modal fade" id="loading_modal" tabindex="-1" role="dialog" aria-labelledby="basicModal" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h2>Querying database...</h2>
			</div>
			<div class="modal-body">
				<div class="progress">
					<div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%"></div>
				</div>
			</div>
		</div>
	</div>
</div>