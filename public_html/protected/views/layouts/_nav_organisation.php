<?php

/* nav used for role organisation */

$route = Yii::app()->controller->route;

?><div class="navbar-collapse collapse">

    <ul class="nav navbar-nav">

      <li class="dropdown">
        <?= CHtml::link('Organisation Menu <b class="caret"></b>', array('#'), array('class' => 'dropdown-toggle', 'data-toggle' => 'dropdown')); ?>
        <ul class="dropdown-menu">
          <li class="<?= $route === 'site/dashboard' ? 'active' : '';?>"><a href="<?=$this->createUrl('site/dashboard');?>"><span class="glyphicon glyphicon-th"></span> Dashboard</a></li>

          <li class="divider"></li>

          <li class="<?= $route === 'user/details' ? 'active' : '';?>"><a href="<?=$this->createUrl('user/updateBasic');?>"><span class="glyphicon glyphicon-user"></span> Edit your details</a></li>

          <li class="<?= $route === 'query/index' ? 'active' : '';?>"><a href="<?=$this->createUrl('query/index');?>"><span class="glyphicon glyphicon-sort-by-attributes"></span> Queries</a></li>

          <li class="<?= $route === 'data/upload' ? 'active' : '';?>"><a href="<?=$this->createUrl('data/upload');?>"><span class="glyphicon glyphicon-upload"></span> Upload CSV</a></li>
         </ul>
      </li>
    </ul>
    <ul class="nav navbar-nav navbar-right">
          <li><p class='logged-in-as hidden-xs'>Logged in as <?php print User::model()->getUser()->email; ?> (<?=User::model()->getUser()->adminType;?>) </p></li>

        <li><a  href="/logout">Logout</a></li>
  </ul>
</div>




