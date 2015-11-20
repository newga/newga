<h2>Update your contact details</h2>
<hr>

<?php

foreach(Yii::app()->accessionUser->getFlashes() as $state => $message){

	?><p class="alert alert-<?= $state; ?>"><strong>Success!</strong> <?= $message; ?></p><?php
}

?>

<?php print $this->renderPartial('_details_form', array(
	'Accession' => $Accession,
	'Store' => $Store,
	'progress' => 2,
	'passwordIsSet' => $passwordIsSet,
	'updateDetails' => $updateDetails,
)); ?>