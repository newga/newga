<?php

/* query details page */

if(sizeof($people))
{

	//age line chart

	$numberSpread = array();
	$cultureSpread = array();
	$originSpread = array();
	$originSpreadKeys = array();
	$originSpreadTitles = array();


	//create a universal age range of 0 - 100
	for ($j=0; $j<=100; $j++)
	{
		$numberSpread[$j] = 0;
	}


	foreach ($people as $person)
	{
		if(strlen($person['dob']))
		{
			$date = new DateTime($person['dob']);
			$now = new DateTime();
			$interval = $now->diff($date);
			$numberSpread[$interval->y]++;
		}

		$person['origin_organisation_id'];
		$originSpread[$person['origin_organisation_id']]++;


		if (!$person['culture_segment']) {
			$person['culture_segment'] = 'Unknown';
		}
		$cultureSpread[$person['culture_segment']]++;

	}

	//sort by key
	ksort($numberSpread);


	$Organisations = Organisation::model()->findAllByAttributes(array(
		'id'=>array_keys($originSpread)
		),
		array('index'=>'id')
	);


	foreach ($originSpread as $key => $value)
	{
		$originSpreadTitles[$Organisations[$key]->title]=$value;
	}

} // !sizeof($people)

?>
<div class="query-infographics">
<?php

if(Yii::app()->controller->getRoute() != 'site/dashboard')
{

?>
<div class="page-header">
	<h1><?=CHtml::encode($Query->name);?></h1>
</div>

<?php

	if(ENVIRONMENT == 'LOCAL')
	{

?>
<div class="row">

	<div class="col-sm-12">
		<pre><?=$string;?></pre>
	</div>
</div>

<?php

	}
}

if (!sizeof($people))
{ 

?> 
<p class="alert alert-info">At this stage of the project there is no data available for you to see the results of your query.</p>
<?php

}
else
{

?>
<div class="row">
<?php 

//large number
echo $this->renderPartial('//query/_run/number',array('count'=>count($people)));

//commonwealth spread

// $commonSpread;

// foreach ($people as $person) {
// 	if ($person['terms_agreed'] > 0) {
// 		$commonSpread['Commonwealth']+=1;	
// 	}
// 	else {
// 		$commonSpread['Warehouse']+=1;
// 	}
	
// }

//echo $this->renderPartial('//query/_run/percentage_bar',array("data"=>$commonSpread,'showCount'=>false, 'fullWidth'=>false,'title'=>'Commonwealth'));

?>
</div>
<div class="row">
<?php

	//age line chart

	echo $this->renderPartial('//query/_run/line', array('data' => $numberSpread, 'title' => 'Age Spread'));

	echo $this->renderPartial('//query/_run/doughnut',array("data"=>$originSpreadTitles,'title'=>'Origin Organisation'));

	echo $this->renderPartial('//query/_run/percentage_bar', array('data' => $cultureSpread, 'showCount'=>false, 'fullWidth'=>true, 'title'=>'Culture Segments'));


?>
	<div class="col-md-12">
		<div class="page-header">
			<h3>Visiting Venues</h3>
		</div>
	</div>
	
	<?php
	
	// Have re-written this code to stop the loop of every contact2venue row
	// Currently still performing 1 query per venue, but it's pretty fast.
	$Venues = Venue::model()->findAll(array('condition'=>'active = 1'));
	
	$visitedVenueCounts = array();
	
	foreach($Venues as $Venue)
	{
		$Command = Yii::app()->db->createCommand("
		
		SELECT COUNT(*) AS visitedCount, visited FROM contact2venue
		
		WHERE venue_id = :venue_id 
		
		GROUP BY visited
		
		");
		
		$Command->bindParam(':venue_id', $Venue->id);
		
		$results = $Command->queryAll();
		
		$visitedVenueCounts[$Venue->id] = array(
			'name' => $Venue->title,
			'visitedCounts' => array(),
		);
		
		// Only 4 items in $results, a row for each visited answer
		foreach($results as $result)
		{
			$visitedVenueCounts[$Venue->id]['visitedCounts'][$result['visited']] = $result['visitedCount'];
		}
	}
	
	$visitedNames = array(
		1 => 'Visited',
		2 => 'Visited in the last three years',
		3 => 'Never been but I would',
		4 => 'Never been and do not plan to'
	);

	?>

</div>

<?php

	// if not home then offer download link for results of this query.
	if(Yii::app()->controller->getRoute() != 'site/dashboard')
	{

?>
<div class="row">
	<div class="span12">
<?php if($downloadResultSize < 1){ ?>
		<p>This query returns no results which you are entitled to download</p>
<?php } else { ?>
		<p>You are entitled to <?= CHtml::link('Download<span class="hide">ing</span> ' . $downloadResultSize . ' contacts', array('query/download', 'id' => $Query->id), array('class' => 'btn btn-default download-query-results')); ?></p>
<?php } ?>
	</div>
</div>

<?php 
	}
}
?>

</div>
<script>
$(document).on('click', '.download-query-results', function(e){
	$(this).addClass('disabled').find('span').removeClass('hide');
})
</script>