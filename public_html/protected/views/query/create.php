<div class="query_builder">
	<div class="page-header">
		<h1><?= ($invite ? 'Create Invite Query' : 'Build Query');?></h1>
	</div>

	<?php echo $this->renderPartial('_form', array(
		'Query'=>$Query,
		'QueryQuestions'=>$QueryQuestions,
		'invite'=>$invite,
		'queryResults' => $queryResults,
	)); ?>
</div>
