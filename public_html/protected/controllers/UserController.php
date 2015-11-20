<?php

class UserController extends Controller
{

	/**
	 * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
	 * using two-column layout. See 'protected/views/layouts/column2.php'.
	 */
	public $layout='/layouts/admin';

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
				'expression' => 'Yii::app()->user->role >= USER::ROLE_MANAGER',
			),
			array('allow',
				'actions'=>array('updateBasic'),
				'expression' => 'Yii::app()->user->role >= USER::ROLE_ORGANISATION',
			),
			
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}


	public function actionIndex() {

		$this->pageTitle = 'Users | ' . Yii::app()->name;	
		$this->breadcrumbs=array(
			'Users',
		);

		$User = new User('search');
		$User->unsetAttributes();

		if($_GET['User']) {
			$User->attributes = $_GET['User'];
		}

		$this->render('index', array(
			'User' => $User,
		));

	}



	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
		$User = new User();

		if(isset($_POST['User']))
		{
			$User->attributes = $_POST['User'];
			
			//Protect against users being able to view people with a higher role
			if ($User->role > Yii::app()->user->role) {
				throw new CHttpException(404, 'You are not authorised to view this user.');
			}

			//force organisation to be 0 if manager or above			
			if ($User->role >= User::ROLE_MANAGER) {
				$User->organisation_id = 0;
			}



			$User->verified = 0;

			
			

			if($User->save())
			{
				//set hash and email
				$User->sendInvitation();
				
				Yii::app()->user->setFlash('success', 'New user ' . $User->first_name . ' ' . $User->last_name . ' created. They will receive an email invitation.');
				
				$this->redirect(array('index'));
			}
		}

		$this->breadcrumbs=array(
			'Users' => array('index'),
			'Create User',
		);

		$this->render('create',array(
			'User'=>$User,
			'formDetail' => 'full'
		));
	}
	
	public function actionUpdateBasic()
	{
		$User = User::model()->findByPk(Yii::app()->user->id);
		
		if(!$User) {
			throw new CHttpException(404, 'Not Found. That user does not exist.');
		}
		
		if(isset($_POST['User']))
		{
			$User->first_name = $_POST['User']['first_name'];
			$User->last_name = $_POST['User']['last_name'];
			$User->email = $_POST['User']['email'];
			$User->updated = date("Y-m-d H:i:s");
			$User->password1 = $_POST['User']['password1'];
			$User->password2 = $_POST['User']['password2'];
			
			if($User->save(true, array(
				'first_name',
				'last_name',
				'email',
				'updated',
				'password',
			)))
			{
				Yii::app()->user->setFlash('success', ($User->id == Yii::app()->user->id ? 'You\'ve been ' : $User->fullName) . ' updated successfully.');
				
				// Changed to refresh so you aren't always sent to your own profile
				// even when editing someone else
				$this->refresh();
			}
		}
		
		
		$this->render('update', array(
			'User' => $User,
			'h1' => 'Your account details',
			'formDetail' => 'basic',
		));
	}


	public function actionUpdate($id) {
		
		$User = User::model()->findByPk($id);

		if(is_null($User) || $User->mothballed) {
			throw new CHttpException(404, 'Page not Found.');
		}

		//Protect against users being able to view people with a higher role
		if ($User->role > Yii::app()->user->role) {
			throw new CHttpException(404, 'Page not Found.'); // Generic error, not 'You are not authorised' which gives away the ID
		}

		if(isset($_POST['User']))
		{
			if(isset($_POST['archive'])){

				if(
					Yii::app()->user->role >= User::ROLE_MANAGER &&  	// At least manager
					$User->id != Yii::app()->user->id &&				// Not themself
					Yii::app()->user->role >= $User->role 				// Must be equal or greater role
				)
				{
					$User->scenario = 'mothballing';
					$User->email = ''; // database doesn't allow null.
					$User->mothballed = 1;
					$User->save();
					
					Yii::app()->user->setFlash('success', CHtml::encode($User->fullName) . ' archived successfully');
					
					$this->redirect(array('index'));
				}
			}

			//$User->attributes = $_POST['User'];
			$User->first_name = $_POST['User']['first_name'];
			$User->last_name = $_POST['User']['last_name'];
			$User->email = $_POST['User']['email'];
			$User->organisation_id = $_POST['User']['organisation_id'];
			$User->updated = date("Y-m-d H:i:s");
			$User->password1 = $_POST['User']['password1'];
			$User->password2 = $_POST['User']['password2'];
			
			// Alter user role
			// Is this user trying to elevate a user above their own role?
			if($_POST['User']['role'] > Yii::app()->user->role)
			{
				$User->addError('role', 'Role is invalid');
			}
			elseif(!in_array($_POST['User']['role'], array(User::ROLE_ORGANISATION, User::ROLE_MANAGER, User::ROLE_SUPERADMIN)))
			{
				$User->addError('role', 'Role is invalid'); // Same error, don't give anything away
			}
			else
			{
				$User->role = $_POST['User']['role'];
			}
			
			
			//force organisation to be 0 if manager or above
			if ($User->role >= User::ROLE_MANAGER) {
				$User->organisation_id = 0;
			}
			
			if($User->validate(null, false))
			{
				if($User->save())
				{
					Yii::app()->user->setFlash('success', ($User->id == Yii::app()->user->id ? 'You\'ve been ' : $User->fullName) . ' updated successfully.');
					
					// Changed to refresh so you aren't always sent to your own profile
					// even when editing someone else
					$this->refresh();
				}
			}
		}


		$this->pageTitle = 'Update ' . $User->fullName . ' | Admin | ' . Yii::app()->name;
		
		$this->breadcrumbs=array(
			'Users' => array('user/index'),
			'Update ' . $User->fullName,
		);

		
		// clear. We don't show in the form.
		$User->password = '';

		$this->render('update', array(
			'User' => $User,
			'h1' => 'Update User',
			'formDetail' => 'full',
		));

	}

}

?>