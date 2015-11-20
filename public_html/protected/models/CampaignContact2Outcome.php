<?php

class CampaignContact2Outcome extends CActiveRecord
{
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
		return 'campaign_contact2outcome';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('campaign_contact_id, campaign_outcome_id', 'required'),
			array('hash', 'length', 'max' => 8),
			array('campaign_contact_id, campaign_outcome_id', 'numerical', 'integerOnly' => true),
			array('outcome', 'type', 'type' => 'date', 'message' => '{attribute}: is not a datetimestamp', 'dateFormat' => 'yyyy-MM-dd hh:mm:ss'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
			'campaign_contact' => array(self::BELONGS_TO, 'CampaignContact', 'campaign_contact_id'),
			'campaign_outcome' => array(self::BELONGS_TO, 'CampaignOutcome', 'campaign_outcome_id'),
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
}