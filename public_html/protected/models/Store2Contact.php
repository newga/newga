<?php

class Store2Contact extends CActiveRecord
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
		return 'store2contact';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			//array('', 'required'),
			array('store_id,contact_warehouse_id,origin_id', 'numerical', 'integerOnly' => true),
			//array('origin_unique_id','required'),
			array('most_recent_invite_date', 'safe'),
			array('origin_unique_id', 'length', 'max' => 100),
			array('id', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
			'accession' => array(self::HAS_ONE, 'Accession', '', 'on' => 'warehouse_id = contact_warehouse_id'),
			'store' => array(self::BELONGS_TO, 'Store', 'store_id'),
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