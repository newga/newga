<?php

foreach(Yii::app()->user->getFlashes() as $state => $message){

	?><p class="alert alert-<?= $state; ?>"><?= $message; ?></p><?php
}

?>
<div class="page-header">
	<h1>Export campaign groups</h1>
</div>

<?php

foreach($Campaign->groups as $Group){

?>
<div class="well well-sm">
	<h3><?= $Group->name; ?> <small>- <?= round($Group->fraction); ?>%</small></h3>
	<p><?= CHtml::link('Download contacts csv', array('campaignGroup/export', 'campaign_id' => $Campaign->id, 'id' => $Group->id), array('class' => 'btn btn-default')); ?></p>
</div>
<?php

}

?>