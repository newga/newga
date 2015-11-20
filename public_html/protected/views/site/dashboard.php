<div class="dashboard">
	<div class="page-header">
		<h1>Dashboard</h1>
	</div>
	<div class="dashboard-buttons">
		<a class="btn btn-default btn-lg" href="<?=$this->createUrl('query/index');?>"><span class="glyphicon glyphicon-sort-by-attributes"></span> Query Builder</a>
		<a class="btn btn-default btn-lg" href="<?=$this->createUrl('data/upload');?>"><span class="glyphicon glyphicon-upload"></span> Upload CSV</a>
		<?php if((int)Yii::app()->user->role === User::ROLE_MANAGER || (int)Yii::app()->user->role === User::ROLE_ORGANISATION){ ?>
		<?php if((int)Yii::app()->user->role === User::ROLE_ORGANISATION){ ?><a class="btn btn-default btn-lg" href="<?=$this->createUrl('data/unsubscribes');?>"><span class="glyphicon glyphicon-download"></span> Unsubscribes</a><?php } ?>
		<?php } ?>
		<?php if (Yii::app()->user->role >= User::ROLE_MANAGER): ?>

		<a class="btn btn-default btn-lg" href="<?=$this->createUrl('campaign/index');?>"><span class="glyphicon glyphicon-bullhorn"></span> Campaigns</a>
		<a class="btn btn-default btn-lg" href="<?=$this->createUrl('invite/index');?>"><span class="glyphicon glyphicon-plus-sign"></span> Invites</a>
		<a class="btn btn-default btn-lg" href="<?=$this->createUrl('venue/index');?>"><span class="glyphicon glyphicon-flag"></span> Venues</a>
		<a class="btn btn-default btn-lg" href="<?=$this->createUrl('user/index');?>"><span class="glyphicon glyphicon-user"></span> Users</a>

		<?php endif; ?>

		<?php if (Yii::app()->user->role >= User::ROLE_SUPERADMIN): ?>
        
        <a class="btn btn-default btn-lg" href="<?=$this->createUrl('organisation/index');?>"><span class="glyphicon glyphicon-map-marker"></span> Organisations</a>
		
		<?php endif; ?>
	</div>
</div>

<hr>
<?php

if((int)Yii::app()->user->role === User::ROLE_ORGANISATION)
{

?>
<div class="page-header">
	<h2>Download Contacts</h2>
</div>
<p>Download all your uploaded contacts who have since signed up and thus updated their contact information.</p>
<p>
	<?= CHtml::link('Download ' . $totalContacts . ' contacts', array('site/downloadContacts'), array('class' => 'btn btn-info ' . ($totalContacts > 0 ? '' : 'disabled'), 'onclick' => ($totalContacts > 0 ? "this.className += 'disabled';this.text = 'Export in progress...'" : ''))); ?>
</p>

<?php

}

?>