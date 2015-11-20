<?php

class Venue extends CActiveRecord
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
		return 'venue';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('title,organisation_id', 'required'),
			array('organisation_id', 'numerical', 'integerOnly' => true),
			array('active', 'in', 'range' => array('0', '1')),
			array('title', 'length', 'max'=>100),
			array('id, title, active', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
		
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'organisation_id' => 'Organisation'
		);
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

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search() {

		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria = new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('title',$this->title,true);
		$criteria->compare('active',$this->active,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
			'Pagination' => array (
                'PageSize' => 20
             ),
			'sort'=>array(
    			'defaultOrder'=>'title ASC'
			),
		));
	
	}

	
}