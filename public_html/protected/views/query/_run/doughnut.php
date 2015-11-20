 <script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable([
          ['Task', 'Percentage'],
		<?php foreach ($data as $key=>$value) { ?>
		  [<?=CJavaScript::encode($key);?>,	<?=CJavaScript::encode($value);?>],     
		<?php } ?>
 		]);
        var options = {
          pieHole: 0.4,
          chartArea: {width: '90%', height: '90%'},
          colors: ['#3e8acc','#58b957','#f2ae43','#db524b','#999999']
        };

        var chart = new google.visualization.PieChart(document.getElementById('donutchart'));
        google.visualization.events.addListener(chart, 'ready', function () {
            $('#donutchart').hide();
            $('#donutchart').delay(500).fadeIn(800);
        });


        chart.draw(data, options);
      }
</script>

<div class="col-xs-6">
	<div class="query-doughnut">
		<h3><?=$title;?></h3>
		<hr>
		<div class="doughnut" id="donutchart"></div>
	
	</div>
</div>