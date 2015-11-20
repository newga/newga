<?php


class CampaignContact extends CActiveRecord
{

	// Status
	const
		STATUS_UNSENT = 0,
		STATUS_SENT = 1,
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
		return 'campaign_contact';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('campaign_id, group_id, warehouse_id', 'required', 'on' => 'insert'),
			array('date, status, processing', 'required', 'on' => 'update'),
			array('campaign_id, group_id, warehouse_id,status', 'numerical', 'integerOnly' => true),
			array('hash', 'length', 'max' => 6),
			array('date,opened, bounced', 'safe')
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
			'contact' => array(self::BELONGS_TO, 'CleanWarehouse', 'warehouse_id'),
			'campaign' => array(self::BELONGS_TO, 'Campaign', 'campaign_id'),
			'group' => array(self::BELONGS_TO, 'CampaignGroup', 'group_id'),
			
			'contact2outcomes' => array(self::HAS_MANY, 'CampaignContact2Outcome', 'campaign_contact_id'),
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