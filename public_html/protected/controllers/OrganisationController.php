<?php

class OrganisationController extends Controller
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
				'expression' => 'Yii::app()->user->role >= ' . User::ROLE_SUPERADMIN,
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
	
		$Organisation = new Organisation('search');
		$Organisation->unsetAttributes();  // clear any default values
		
		//for grid search
		if(isset($_GET['Organisation'])) {
        	$Organisation->attributes = $_GET['Organisation'];
        }

		$this->pageTitle = 'Organisations | ' . Yii::app()->name;
        $this->breadcrumbs=array(
			'Organisations'
		);
		
		$this->render('index',array(
			'Organisation'=>$Organisation,
		));

	}


	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
		$Organisation = new Organisation();
		

		if(isset($_POST['Organisation']))
		{
			$Organisation->attributes = $_POST['Organisation'];
					
			if($Organisation->save())
			{
				$this->redirect(array('index'));
			}
		}

		$this->pageTitle = 'Create Organisation | ' . Yii::app()->name;
		$this->breadcrumbs=array(
			'Organisations' => array('index'),
			'Create Organisation'
		);

		$this->render('create',array(
			'Organisation'=>$Organisation,
		));
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id)
	{
		$Organisation = $this->loadModel($id);
		
		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Organisation']))
		{
			$Organisation->attributes = $_POST['Organisation'];
	
			if($Organisation->save())
			{
				Yii::app()->user->setFlash('success', 'Organisation details saved');
				//$this->redirect(array('update','id'=>$Organisation->id));
			}
			
		}

		$this->pageTitle = 'Update Organisation | ' . Yii::app()->name;
		$this->breadcrumbs = array(
			'Organisations' => array('index'),
			'Update Organisation'
		);

		$this->render('update', array(
			'Organisation'=>$Organisation,
		));
	}


	public function loadModel($id)
	{
		$Model = Organisation::model()->findByPk($id);
		if($Model === null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $Model;
	}
	
	
}

?>