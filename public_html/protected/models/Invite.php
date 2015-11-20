<?php

class Invite extends CActiveRecord
{
	// Status
	const
		STATUS_UNSENT = 0,
		STATUS_SENT = 1,
		STATUS_LINK_FOLLOWED = 2,
		STATUS_ACCEPTED = 3,
		STATUS_MAILGUN_ERROR = 4;


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
		return 'invite';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('store2contact_id,contact_warehouse_id,store_id,organisation_id,hash,date,status', 'required', 'on' => 'insert'),

			array('store2contact_id,contact_warehouse_id,organisation_id,status', 'numerical', 'integerOnly' => true),

			array('hash', 'length', 'max' => 40),

			array('date', 'safe'),

			array('id', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
			'campaign' => array(self::BELONGS_TO, 'Campaign', 'campaign_id'),
			'store2contact' => array(self::BELONGS_TO, 'Store2Contact', 'store2contact_id'),
			'store' => array(self::BELONGS_TO, 'Store', 'store_id')
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
		);
	}


	/**
	 *	Return url to unsubscribe from this organisation's invites
	 */
	public function getUnsubscribeUrl(){
		
		
		
		$configs = Yii::app()->db->createCommand("SELECT * FROM `config` WHERE `key` IN ('host', 'https')")->queryAll();
		
		if(sizeof($configs) < 2){
			throw new CException("\n\n\n===\nDomain or HTTPS config not yet set. View any admin area page in a browser to remedy this.\n===\n\n");
		}

		foreach($configs as $config){
			$configParams[$config['key']] = $config['value'];
		}
		
		$url = ($configParams['https'] ? 'https://' : 'http://') . $configParams['host'] . '/unsubscribe/' . $this->campaign_id . '-' . $this->campaign->hash . '/' . $this->id . '-' . substr($this->hash, 0, 8);
		
		return $url;
	}

}