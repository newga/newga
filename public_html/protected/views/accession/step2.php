<?php print $this->renderPartial('_progressbar', array(
	'progress' => $progress,
)); ?>

<h2>Confirming your contact details</h2>
<hr>

<?php print $this->renderPartial('_details_form', array(
	'Accession' => $Accession,
	'Store' => $Store,
	'progress' => 2,
	'passwordIsSet' => $passwordIsSet,
	'salutations' => $salutations,
	'counties' => $counties,
)); ?>
