<?php

/* a plain layout used for email unsubscribes */
Yii::app()->clientScript->registerCoreScript('jquery', CClientScript::POS_END);

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">

	<link rel="stylesheet" type="text/css" href="/css/bootstrap.min.css" media="screen" />

	<title><?php echo CHtml::encode($this->pageTitle); ?></title>

</head>

<body role="document">

	<div class="container-fluid content">

		<div class="row">
			<div class="col-sm-12 col-sm-10 col-sm-offset-1">
				<?php echo $content; ?>
			</div>
		</div>
		
	</div>

</body>
</html>