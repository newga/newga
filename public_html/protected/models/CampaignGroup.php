<?php

class CampaignGroup extends CActiveRecord
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
		return 'campaign_group';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('campaign_id, name, fraction', 'required'),
			array('name', 'length', 'max' => 50),
			array('subject', 'length', 'max' => 255),
			array('description', 'length', 'max' => 10000),
			array('fraction', 'numerical', 'min' => 0.01, 'max' => 99.99),

			array('name', 'safe', 'on'=>'search'),
		);
	}


	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
			'campaign' => array(self::BELONGS_TO, 'Campaign', 'campaign_id'),
			'email_template' => array(self::HAS_ONE, 'EmailTemplate','campaign_group_id')
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'campaign_id' => 'Campaign',
			'subject' => 'Email Subject',
			'fraction' => 'Percentage Split'
		);
	}




	public function beforeSave(){

		if($this->isNewRecord){

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
		$criteria->compare('name', $this->name, true);

		return new CActiveDataProvider($this, array(
			'criteria' => $criteria,
			'pagination' => array(
				'pageSize' => 20,
			),
			'sort' => array(
				'defaultOrder'=>'name ASC',
			),
		));

	}


}