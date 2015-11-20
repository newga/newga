<div class="page-header">
	<h1>Invitation Results - <small><?php print $Campaign->name; ?></small></h1>
</div>

<?php print $this->renderPartial('_tabs', array('Campaign' => $Campaign)); ?>

<div class="row">
	<div class="col-md-4">
		<ul class="nav nav-pills nav-stacked" role="tablist">
<?php
		$k = 0;
		foreach($organisationsArray as $orgID => $organisation)
		{
			$maxLength = 26;
			if(strlen($organisation['title']) > $maxLength)
			{
				$organisation['title'] = substr($organisation['title'], 0, $maxLength) . '...';
			}
?>
			<li <?php print $k == 0 ? 'class="active"':''; ?>><a role="tab" data-toggle="tab" href="#org<?php print $orgID; ?>"><?php print CHtml::encode($organisation['title']); ?> <span class="badge pull-right <?php print count($organisation['invites']) < 1 ? 'badge-red':'badge-green'; ?>"><?php print count($organisation['invites']); ?></span></a></li>
<?php
			$k++;
		}
		
?>
		</ul>
	</div>
	<div class="col-md-8">
		<div class="tab-content">
<?php
		$k = 0;
		foreach($organisationsArray as $orgID => $organisation)
		{
?>
			<div class="tab-pane<?php print $k == 0 ? ' active':''; ?>" id="org<?php print $orgID; ?>">
				<table class="table table-bordered">
					<tr>
						<th>First Name</th>
						<th>Last Name</th>
						<th>Email</th>
					</tr>
<?php

			foreach($organisation['invites'] as $Invite)
			{
?>
					<tr>
						<td title="<?php print $Invite->contact_warehouse_id; ?>"><?php print $Invite->store2contact->store->first_name; ?></td>
						<td><?php print $Invite->store2contact->store->last_name; ?></td>
						<td><?php print $Invite->store2contact->store->email; ?></td>
					</tr>
<?php
			}
?>
				</table>
			</div>
<?php
			$k++;
		}
?>
		</div>
	</div>
</div>
