<div class="query_builder">
	<div class="page-header">
		<h1>Update Query</h1>
	</div>

<?php

$this->renderPartial('_form', array(
	'Query'=>$Query,
	'QueryQuestions'=>$QueryQuestions,
	'queryResults' => $queryResults
));
?>
</div>
