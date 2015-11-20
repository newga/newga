
						<div class="nav-respond">
							<ul class="nav navbar-nav navbar-right">
<?php

if(Yii::app()->user->role >= 50)
{
?>
								<li><?php print CHtml::link('Dashboard', array('site/dashboard')); ?></li>
<?php
}

?>
								<li><?php print CHtml::link('Register', array('accession/start')); ?></li>
								<li><?= CHtml::link('Login', array('site/login')); ?></li>
							</ul>
						</div>	