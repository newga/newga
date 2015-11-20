<?php

class QueryOption extends CActiveRecord
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
		return 'query_option';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(

		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array ('questions' => array(self::MANY_MANY, 'QueryQuestion', 'query_question2query_option(question_id, option_id)')
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

	public function getTypeName()
	{
		switch ($this->type) {
			case QueryQuestion::TYPE_CONTACT:
				return "Contact details";
				break;
			case QueryQuestion::TYPE_ACCESSION:
				return "Accession profile";
				break;
			case QueryQuestion::TYPE_CAMPAIGN:
				return "Campaigns";
				break;
			default:
				return "Other";
				break;
		}
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search() {

	}

	
}