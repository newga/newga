<?php

class AccessionController extends Controller
{
	
	public $layout = '/layouts/accession';
	public $lastStep = 8;
	
	
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
				'actions'=>array('updateDetails'),
				'expression' => '!Yii::app()->accessionUser->isGuest'
			),
			

			// Assession is publically available and so all steps need to be available when not logged in.
			array('allow',
				'actions'=>array('invite', 'start','assign','stepOne','stepTwo','stepThree','stepFour', 'stepFive','stepSix', 'stepSeven', 'stepEight', 'complete'),
				'users'=>array('*'),
			),


			array('allow',
				'actions'=>array('error'),
			),

			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}



	public function actionUpdateDetails()
	{
		$Store = Store::model()->findByPk(Yii::app()->accessionUser->id);
		
		$Store->scenario = 'updateDetails';
		
		$Store2Contact = Store2Contact::model()->find(array(
			'condition' => 'store_id = :store_id',
			'params' => array(
				':store_id' => $Store->id,
			),
		));
		
		$Accession = Accession::model()->find(array(
			'condition' => 'warehouse_id = :warehouse_id',
			'params' => array(
				':warehouse_id' => $Store2Contact->contact_warehouse_id,
			),
		));
		
		$this->pageTitle = 'Update your details | ' . Yii::app()->name;
		
		if(isset($_POST['Accession']))
		{
			// Save form etc
			$Store->attributes = $_POST['Store'];
			
			if(strlen($_POST['Accession']['age']))
			{
				$Accession->age = $_POST['Accession']['age'];
				
				if(in_array((int)$_POST['Accession']['age'], range(1,120)))
				{
					$Store->dob = date('Y-m-d', strtotime((int)$_POST['Accession']['age'] . ' years ago'));
				}
				else
				{
					$Accession->addError('age', 'Age was not recognised');
				}
			}
			
			// Password?
			if(strlen($_POST['Accession']['password']))
			{
				$Accession->password = $_POST['Accession']['password'];
				$Accession->password_repeat = $_POST['Accession']['password_repeat'];
				
				// Check theyre the same
				if($_POST['Accession']['password'] != $_POST['Accession']['password_repeat'])
				{
					$Accession->addError('password', 'Both password fields must match');
				}
				else
				{
					$updatePassword = true;
				}
			}
			
			if($Accession->validate(null, false))
			{
				if($updatePassword){ $Accession->password = hash('sha256', $_POST['Accession']['password'] . SHASALT); }
				
				if($Accession->validate() && $Store->validate())
				{
					$Accession->save();
					$Store->save();

					Yii::app()->accessionUser->setFlash('success', 'Your details were saved');
					$this->refresh();
				}
				else
				{
					if(!strlen($_POST['Accession']['password']))
					{
						$Accession->password = '';
					}
					
					if(!strlen($_POST['Accession']['password_repeat']))
					{
						$Accession->password_repeat = '';
					}
				}
			}
			else
			{
				
			}
		}
		else
		{
			$Accession->password = '';
			$Accession->password_repeat = '';
			
			// Age has been calculated and saved as a guessed DOB
			$date = new DateTime($Store->dob);
			$now = new DateTime();
			$Accession->age = $now->diff($date)->y;
			
			if($Accession->age == 0)
			{
				$Accession->age = null;
			}
		}
		
		$this->render('updateDetails', array(
			'Accession' => $Accession,
			'Store' => $Store,
			'passwordIsSet' => true,
			'updateDetails' => true,
		));
	}



	public function actionInvite()
	{
		// Load the invite based on the hash
		$Invite = Invite::model()->find(array(
			'condition' => 'hash = :invite_hash',
			'params' => array(
				':invite_hash' => $_GET['invite_hash']
			),
		));
		
		if(is_null($Invite))
		{
			throw new CHttpException(404, 'Invite not found.');
		}
		
		// Set new status of invite
		if($Invite->status < Invite::STATUS_LINK_FOLLOWED)
		{
			$Invite->status = Invite::STATUS_LINK_FOLLOWED;
			$Invite->save(true, array('status'));
		}

		// Has this person already been through accession?
		$Accession = Accession::model()->find(array(
			'condition' => 'warehouse_id = :warehouse_id',
			'params' => array(
				':warehouse_id' => $Invite->contact_warehouse_id,
			),
		));
		
		if(!is_null($Accession))
		{
			// Did they only get half way through accession and give up?
			if($Accession->step < $this->lastStep)
			{
				// Lets take them to the step at which they gave up
				$this->redirect(array('accession/step' . $this->numbersToWords($Accession->step), 'accessionhash' => $Accession->accession_hash));
			}
			// Did they complete the process?
			elseif((int)$Accession->step === (int)$this->lastStep)
			{
				// Take them to the complete page
				$this->redirect(array('accession/complete', 'accessionhash' => $Accession->accession_hash));
			}
		}
		
		$Accession = new Accession;
		$Accession->invite_id = $Invite->id;
		$Accession->step = 1;
		$Accession->accession_hash = $Invite->hash;
		$Accession->original_store2contact_id = $Invite->store2contact_id;
		$Accession->warehouse_id = $Invite->contact_warehouse_id;
		$Accession->save(true, array('original_store2contact_id','accession_hash','warehouse_id','step', 'invite_id'));
		
		// redirect to start the forms
		$this->redirect(array('accession/stepOne', 'accessionhash' => $Accession->accession_hash));
	}



	public function actionStart()
	{
		// SOMEONE SIGNING UP WITH NO PREVIOUS RECORD

		// redirect to start the forms
		$this->redirect(array('accession/stepOne'));
	}



	public function getAccessionRecord()
	{
		$Accession = Accession::model()->find(array(
			'condition' => 'accession_hash = :accession_hash',
			'params' => array(
				':accession_hash' => $_GET['accessionhash'],
			),
		));
		
		if(is_null($Accession))
		{
			throw new CHttpException(404,'Accession record not found');
		}
		
		return $Accession;
	}



	public function getWarehouse($Accession)
	{
		return Warehouse::model()->findByPk($Accession->warehouse_id);
	}



	public function getStore2Contact($store2ContactID)
	{
		return Store2Contact::model()->findByPk($store2ContactID);
	}



	public function getStore($storeID)
	{
		return Store::model()->findByPk($storeID);
	}



	public function actionStepOne()
	{
		$this->inAccession = true;
		
		// Have we arrived here with an accession hash, allowing us to track this contact through accession?
		if(isset($_GET['accessionhash']))
		{
			$Accession = $this->getAccessionRecord();
			
			$this->checkStep($Accession, 1);
		}
		else
		{
			$Accession = new Accession;
			$Accession->step = 1;
		}
		
		$this->pageTitle = 'Welcome to ' . Yii::app()->name . ' | Step One | Accession';
		
		if(isset($_POST['Accession']))
		{
			if($_POST['Accession']['terms_agreed'] === '1')
			{
				// Accession hash will be null if they've signed up from the public link
				if(is_null($Accession->accession_hash))
				{
					$Accession->accession_hash = sha1(rand(1,99999).microtime(true));
				}
				
				// TERMS ARE AGREED! We're good to go. Set up all the models
				$Accession->terms_agreed = date('Y-m-d H:i:s');
				
				
				
				if(!$Accession->save())
				{
					print_r($Accession->errors);exit();
				}
				
				// Now they've agreed terms, update the invite (if they had one)
				if($Accession->invite_id)
				{
					$Invite = Invite::model()->findByPk($Accession->invite_id);
					
					if(!is_null($Invite))
					{
						$Invite->status = Invite::STATUS_ACCEPTED;
						$Invite->save(true, array('status'));
					}
				}
				
				$new = false;
				
				// If it's a new contact coming to the list, we just have a blank row
				// If they've come via an invite then we copy their Store row
				if(is_null($Accession->original_store2contact_id))
				{
					// This contact is new
					$Store = new Store;
					
					// Create a new warehouse row
					$Warehouse = new Warehouse;
					$Warehouse->save();
					
					// Set the warehouse id to the accession model
					$Accession->warehouse_id = $Warehouse->id;
					
					$Accession->save(true, array('warehouse_id'));
					
					$new = true;
				}
				else
				{
					// This contact came from an invite
					// Grab their previous data
					$Store2Contact = Store2Contact::model()->findByPk($Accession->original_store2contact_id);
					
					// Get the contact's warehouse_id - this identifies them uniquely, even if they have multiple instances in Store
					$Warehouse = Warehouse::model()->findByPk($Store2Contact->contact_warehouse_id);
					
					$ExistingStore = Store::model()->findByPk($Store2Contact->store_id);
					
					// Now make a new store to duplicate their info to
					$Store = new Store;
					$Store->attributes = $ExistingStore->attributes;
					$Store->id = null;
				}
				
				// Set the org ID to THE LIST
				$Store->origin_organisation_id = 10;
				
				// Try to save the Store
				if(!$Store->save())
				{
					print 'Store errors:<br>';
					print_r($Store->errors);exit();
				}
				
				// Also create a new Store2Contact row
				$Store2Contact = new Store2Contact;
				$Store2Contact->store_id = $Store->id;
				$Store2Contact->contact_warehouse_id = $Warehouse->id;
				$Store2Contact->origin_id = 10;
				
				if(!$Store2Contact->save())
				{
					print 'Store2Contact errors:<br>';
					print_r($Store2Contact->errors);exit();
				}
			
				// Now also save the new Store2Contact ID to Accession
				$Accession->store2contact_id = $Store2Contact->id;
				
				if($new)
				{
					$Accession->original_store2contact_id = $Store2Contact->id;
				}
			}
			
			if($Accession->save(true, array('terms_agreed','store2contact_id','original_store2contact_id')))
			{
				$this->updateStep($Accession, 1);
				
				$this->redirect(array('accession/stepTwo', 'accessionhash' => $Accession->accession_hash));
			}
		}
		
		
		$this->render('step1', array(
			'Accession' => $Accession,
			'progress' => 1,
		));
	}



	public function actionStepTwo()
	{
		$this->inAccession = true;
		
		$Accession = $this->getAccessionRecord();
		$Accession->scenario = 'stepTwo';
		$this->checkStep($Accession, 2);

		$this->pageTitle = 'Contact Details | Step Two | Accession';

		// in this step we're saving contact details like name, email and addresses. We'll need to store that in the store

		// Get the new Store row we created for this contact
		// If the're new, it will be blank
		// If they came from an invite, it will be a copy of their previous store row
		$Store2Contact = $this->getStore2Contact($Accession->store2contact_id);
		$Store = $this->getStore($Store2Contact->store_id);
		
		$Store->scenario = 'accessionPersonalDetails';
		
		$passwordIsSet = (bool)strlen($Accession->password);
		$updatePassword = false;
		
		
		if(isset($_POST['Accession']))
		{
			// Save form etc
			$Store->attributes = $_POST['Store'];
			
			if(strlen($_POST['Accession']['age']))
			{
				$Accession->age = $_POST['Accession']['age'];
				
				if(in_array((int)$_POST['Accession']['age'], range(1,120)))
				{
					$Store->dob = date('Y-m-d', strtotime((int)$_POST['Accession']['age'] . ' years ago'));
				}
				else
				{
					$Accession->addError('age', 'Age was not recognised');
				}
			}
			
			// Password?
			if(strlen($_POST['Accession']['password']))
			{
				$Accession->password = $_POST['Accession']['password'];
				$Accession->password_repeat = $_POST['Accession']['password_repeat'];
				
				// Check theyre the same
				if($_POST['Accession']['password'] != $_POST['Accession']['password_repeat'])
				{
					$Accession->addError('password', 'Both password fields must match');
					$Accession->addError('password_repeat', 'Both password fields must match');
				}
				else
				{
					$updatePassword = true;
				}
			}
			
			$accessionValid = $Accession->validate(null, false);
			$storeValid = $Store->validate(null, false);
			
			if($accessionValid && $storeValid)
			{
				if($updatePassword){ $Accession->password = hash('sha256', $_POST['Accession']['password'] . SHASALT); }
				
				if($Accession->validate() && $Store->validate())
				{
					$Accession->save();
					$Store->save();

					$this->updateStep($Accession, 2);
					
					$this->redirect(array('accession/stepThree', 'accessionhash' => $Accession->accession_hash));
				}
			}
		}
		else
		{
			// Age has been calculated and saved as a guessed DOB
			$date = new DateTime($Store->dob);
			$now = new DateTime();
			$Accession->age = $now->diff($date)->y;
			
			if($Accession->age == 0)
			{
				$Accession->age = null;
			}
			
			$Accession->password = '';
			$Accession->password_repeat = '';
		}
		
		$this->render('step2', array(
			'Accession' => $Accession,
			'Store' => $Store,
			'progress' => 2,
			'passwordIsSet' => $passwordIsSet,
			'salutations' => $Store->getSalutations(),
			'counties' => $Store->getCounties(),
		));
		
		
	}



	public function actionStepThree()
	{
		$this->inAccession = true;
		
		// In this step we're saving how many children the contact has. Only need to save into Accession table
		$Accession = $this->getAccessionRecord();
		
		$this->checkStep($Accession, 3);
		
		$this->pageTitle = 'Children | Step Three | Accession';
		
		$Accession->scenario = 'stepThree';
		
		if(isset($_POST['Accession']))
		{
			// Save form etc
			$Accession->children = $_POST['Accession']['children'];
			
			if($Accession->save(true, array('children')))
			{
				$this->updateStep($Accession, 3);
				
				$this->redirect(array('accession/stepFour', 'accessionhash' => $Accession->accession_hash));
			}
		}
		else
		{
			if((int)$Accession->children === 0)
			{
				$Accession->children = null;
			}
		}
		
		
		$this->render('step3', array(
			'Accession' => $Accession,
			'progress' => 3,
		));
	}



	// ! CHILD AGES
	public function actionStepFour()
	{
		$this->inAccession = true;
		
		$Accession = $this->getAccessionRecord();
		
		$this->checkStep($Accession, 4);
		
		$this->pageTitle = 'Child Ages | Step Four | Accession';
		
		// No children? Skip this step
		if((int)$Accession->children < 1)
		{
			$this->updateStep($Accession, 4);
			$this->redirect(array('accession/stepFive', 'accessionhash' => $Accession->accession_hash));
		}
		
		if(isset($_POST['Child']))
		{
			$childAges = $_POST['Child'];
			
			// Loop the ages and validate them
			for($i = 0; $i < (int)$Accession->children; $i++)
			{
				if(!strlen($childAges[$i]))
				{
					$Accession->addError('child_ages', 'You must supply an age for child ' . ($i+1));
				}
				elseif(!is_numeric($childAges[$i]))
				{
					$Accession->addError('child_ages', 'Child ' . ($i+1) . ' age must be numeric');
				}
				elseif(!preg_match('@^\d{1,2}$@', $childAges[$i]))
				{
					$Accession->addError('child_ages', 'Child ' . ($i+1) . ' age must be a whole number');
				}
				elseif($childAges[$i] > 17)
				{
					$Accession->addError('child_ages', 'Child ' . ($i+1) . ' is not under 18 and should not be included');
				}
			}
			
			// No errors?
			if(!sizeof($Accession->errors))
			{
				// Save child ages as JSON
				$Accession->child_ages = json_encode($childAges);
				
				if($Accession->save(true, array('child_ages')))
				{
					$this->updateStep($Accession, 4);
					
					$this->redirect(array('accession/stepFive', 'accessionhash' => $Accession->accession_hash));
				}
				else
				{
					print_r($Accession->errors);
				}
			}
			
			
		}
		else
		{
			$childAges = (array)json_decode($Accession->child_ages, true);
		}
		
		
		
		$this->render('step4', array(
			'Accession' => $Accession,
			'progress' => 4,
			'childAges' => $childAges,
		));
	}



	public function actionStepFive()
	{
		$this->inAccession = true;
		
		$Accession = $this->getAccessionRecord();
		
		$this->checkStep($Accession, 5);
		
		$this->pageTitle = 'Approach to Life  | Step Five | Accession';
		
		$CSSurvey = new CultureSegmentsForm;
		$CSSurvey->scenario = 'step5';
		
		if(isset($_POST['submit-cs-form']))
		{

			$CSSurvey->attributes = $_POST['CultureSegmentsForm'];
			
			if($CSSurvey->validate())
			{
				
				$fieldsToSave = array(
					'q2_1',
					'q2_2',
					'q2_3',
					'q2_4',
					'q2_5',
					'q2_6',
					'q2_7',
					'q2_8',
					'q17',
					'q18',
					'q1_1',
					'q1_2',
					'q1_3',
				);
				
				$jsonArray = array();
				
				foreach($fieldsToSave as $fieldToSave)
				{
					$jsonArray[$fieldToSave] = $_POST['CultureSegmentsForm'][$fieldToSave];
				}
				
				$Accession->cs_answers = json_encode($jsonArray);
				
				if($Accession->save(true, array('cs_answers')))
				{

					/*
					 * Here is where you would add code to segment
					 * a person based on their attributes.
					 */

					$Accession->culture_segment = 'unknown';
					$Accession->level_of_engagement = 'unknown';


					if($Accession->save())
					{
						$this->updateStep($Accession, 5);
						
						$this->redirect(array('accession/stepSix', 'accessionhash' => $Accession->accession_hash));
					}
					else
					{
						$CSSurvey->addError('error', 'Error saving Accession information');
					}
				}
			}
			
			
		}
		else
		{
			$CSSurvey->attributes = json_decode($Accession->cs_answers, true);
		}
		
		$datapoints = json_decode($Accession->cs_answers, true);
		
		$this->render('step5', array(
			'Accession' => $Accession,
			'progress' => 5,
			'CSSurvey' => $CSSurvey,
			'datapoints' => $datapoints,
		));
	}



	public function actionStepSix()
	{
		$this->inAccession = true;
		
		$Accession = $this->getAccessionRecord();
		
		$this->checkStep($Accession, 6);
		
		$this->pageTitle = 'Visited Venues | Step Six | Accession';
		
		$Venues = Venue::model()->findAll();
		
		if(isset($_POST['submit-venues']))
		{
			if(sizeof($_POST['Venues']) < sizeof($Venues))
			{
				$Accession->addError('id', 'You must answer all the questions');
			}
			else
			{
				foreach($_POST['Venues'] as $venue_id => $visited)
				{
					// Record already exists?
					$Contact2Venue = Contact2Venue::model()->find(array(
						'condition' => 'accession_id = :accession_id AND venue_id = :venue_id',
						'params' => array(
							':accession_id' => $Accession->id,
							':venue_id' => $venue_id,
						),
					));
					
					if(!is_null($Contact2Venue))
					{
						// Update the exisiting record
						$Contact2Venue->visited = $visited;
						$Contact2Venue->save(true, array('visited'));
					}
					else
					{
						$Contact2Venue = new Contact2Venue;
						$Contact2Venue->accession_id = $Accession->id;
						$Contact2Venue->venue_id = $venue_id;
						$Contact2Venue->visited = $visited;
						$Contact2Venue->save();
					}
				}
				
				$this->updateStep($Accession, 6);
				
				$this->redirect(array('accession/stepSeven', 'accessionhash' => $Accession->accession_hash));
			}
			
			$visitedVenues = $_POST['Venues'];
		}
		else
		{
			// Get existing choices
			$Contact2Venues = Contact2Venue::model()->findAll(array(
				'condition' => 'accession_id = :accession_id',
				'params' => array(
					':accession_id' => $Accession->id,
				),
			));
			
			$visitedVenues = array();
			
			foreach($Contact2Venues as $visitedVenue)
			{
				$visitedVenues[$visitedVenue->venue_id] = $visitedVenue->visited;
			}
		
		}
		
		$this->render('step6', array(
			'Accession' => $Accession,
			'progress' => 6,
			'Venues' => $Venues,
			'visitedVenues' => $visitedVenues,
		));
	}



	public function actionStepSeven()
	{
		$this->inAccession = true;
		
		$Accession = $this->getAccessionRecord();
		
		$this->checkStep($Accession, 7);
		
		$this->pageTitle = 'Visited Artforms | Accession';
		
		$Artforms = Artform::model()->findAll();
		
		if(isset($_POST['submit-artforms']))
		{
			if(sizeof($_POST['Artforms']) < sizeof($Artforms))
			{
				$Accession->addError('id', 'You must answer all the questions');
			}
			else
			{
				foreach($_POST['Artforms'] as $artform_id => $visited)
				{
					// Record already exists?
					$Contact2Artform = Contact2Artform::model()->find(array(
						'condition' => 'accession_id = :accession_id AND artform_id = :artform_id',
						'params' => array(
							':accession_id' => $Accession->id,
							':artform_id' => $artform_id,
						),
					));
					
					if(!is_null($Contact2Artform))
					{
						// Update the exisiting record
						$Contact2Artform->visited = $visited;
						$Contact2Artform->save(true, array('visited'));
					}
					else
					{
						$Contact2Artform = new Contact2Artform;
						$Contact2Artform->accession_id = $Accession->id;
						$Contact2Artform->artform_id = $artform_id;
						$Contact2Artform->visited = $visited;
						
						if(!$Contact2Artform->save())
						{
							print_r($Contact2Artform->errors);
							exit();
						}
					}
				}
				
				$this->updateStep($Accession, 7);
				
				$this->redirect(array('accession/complete', 'accessionhash' => $Accession->accession_hash));
			}
			
			$visitedArtforms = $_POST['Artforms'];
		}
		else
		{
			// Get existing choices
			$Contact2Artforms = Contact2Artform::model()->findAll(array(
				'condition' => 'accession_id = :accession_id',
				'params' => array(
					':accession_id' => $Accession->id,
				),
			));
			
			$visitedArtforms = array();
			
			foreach($Contact2Artforms as $visitedArtform)
			{
				$visitedArtforms[$visitedArtform->artform_id] = $visitedArtform->visited;
			}
		}
		
		
		
		
		$this->render('step7', array(
			'Accession' => $Accession,
			'progress' => 7,
			'Artforms' => $Artforms,
			'visitedArtforms' => $visitedArtforms,
		));
	}
	
	
	public function actionComplete()
	{
		$Accession = $this->getAccessionRecord();
		
		$this->checkStep($Accession, $this->lastStep);
		
		$this->pageTitle = 'Complete | Accession';
		
		$Accession->complete = 1;
		$Accession->save(true, array('complete'));
		
		$this->render('complete');
	}


	public function checkStep($Accession, $step)
	{
		if($Accession->complete && $step != $this->lastStep)
		{
			$this->redirect(array('accession/complete', 'accessionhash' => $Accession->accession_hash));	
		}
		
		// Trying to go back to first step?
		if($step == 1 && $Accession->step > 1)
		{
			if((int)$Accession->step === (int)$this->lastStep)
			{
				$this->redirect(array('accession/complete', 'accessionhash' => $Accession->accession_hash));
			}
			else
			{
				$this->redirect(array('accession/step'.$this->numbersToWords($Accession->step), 'accessionhash' => $Accession->accession_hash));
			}
		}
		
		if((int)$step > 1 && (int)$Accession->step < (int)$step)
		{
			$this->redirect(array('accession/step'.$this->numbersToWords($Accession->step), 'accessionhash' => $Accession->accession_hash));
		}
	}


	public function updateStep($Accession, $step)
	{
		if((int)$step >= (int)$Accession->step)
		{
			$Accession->step = ($step + 1);
			if(!$Accession->save(true, array('step')))
			{
				exit('Failed to update step');
			}
		}
	}


	public function numbersToWords($number)
	{
		$array = array(
			1 => 'One',
			2 => 'Two',
			3 => 'Three',
			4 => 'Four',
			5 => 'Five',
			6 => 'Six',
			7 => 'Seven',
		);
		
		return $array[$number];
	}
}

?>