<?php

/**
 * This is the model class for table "user".
 *
 * The followings are the available columns in table 'user':
 * @property integer $id
 * @property string $email
 * @property string $password
 * @property string $first_name
 * @property string $last_name
 * @property integer $image_id
 * @property string $reset_hash
 * @property integer $role
 * @property integer $verified
 * @property integer $mothballed
 * @property string $created
 * @property string $updated
 * @property string $short_bio
 * @property integer $bio_article_id
 */
class User extends CActiveRecord
{
	public $filterFullName; // used in cgridviews to allow db filtering
	
	public $password1;
	public $password2;

	const ROLE_SUPERADMIN = 200;
	const ROLE_MANAGER = 100;
	const ROLE_ORGANISATION = 50;

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return User the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'user';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(

			array('first_name, last_name, updated, email', 'required','on'=>'insert'),
			
			array('first_name, last_name, updated, email, role','required', 'on' => 'update'),
			
			// for mothballing
			array('email', 'email', 'allowEmpty' => true, 'on' => 'mothballing'),

			array('password1, password2', 'required', 'on' => 'resetPassword'),

			array('role, verified, mothballed', 'numerical', 'integerOnly'=>true),
			array('organisation_id', 'numerical', 'integerOnly'=>true, 'allowEmpty' => true),

			array('first_name, last_name', 'length', 'max'=>50),

        	array('email', 'email'),
			array('email', 'length', 'max'=>100),
			array('email', 'unique', 'criteria' => array(
                	'condition' => 'email != :email',
                	'params' => array(':email' => $this->email)
                )
        	),

			array('reset_hash', 'length', 'max'=>40),

			array('password,password1,password2', 'length', 'max'=>64, 'min' => 6),
            array('password1', 'compare', 'compareAttribute'=>'password2'),
			
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, email, first_name, last_name, filterFullName, created, updated, role, organisation', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'organisation'=>array(self::BELONGS_TO, 'Organisation', 'organisation_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'User',
			'email' => 'Email',
			'first_name' => 'First Name',
			'last_name' => 'Last Name',
			'image_id' => 'Image',
			'filterFullName' => 'Full Name',
			'reset_hash' => 'Reset Hash',
			'organisation_id' => 'Organisation',
			'password1' => 'Password',
			'password2' => 'Repeat Password',
		);
	}


	/*
	 * Scopes
	 */
	public function scopes() {
		return array(

		
		);
	}




	/* fires just before any ->validate() call */
	public function beforeValidate()
	{
		if($this->isNewRecord)
		{
			$this->created = date('Y-m-d H:i:s');
		}

		$this->updated = date('Y-m-d H:i:s');

		return parent::beforeValidate();
	}


	/*
	 * beforeSave()
	 */
	public function beforeSave() {
		//if password variable given, then save
		if (strlen($this->password1)) {
			$this->password = hash('sha256', $this->password1 . SHASALT);

		}
		return parent::beforeSave();

	}


	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search() {

		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria = new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('email',$this->email,true);
		$criteria->compare('first_name',$this->first_name,true);
		$criteria->compare('last_name',$this->last_name,true);
		$criteria->compare('role', $this->role);
		$criteria->compare('organisation_id', $this->organisation_id);

	

		// always bring this back
		$criteria->select = '*, CONCAT_WS(\' \', first_name, last_name) AS filterFullName';

		if(trim($this->filterFullName))
		{
			$criteria->compare('CONCAT_WS(\' \', first_name, last_name)', $this->filterFullName, true);
		}

		//you can only see users that are your level or below
		$criteria->addCondition('role <='.Yii::app()->user->role,'AND');
	
		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
			'sort'=>array(
				'attributes'=>array(
					'filterFullName'=>array(
						'asc' => 'CONCAT_WS(\' \', first_name, last_name)',
						'desc' => 'CONCAT_WS(\' \', first_name, last_name) DESC',
					),
					'*',
				),
				'defaultOrder'=>'first_name ASC',
			),
			'Pagination' => array (
                'PageSize' => 20
             ),
		));
	}

	
	public function getFullName() {

		return $this->first_name . ' ' . $this->last_name;
	}
	
	public function getUser($id = false) {

		if(!$id) {
			$id = Yii::app()->user->getId();
		}
		
		return User::model()->findByPk($id);
	}
	
	public function getActiveState($verified) {

		return ($verified) ? '<span class="green">Verified</span>':'<span class="red">Unverified</span>';
	}
	
	public function getLastLogin($dateTime) {
		
		if($dateTime > 0)
		{
			return date("jS M Y H:i", strtotime($dateTime));
		}
		else
		{
			return 'No logins';
		}
	}

	

	
	/**
	 * Update and return a new reset hash
	 */
	public function updateResetHash() {
		
		$this->reset_hash = substr(hash('sha256', uniqid('', true)), 0, 16);
		$this->save(true, array('reset_hash'));
		return $this->reset_hash;
	}

	/**
	 *	Send a password reset hash to a user
	 */
	public function sendPasswordResetEmail()
	{
		$mailgunApi = new MailgunApi(Yii::app()->params['insiderEmailDomain'], Yii::app()->params['mailgun']['key']);
		$message = $mailgunApi->newMessage();
		
		$message->setFrom(Yii::app()->params['fromEmail'], Yii::app()->name);
		$message->addTo($this->email, $this->fullName);
		$message->setSubject('Your password reset request');
		
		$renderedView = Yii::app()->controller->renderPartial('//mail/resetPassword', array(
			'name' => $this->first_name,
			'resetLink' => 'http://' . Yii::app()->request->serverName . '/reset-password?hash=' . $this->updateResetHash(),
			
		), true);
		
		$message->setHtml($renderedView);
		
		$message->addTag('Password reset');
		$message->send();
	}


	/**
	 *	Send a invitation to a user, get them to 'reset' their password to generate a password
	 */
	public function sendInvitation() {
		
		$mailgunApi = new MailgunApi(Yii::app()->params['insiderEmailDomain'], Yii::app()->params['mailgun']['key']);
		$message = $mailgunApi->newMessage();
		
		$message->setFrom(Yii::app()->params['fromEmail'], Yii::app()->name);
		$message->addTo($this->email, $this->fullName);
		$message->setSubject('Welcome to ' . Yii::app()->name);
		
		$renderedView = Yii::app()->controller->renderPartial('//mail/welcome', array(
			'name' => $this->first_name,
			'resetLink' => 'http://' . Yii::app()->request->serverName . '/reset-password?hash=' . $this->updateResetHash(),
			
		), true);
		
		$message->setHtml($renderedView);
		
		$message->addTag('User Invite');
		$message->send();
	}

	public function getAdminType() {
		switch ($this->role) {
			case $this::ROLE_SUPERADMIN:
				return 'Super Admin';
				break;
			case $this::ROLE_MANAGER:
				return 'Manager';
				break;
			case $this::ROLE_ORGANISATION:
				return 'Organisation';
				break;
		}		
	}

	public function getOrganisationName() {
		
		if ($this->organisation_id == 0) {
			return '<span class="text-muted">N/A</span>';
		}
		else
		{
			$Organisation = Organisation::model()->findByPk($this->organisation_id);
			return $Organisation->title . ($this->mothballed ? ' (archived)' : '');
		}

	}


	public function roleOptions() {
		
		if (Yii::app()->user->role == $this::ROLE_SUPERADMIN)
		{
			return array(
				$this::ROLE_SUPERADMIN => 'Super Admin',
				$this::ROLE_MANAGER => 'Manager',
				$this::ROLE_ORGANISATION => 'Organisation'
			);		

		}
		else if (Yii::app()->user->role == $this::ROLE_MANAGER)
		{
			return array(
				$this::ROLE_MANAGER => 'Manager',
				$this::ROLE_ORGANISATION => 'Organisation'
			);		

		}
		else if (Yii::app()->user->role == $this::ROLE_ORGANISATION)
		{
			return array(
				$this::ROLE_ORGANISATION => 'Organisation'
			);		

		}

		
	}

	public function organisationOptions() {
		
		//$a[0]='N/A';	

		$organisations = Organisation::model()->findAll(array(
			'condition'=>'active = :active',
			'params' => array(':active' => 1),
		));

		foreach ($organisations AS $organisation) {

			$a[$organisation->id]=$organisation->title;

		}

		return $a;




	}
	
	public function getAvatar()
	{
		if(!is_null($this->teamImage)){

			// new way of linking person to image in site admin team image page
			return $this->teamImage->teamSrc;
		}
		elseif($this->image_id) {

			// previous way of linking a person to an image in update person.
			return '/images/' . $this->image->thumb;
		
		} else {

			return '/css/assets/missing-man.png';
		}
	}
}