<?php


// Include jQuery on every page
//Yii::app()->clientScript->registerCoreScript('jquery', CClientScript::POS_END);

// Include Bootstrap JS
//Yii::app()->clientScript->registerScriptFile('/js/bootstrap.min.js', CClientScript::POS_END);


// Include Admin JS
//Yii::app()->clientScript->registerScriptFile('/js/scripts.js', CClientScript::POS_END);

?>
<!DOCTYPE html>
<html lang="en">
	<head>

	<meta charset="utf-8">

	
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/bootstrap.min.css" media="screen" />
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/style.css" media="screen" />

	<title><?php echo CHtml::encode($this->pageTitle); ?></title>



	</head>


	<body role="document" class="admin">

		 <!-- Fixed navbar -->
		 <div class="navbar navbar-default navbar-fixed-top" role="navigation">
		  	<div class="container">

		  		<div class="row">
					<div class="col-sm-12">
					
				

					  		<div class="navbar-header">
							 
						 		<a href="/"><img  class="logo" src="/css/assets/logo.png" width="90" alt="Application Name Logo"/></a>
					   	</div>
			 	
						<?php echo $this->renderPartial('//layouts/_nav_guest'); ?>
					</div>
				</div>
			</div>
		 </div>



		<div class="container content">
			

	<?php if(isset($this->breadcrumbs) && sizeof($this->breadcrumbs) && Yii::app()->user->id):?>
			<div class="row">
				<div class="col-sm-12">
		<?php $this->widget('zii.widgets.CBreadcrumbs', array(
			'links'=>$this->breadcrumbs,
			'homeLink' => '<li><a href="/">Dashboard</a></li>',
			'separator' => '',
			'tagName' => 'ol',
			'activeLinkTemplate'=>'<li><a href="{url}">{label}</a></li>',
			'inactiveLinkTemplate'=>'<li>{label}</li>',
			'htmlOptions' => array(
				'class' => 'breadcrumb'
			),
		)); ?><!-- breadcrumbs -->
				</div>
			</div>
 	<?php endif?>
				



			<div class="row">
				<div class="col-sm-12">
					<?php echo $content; ?>
				</div>
			</div>
			
		</div>
	</body>
</html>
