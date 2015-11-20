 <script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable([
          ['Age', 'Number'],
		<?php foreach ($data as $key=>$value) { ?>
		  ['<?=CJavaScript::encode($key);?>',	<?=CJavaScript::encode($value);?>],     
		<?php } ?>
 		]);

        var options = {
          pieHole: 0.4,
          chartArea: {width: '80%', height: '70%'},
          colors: ['#3e8acc','#58b957','#f2ae43','#db524b','#999999'],
          legend: 'none',
          vAxis: {title: "Number"},
          hAxis: {title: "Age"},
          
        };

        var chart = new google.visualization.AreaChart(document.getElementById('linechart'));
        google.visualization.events.addListener(chart, 'ready', function () {
            $('#linechart').hide();
            $('#linechart').fadeIn(800);
        });

        chart.draw(data, options);
      }
</script>

<div class="col-xs-6">
	<div class="query-line">
		<h3><?=$title;?></h3>
		<hr>
		<div class="line" id="linechart"></div>
	
	</div>
</div>