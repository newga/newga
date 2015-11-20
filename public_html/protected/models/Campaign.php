<?php

class Campaign extends CActiveRecord
{

	const
		STATUS_NOT_RUN = 0,
		STATUS_HAS_BEEN_RUN = 1,
		STATUS_QUEUED = 2,
		STATUS_ERROR_SEND = 3;

	const
		TYPE_EMAIL = 1;


	public $email_test_recipient;


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
		return 'campaign';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('query_id, name, description, hash', 'required'),
			array('type', 'required', 'on' => 'insiderCampaign'),

			array('name', 'length', 'max' => 100),
			array('description', 'length', 'max' => 10000),

			array('hash', 'length', 'is' => 6),

			array('creator_id, status, size, query_id, processing, type', 'numerical', 'integerOnly' => true),

			// Invite send validators
			array('email_test_recipient', 'email'),
			array('invite_email_subject', 'length', 'max' => 100),
			array('invite_email_body', 'hasInviteTemplateTag', 'on' => 'inviteEdit'),

			array('invite_email_subject,invite_email_body', 'required', 'on' => 'inviteEdit'),

			array('size', 'numerical', 'min' => 1),
			array('date_run', 'safe'),
			array('name,', 'safe', 'on'=>'search'),
		);
	}

	public function hasInviteTemplateTag($attribute,$params)
	{
		if(!preg_match('@\[\[invitelink\]\]@', $this->$attribute))
		{
			$this->addError($attribute, 'Email body must include the invite template tag: [[invitelink]]');
		}
	}


	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
			'query' => array(self::BELONGS_TO, 'Query', 'query_id'),
			'creator' => array(self::BELONGS_TO, 'User', 'creator_id'),
			'groups' => array(self::HAS_MANY, 'CampaignGroup', 'campaign_id'),
			'files' => array(self::HAS_MANY, 'CampaignFile', 'campaign_id'),
			'outcomes' => array(self::HAS_MANY, 'CampaignOutcome', 'campaign_id'),
			'contacts' => array(self::HAS_MANY, 'CampaignContact', 'campaign_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'query_id' => 'Query',
			'creator_id' => 'Created by',
			'invite_email_subject' => 'Email Subject',
			'invite_email_body' => 'Email Body',
			'type' => 'Campaign Type'
		);
	}



	public function beforeValidate(){

		if($this->isNewRecord){
			$this->hash = Yii::app()->functions->makeHash(6);
		}


		return parent::beforeValidate();
	}



	public function beforeSave(){

		if($this->isNewRecord){
			$this->created = new CDbExpression("NOW()");
			$this->creator_id = Yii::app()->user->id;
		}

		if(!$this->size) {
			// 0 or ''
			$this->size = null;
		}

		return parent::beforeSave();

	}




	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search($invite = 0) {

		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria = new CDbCriteria;
		$criteria->compare('t.name',$this->name,true);
		$criteria->compare('query.invite', (int)$invite);

		$criteria->with = array('query');

		if($invite)
		{
			$criteria->order = '`t`.`created` DESC';
		}

		return new CActiveDataProvider($this, array(
			'criteria' => $criteria,
			'pagination' => array(
				'pageSize' => 20,
			),
			'sort' => array(
				'defaultOrder'=>'t.created DESC',
			),
		));

	}



	/* return if the campaign has been run based on bitwise status column */
	public function getHasBeenRun(){

		return $this->status > 0;
		//return ($this->status & self::STATUS_HAS_BEEN_RUN);
	}

	public function getStatusHTML()
	{
		switch($this->query->invite)
		{
			case 1: // INVITE
				switch($this->status)
				{
					case Campaign::STATUS_NOT_RUN:
						return '<span class="alert alert-warning">Unsent</span>';
					break;

					case Campaign::STATUS_HAS_BEEN_RUN:
						return '<span class="alert alert-success">Sent</span>';
					break;

					case Campaign::STATUS_QUEUED:
						return '<span class="alert alert-info">Queued</span>';
					break;
				}
			break;

			case 0: // CAMPAIGN
				switch($this->status)
				{
					case Campaign::STATUS_NOT_RUN:
						return '<span class="alert alert-warning">Pending</span>';
					break;

					case Campaign::STATUS_HAS_BEEN_RUN:
						return '<span class="alert alert-success">Run</span>';
					break;

					case Campaign::STATUS_QUEUED:
						return '<span class="alert alert-info">Queued</span>';
					break;
				}
			break;
		}

	}

	public function getStatusText()
	{
		switch($this->status)
		{
			case Campaign::STATUS_NOT_RUN:
				return 'Unsent';
			break;

			case Campaign::STATUS_HAS_BEEN_RUN:
				return 'Sent';
			break;

			case Campaign::STATUS_QUEUED:
				return 'Queued';
			break;

			case Campaign::STATUS_ERROR_SEND:
				return 'Error for 1 or more invite';
			break;
		}
	}



	public function generateHash($length = 5)
	{
    	return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
	}

}