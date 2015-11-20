<ul class="nav nav-tabs" role="tablist">
	<li class="<?php print $this->route == 'invite/view' ? 'active':'';?>"><a href="<?php print $this->createUrl('invite/view', array('campaign_id' => $Campaign->id)); ?>">Results</a></li>
	<li class="<?php print $this->route == 'invite/recipients' ? 'active':'';?>"><a href="<?php print $this->createUrl('invite/recipients', array('campaign_id' => $Campaign->id)); ?>">Recipients</a></li>
	<li class="<?php print $this->route == 'invite/rules' ? 'active':'';?>"><a href="<?php print $this->createUrl('invite/rules', array('campaign_id' => $Campaign->id)); ?>">Query Rules</a></li>
</ul>