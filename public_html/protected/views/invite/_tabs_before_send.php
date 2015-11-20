<ul class="nav nav-tabs" role="tablist">
	<li class="<?php print $this->route == 'invite/edit' ? 'active':'';?>"><a href="<?php print $this->createUrl('invite/edit', array('campaign_id' => $Campaign->id)); ?>">Edit Content</a></li>
	<li class="<?php print $this->route == 'invite/intendedRecipients' ? 'active':'';?>"><a href="<?php print $this->createUrl('invite/intendedRecipients', array('campaign_id' => $Campaign->id)); ?>">Recipients <span class="badge badge-default"><?php print $Campaign->query->num_contacts; ?></span></a></li>
	<li class="<?php print $this->route == 'invite/rules' ? 'active':'';?>"><a href="<?php print $this->createUrl('invite/rules', array('campaign_id' => $Campaign->id)); ?>">Query Rules</a></li>
<?php

if(strlen($Campaign->invite_email_subject))
{
?>
	<li class="<?php print $this->route == 'invite/send' ? 'active':'';?>"><a href="<?php print $this->createUrl('invite/send', array('campaign_id' => $Campaign->id)); ?>">Preview and Sending Options</a></li>
<?php
}

?>
</ul>