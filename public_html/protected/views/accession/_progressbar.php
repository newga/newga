<?php

$start = 1;
$end = 7;

$steps = range($start, $end);

$progress = $progress -1;

$progressPercent = round($progress / $end * 100, 2);

?>

<div class="progress">
	<div class="progress-bar" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: <?php print $progressPercent; ?>%;">
		<span class="sr-only"><?php print $progressPercent; ?>% Complete</span>
	</div>
</div>