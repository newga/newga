<?php


// Include jQuery on every page
Yii::app()->clientScript->registerCoreScript('jquery', CClientScript::POS_END);

// Include Bootstrap JS
Yii::app()->clientScript->registerScriptFile('/js/bootstrap.min.js', CClientScript::POS_END);


// Include Admin JS
Yii::app()->clientScript->registerScriptFile('/js/scripts.js', CClientScript::POS_END);

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
						 
							   <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
								 <span class="sr-only">Toggle navigation</span>
								 <span class="icon-bar"></span>
								 <span class="icon-bar"></span>
								 <span class="icon-bar"></span>
							   </button>
						 		<a href="/"><img  class="logo" src="/images/logo.png" width="" alt=""/></a>
					   	</div>
			 	
					  		<?php
					  		//nav swith on user type
					  		switch (Yii::app()->user->role) {
							case User::ROLE_SUPERADMIN:
								echo $this->renderPartial('//layouts/_nav_super');
								break;
							case User::ROLE_MANAGER:
								echo $this->renderPartial('//layouts/_nav_manager');
								break;
							case User::ROLE_ORGANISATION:
								echo $this->renderPartial('//layouts/_nav_organisation');
								break;
							default:
								echo $this->renderPartial('//layouts/_nav_guest');
						}					 		
					  		?>
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
			'homeLink' => '<li><a href="/dashboard">Dashboard</a></li>',
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
