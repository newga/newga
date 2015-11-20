<?php

if(sizeof($visitedVenueCount['visitedCounts']))
{

?>
	<script type="text/javascript">
		google.load("visualization", "1", {packages:["corechart"]});
		google.setOnLoadCallback(drawChart);
		function drawChart() {
			var data = google.visualization.arrayToDataTable([
				['Task', 'Number',{ role: 'style' }],
		<?php foreach ($visitedVenueCount['visitedCounts'] as $key=>$value) { ?>
			[<?= CJavaScript::encode($visitedNames[$key]) ;?>,	<?=(int)$value;?>, '#c3dbf0'],	  
		<?php } ?>
 		]);
		var options = {
			chartArea: {width: '90%', height: '90%'},
			colors: ['#3e8acc','#58b957','#f2ae43','#db524b','#999999'],
		};

		var chart = new google.visualization.BarChart(document.getElementById('vsg<?=$venueID;?>'));
		google.visualization.events.addListener(chart, 'ready', function () {
		});


		chart.draw(data, options);
	  }
</script>



<div class="col-xs-3">
	<div class="query-small-chart">
		<h3><?=CHtml::encode($visitedVenueCount['name']);?></h3>
		<hr>
		<div class="doughnut" id="vsg<?=$venueID;?>"></div>
	
	</div>
</div>

<?php
}
?>
