<?php
//work out percentage convert
$total = 0;
foreach ($data as $item)
{
	$total +=$item;
}
$c = 100/$total;

$totalPercentage = 0;

?>



<div class="<?php echo ($fullWidth ? 'col-xs-12' :'col-xs-6');?>">
	<div class="query-percentage-bar">
	<h3><?=$title;?></h3>
	<hr>
	<?php 
	$i=0; 
	foreach ($data as $key=>$item) { 
		
		$percentage = round(($item/$total)*100,1);
		
		// Set a margin to push overlapping titles apart
		$marginTop = 0;
		if((($i+1) % 2) == 0)
		{
			$marginTop = 18;
		}
		
		// Ensure percentage doesn't exceed 100
		$totalPercentage += $percentage;
		
		if($totalPercentage > 100)
		{
			// Alter the width of the last element
			$percentage = $percentage - ($totalPercentage - 100);
		}
	?>

		<div class="bar-part" style="width:<?=$percentage;?>%;">
			<div class="bar-colour <?='bc'.$i%4;?>"><?=$percentage;?>%</div>
			<div class="bar-label" style="<?php print $percentage < 7 ? 'margin-top:'.$marginTop.'px;':''; ?>"><?=$key;?><br /><?php if ($showCount) { ?><?=$item;?><?php } ?></div>
		</div>

	<?php $i++; } ?>
	</div>
</div>