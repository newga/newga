<?php

class VenueController extends Controller
{
	
	public $layout = '/layouts/admin';
	
	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
		);
	}

	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules()
	{
		return array(
			
			array('allow',
				'actions'=>array('index','create','update'),
				'expression' => 'Yii::app()->user->role >= ' . User::ROLE_MANAGER,
			),

			array('allow',
				'actions'=>array('error'),
				'users'=>array('*'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}
	
	/**
	 * This is the default 'view' action that is invoked
	 * when an action is not explicitly requested by users.
	 */
	public function actionIndex()
	{
		$this->pageTitle = 'Venues | ' . Yii::app()->name;
	
		$Venue = new Venue('search');
		$Venue->unsetAttributes();  // clear any default values
		
		//for grid search
		if(isset($_GET['Venue'])) {
        	$Venue->attributes=$_GET['Venue'];
        }

        $this->breadcrumbs=array(
			'Venues'
		);
		
		$this->render('index',array(
			'Venue'=>$Venue,
		));

	}


	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
		$Venue = new Venue();
		

		if(isset($_POST['Venue']))
		{
			$Venue->attributes = $_POST['Venue'];
					
			if($Venue->save())
			{
				$this->redirect(array('index'));
			}
		}

		$this->breadcrumbs=array(
			'Venues' => array('index'),
			'Create Venue'
		);

		$this->render('create',array(
			'Venue'=>$Venue,
		));
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id)
	{
		$Venue = $this->loadModel($id);

		if(isset($_POST['Venue']))
		{
			$Venue->attributes = $_POST['Venue'];
	
			if($Venue->save())
			{
				Yii::app()->user->setFlash('success', 'Venue details saved');
				//$this->redirect(array('update','id'=>$Venue->id));
			}
			
		}

		$this->breadcrumbs=array(
			'Venues' => array('index'),
			'Update Venue'
		);

		$this->render('update',array(
			'Venue'=>$Venue,
		));
	}


	public function loadModel($id)
	{
		$Model=Venue::model()->findByPk($id);
		if($Model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $Model;
	}
	
	
}

?>