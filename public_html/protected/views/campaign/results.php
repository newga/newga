

<div class="page-header">
	<h1>Results <small><?= $Campaign->name; ?></small></h1>
	<h2>Sent <small><?= ($stats['sent']) ? date("jS F Y G:i",strtotime($stats['sent'])) : 'Not sent'; ?></small></h2>
</div>


<div class="row">

	

	<div class="query-run-number col-xs-4">
		 <h4 class="text-center">Delivered</h4>
		 <hr>
		 <p class="text-center"><?= number_format($mailgunCampaign['delivered_count']);?></p>
	</div>

	<div class="query-run-number col-xs-4">
		 <h4 class="text-center">Opened Email</h4>
		 <hr>
		 <p class="text-center"><?= number_format($stats['opencount']);?></p>
	</div>

	<div class="query-run-number col-xs-4">
		 <h4 class="text-center">Bounced</h4>
		 <hr>
		 <p class="text-center"><?= number_format($mailgunCampaign['bounced_count']);?></p>
	</div>
</div>


<div class="row">
	<div class="query-run-number col-xs-12">
		<div class="page-header">
			<h2>Outcome Click-through</h2>
		</div>

		<table class="table table-striped">
			<tr>
				<th>Name</th>
				<th>Count</th>
			</tr>
<?php

foreach($stats['outcomes'] as $outcome)
{

?>
	<tr>
		<td><?php print $outcome['name']; ?></td>
		<td><?php print $outcome['positive_outcomes_count']; ?></td>
	</tr>
<?php

}

?>
		</table>
	</div>
</div>


<?php

//condition so old campaigns pre-json store don't kick off
if(!is_null($Campaign->json)): ?>
<div class="row">
	<div class="col-xs-12">
		<div class="page-header">
			<h2>Query Criteria</h2>
		</div>

<?php

//for each row in the query builder create the correct choice
$json = json_decode($Campaign->json);
$textFirstRow = true;
$listItems = array();

foreach ($json->rows as $k => $row)
{

	$Question = QueryQuestion::model()->findByPk($row->query_choice);

	//render partial
	$listItems[] = $this->renderPartial('_textRow',array(
		'Question' => $Question,
		'QueryQuestions'=>$QueryQuestions,
		'and_choice' => $row->and_choice,
		'bool_choice' => $row->bool_choice,
		'query_choice' => $row->query_choice,
		'query_number' => $row->query_number,
		'query_option' => $row->query_option,
		'disabled' => $row->disabled,
		'textFirstRow' => $textFirstRow,
		'rowNumber' =>  $k
	), true);

	$textFirstRow = false;

}

if(sizeof($listItems))
{
	echo '<ul><li>' . implode('</li><li>', $listItems) . '</li></ul>';
}

?>
	</div>
</div>


<div class="row">
	<div class="col-xs-12">
		<div class="page-header">
			<h2>Download Campaign Contacts and Results</h2>
		</div>
		<p>Campaign results change over time. Download a current snapshot of results:</p>
		<p><?= CHtml::link('Download<span class="hide">ing</span> contacts &amp; groups', array('campaign/download', 'id' => $Campaign->id), array('class' => 'btn btn-primary btn-sm download-results')); ?></p>

		<div class="page-header">
			<h2>Upload a manual Campaign Outcome result</h2>
		</div>
		<p>Upload a csv of unique user ids to mark a manual outcome as complete:</p>
		<p><?= CHtml::link('Update a single manual outcome', array('campaign/uploadOutcome', 'id' => $Campaign->id), array('class' => 'btn btn-default btn-sm')); ?></p>
<?php

if(Yii::app()->user->role >= User::ROLE_MANAGER)
{
	// allows upload of all campaign outcomes in a single file

?>
		<div class="page-header">
			<h2>Upload all Campaign Outcomes</h2>
		</div>
		<p>Upload a csv of data to update all campaign manual outcomes at once:</p>
		<p><?= CHtml::link('Update all manual outcomes', array('campaign/upload', 'id' => $Campaign->id), array('class' => 'btn btn-default btn-sm')); ?></p>
<?php

} // manager or super

?>
	</div>
</div>
<?php endif; ?>
<script>
$(document).on('click', '.download-results', function(e){
	$(this).addClass('disabled').find('span').removeClass('hide');
})
</script>