<?php

class CleanWarehouse extends CActiveRecord
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
		return 'clean_warehouse';
	}

	public function primaryKey()
	{
		return 'contact_warehouse_id';
	}


	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			//array('warehouse_id', 'required'),
			
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
			'campaignContacts' => array(self::HAS_MANY, 'CampaignContact', 'warehouse_id'),
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