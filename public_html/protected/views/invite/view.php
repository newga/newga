<div class="page-header">
	<h1>Invitation Results - <small><?php print $Campaign->name; ?></small></h1>
</div>

<?php print $this->renderPartial('_tabs', array('Campaign' => $Campaign)); ?>

<div class="row">
	<div class="col-md-8">
		<table class="table invite-results">
			<tr>
				<td>Sent to:</td>
				<th><?php print $invitesCount; ?></th>
			</tr>
<?php

if($invitesCount)
{
?>
			<tr>
				<td>Click-throughs:</td>
				<th><?php print $clickThroughCount; ?></th>
				<td><span class="text-muted">(<?php print round(($clickThroughCount / $invitesCount) * 100); ?>%)</span></td>
			</tr>
			<tr>
				<td>Joined <?php print Yii::app()->name; ?>:</td>
				<th><?php print $joinedCount; ?></th>
				<td><span class="text-muted">(<?php print round(($joinedCount / $invitesCount) * 100); ?>%)</span></td>
			</tr>
<?php
}
?>
		</table>
	</div>
</div>
