<?php


// Include jQuery on every page
Yii::app()->clientScript->registerCoreScript('jquery', CClientScript::POS_END);

// Include Bootstrap JS
//Yii::app()->clientScript->registerScriptFile('/js/bootstrap.min.js', CClientScript::POS_END);

Yii::app()->clientScript->registerScriptFile('/js/respond.js', CClientScript::POS_END);

// Include Accessin JS
Yii::app()->clientScript->registerScriptFile('/js/accession.js', CClientScript::POS_END);

?>
<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="utf-8">

<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/bootstrap.min.css" media="screen" />
<?php
// loop various css files and add cache breakers
foreach(array(
	'style.css',
) as $cssFile){

$filemtime = filemtime(Yii::app()->basePath . '/../css/' . $cssFile);
$cssFile = preg_replace("@\.css$@", "", $cssFile);

?>
<link rel="stylesheet" type="text/css" href="/css/<?= $cssFile . '.' . $filemtime; ?>.css" media="screen" />
<?php
}
?>
<meta name="viewport" content="width=device-width,initial-scale=1.0">

<title><?php echo CHtml::encode($this->pageTitle); ?></title>



</head>


<body role="document">

	<div class="header">
		<div class="container-fluid">
			<div class="row">
				<div class="col-xs-12 col-sm-10 col-sm-offset-1">
				<a href="/"><img class="logo" src="/images/logo-<?=Yii::app()->params['site'];?>.png" width="200" alt="" /></a>

					<div class="navbar-default">
<?php
if(!$this->inAccession)
{
	if(Yii::app()->accessionUser->id)
	{
?>
						<?php echo $this->renderPartial('//layouts/_nav_accession'); ?>
<?php
	}
	else
	{
?>
						<?php echo $this->renderPartial('//layouts/_nav_guest'); ?>
<?php
	}
}
?>
						
					</div>
				</div>
			</div>
		</div>
	</div>

	
	<div class="container-fluid accession-content">
		
		<div class="row">
			<div class="col-xs-12 col-sm-10 col-sm-offset-1">

<?php if(isset($this->breadcrumbs) && sizeof($this->breadcrumbs)):?>
<ol class="breadcrumb">
	<?php $this->widget('zii.widgets.CBreadcrumbs', array(
		'links'=>$this->breadcrumbs,
		'homeLink' => '<a href="/">Home</a>',
		'separator' => ' / ',
	)); ?><!-- breadcrumbs -->
</ol>
	<?php endif?>

			</div>
		</div>


		<div class="row">
			<div class="col-xs-12 col-sm-10 col-sm-offset-1">
				<?php echo $content; ?>
			</div>
		</div>
		
	</div>
</body>
</html>
