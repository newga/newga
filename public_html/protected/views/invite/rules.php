<?php

if($Campaign->status == Campaign::STATUS_NOT_RUN)
{
	
	$this->breadcrumbs=array(
		'Invites' => array('index'),
		$Campaign->name => array('edit', 'campaign_id' => $Campaign->id),
		'Query Rules'
	);
?>
<div class="page-header">
	<h1>Invite <em><?php print $Campaign->name; ?></em></h1>
</div>

<?php print $this->renderPartial('_tabs_before_send', array('Campaign' => $Campaign)); ?>

<?php
}
else
{
?>
<div class="page-header">
	<h1>Invitation Results - <small><?php print $Campaign->name; ?></small></h1>
</div>

<?php print $this->renderPartial('_tabs', array('Campaign' => $Campaign)); ?>

<?php
}

$Questions = QueryQuestion::model()->findAll(array(
	'index' => 'id',
));


$queryData = json_decode($Campaign->query->JSON);

/*
print '<pre>';
print_r($queryData);
print '</pre>';
*/


$Venues = null;
$Organisations = null;
$InviteQueries = null;
$CultureSegments = null;
$Artforms = null;
$LevelsOfEngagement = null;
$Campaigns = null;

?>

<div class="row">
	<div class="col-md-8">
		<h4>Default Rules</h4>
		<table class="table table-bordered table-striped">
			<tr>
				<th style="width:120px;"></th>
				<th>Rule</th>
			</tr>
			<tr>
				<td></td>
				<td>Is part of <?php print Yii::app()->name; ?></td>
			</tr>
			<tr>
				<td>and</td>
				<td>Has an email address</td>
			</tr>
		</table>
		
		<h4>Query rules</h4>
		<table class="table table-bordered table-striped">
			<tr>
				<th style="width:120px;"></th>
				<th>Rule</th>
			</tr>
<?php

foreach($queryData->rows as $row)
{
	$Question = $Questions[$row->query_choice];
?>
			<tr>
				<td><?php print $row->and_choice ? 'and':'or'; ?></td>
				<td>
					<?php print $row->bool_choice ? $Question->positiveLang:$Question->negativeLang; ?>
					<?php print $Question->question; ?>
<?php
	
					switch($Question->option_id)
					{
						case QueryQuestion::OPTION_VENUE:
							if(is_null($Venues)){ $Venues = Venue::model()->findAll(array('condition'=>'active = 1', 'index'=>'id')); }
							print ' ' . $Venues[$row->query_option];
						break;
								
						case QueryQuestion::OPTION_ORGANISATION:
							if(is_null($Organisations)){ $Organisations = Organisation::model()->findAll(array('condition'=>'active = 1', 'index'=>'id')); }
							print ' ' . $Organisations[$row->query_option]->title;
						break;
								
						case QueryQuestion::OPTION_INVITE:
							if(is_null($InviteQueries)){ $InviteQueries = Query::model()->findAll(array('condition'=>'invite = 1','index'=>'id')); }
							print ' ' . $InviteQueries[$row->query_option]->name;
						break;
								
						case QueryQuestion::OPTION_CS:
							if(is_null($CultureSegments)){ $CultureSegments = CultureSegment::model()->findAll(array('index' => 'id')); }
							print ' ' . $CultureSegments[$row->query_option]->name;
						break;

						case QueryQuestion::OPTION_ARTFORM:
							if(is_null($Artforms)){ $Artforms = Artform::model()->findAll(array('index' => 'id', 'order' => 'title ASC')); }
							print ' ' . $CultureSegments[$row->query_option]->title;
						break;

						case QueryQuestion::OPTION_LOE:
							if(is_null($LevelsOfEngagement)){ $LevelsOfEngagement = QueryQuestion::model()->levelsOfEngagement(); }
							print ' ' . $LevelsOfEngagement[$row->query_option];
						break;
								
						case QueryQuestion::OPTION_CAMPAIGN:
							if(is_null($Campaigns)){ $Campaigns = Campaign::model()->with('query')->findAll(array( 'condition' => 'invite = 0', 'index' => 'id' )); }
							print ' ' . $Campaigns[$row->query_option];
						break;
						
						default:
							print ' ' . $row->query_number;
						break;
					}
?>
				</td>
			</tr>
<?php
}

?>
		</table>
	</div>
</div>
