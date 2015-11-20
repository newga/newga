<?php

/* nav used for role organisation */

$route = Yii::app()->controller->route;

?><div class="navbar-collapse collapse">
		<ul class="nav navbar-nav">

   		<li class="dropdown">
        <?= CHtml::link('Super Admin Menu <b class="caret"></b>', array('#'), array('class' => 'dropdown-toggle', 'data-toggle' => 'dropdown')); ?>
  			<ul class="dropdown-menu">
  			  <li class="<?= ($route === 'site/dashboard') ? 'active' : '';?>"><a href="<?=$this->createUrl('site/dashboard');?>"><span class="glyphicon glyphicon-th"></span> Dashboard</a></li>

          <li class="divider"></li>

          <li class="<?= ($route === 'query/index') ? 'active' : '';?>"><a href="<?=$this->createUrl('query/index');?>"><span class="glyphicon glyphicon-sort-by-attributes"></span> Queries</a></li>

          <li class="<?= ($route === 'invite/index') ? 'active' : '';?>"><a href="<?=$this->createUrl('invite/index');?>"><span class="glyphicon glyphicon-plus-sign"></span> Invites</a></li>
      
         
          <li class="<?= ($route === 'campaign/index') ? 'active' : '';?>"><a href="<?=$this->createUrl('campaign/index');?>"><span class="glyphicon glyphicon-bullhorn"></span> Campaigns</a></li>

          <li class="<?= ($route === 'data/upload') ? 'active' : '';?>"><a href="<?=$this->createUrl('data/upload');?>"><span class="glyphicon glyphicon-upload"></span> Upload CSV</a></li>
         
          


          <li class="<?= ($route === 'organisation/index') ? 'active' : '';?>"><a href="<?=$this->createUrl('organisation/index');?>"><span class="glyphicon glyphicon-map-marker"></span> Organisations</a></li>

          <li class="<?= ($route === 'venue/index') ? 'active' : '';?>"><a href="<?=$this->createUrl('venue/index');?>"><span class="glyphicon glyphicon-flag"></span> Venues</a></li>
		  
		  
          <li class="<?= ($route === 'data/suppressionList') ? 'active' : '';?>"><a href="<?=$this->createUrl('data/suppressionList');?>"><span class="glyphicon glyphicon-list"></span> Suppression List</a></li>
		  
          <li class="<?= ($route === 'user/index') ? 'active' : '';?>"><a href="<?=$this->createUrl('user/index');?>"><span class="glyphicon glyphicon-user"></span> Users</a></li>
          
          
          <li class="divider"></li>
          
          <li class="<?= ($route === 'data/testData') ? 'active' : '';?>"><a href="<?=$this->createUrl('data/testData');?>"><span class="glyphicon glyphicon-upload"></span> Generate Test Data</a></li>
          <li class="<?= ($route === 'data/cleaningUpload') ? 'active' : '';?>"><a href="<?=$this->createUrl('data/cleaningUpload');?>"><span class="glyphicon glyphicon-upload"></span> Upload Cleaning Company CSV</a></li>
          <li class="<?= ($route === 'data/importUnsubcribes') ? 'active' : '';?>"><a href="<?=$this->createUrl('data/importUnsubcribes');?>"><span class="glyphicon glyphicon-upload"></span> Upload unsubscribes CSV</a></li>
          <li class="<?= ($route === 'data/importCampaignUnsubcribes') ? 'active' : '';?>"><a href="<?=$this->createUrl('data/importCampaignUnsubcribes');?>"><span class="glyphicon glyphicon-upload"></span> Upload campaign unsubscribes CSV</a></li>
          <li class="<?= ($route === 'data/importExtras') ? 'active' : '';?>"><a href="<?=$this->createUrl('data/importExtras');?>"><span class="glyphicon glyphicon-upload"></span> Upload extra emails CSV</a></li>
		  </ul>
      </li>
    </ul>
		<ul class="nav navbar-nav navbar-right">
	        <li><p class='logged-in-as hidden-xs'>Logged in as <?php print User::model()->getUser()->email; ?> (<?=User::model()->getUser()->adminType;?>) </p></li>
        <li><a  href="/logout">Logout</a></li>
	</ul>
</div>
