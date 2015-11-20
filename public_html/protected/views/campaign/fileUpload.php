
<h1>Upload pdf to <?= CHtml::encode($Campaign->name); ?></h1>

<?php

$this->renderPartial('fileForm', array(

	'Campaign' => $Campaign,
	'CampaignFile' => $CampaignFile,

));

?>