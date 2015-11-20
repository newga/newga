<?php

class QueryQuestion extends CActiveRecord
{
	
	//Each contact IS/IS NOT
	const LANG_TYPE_EXISTENTIAL = 1;

	//Each contact HAS/DOES NOT HAVE
	const LANG_TYPE_POSSESIVE = 2;

	//Each contact DID ANSWER/DID NOT ANSWER
	const LANG_TYPE_RESPONSE = 3;

	//Question category
	const TYPE_CONTACT = 1;
	const TYPE_ACCESSION = 2;
	const TYPE_CAMPAIGN = 3;
	const TYPE_INVITE = 4;
	
	// NEW Options code - JBG
	const 
		//OPTION_NUMBER = 1,
		OPTION_ORGANISATION = 2,
		OPTION_VENUE = 3,
		OPTION_INVITE = 4,
		OPTION_ARTFORM = 5,
		OPTION_CS = 6,
		OPTION_LOE = 7,
		OPTION_CAMPAIGN = 8,
		OPTION_OUTCOME = 9;
	
	//special campaign cases
	//2,3,4  about age
	//15 part of campaign

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
		return 'query_question';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			// array('title', 'required'),
			// array('active', 'numerical', 'integerOnly'=>true),
			// array('title', 'length', 'max'=>100),
			// array('id, title, active', 'safe', 'on'=>'search'),
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
			case QueryQuestion::TYPE_INVITE:
				return "Invites";
				break;
			default:
				return "Other";
				break;
		}
	}

	public function getPositiveLang()
	{
		//change language 
		switch($this->lang_type) {
			case 1:
			    return 'is';
			    break;
			case 2:
			    return 'has';
			    break;
			case 3:
			    return 'did answer';
			    break;
			case 4:
				return 'was';
				break;
			default:
			    return 'is';
		}
	}

	public function getNegativeLang()
	{
		//change language 
		switch($this->lang_type) {
			case 1:
			    return 'is not';
			    break;
			case 2:
			    return 'does not have';
			    break;
			case 3:
			    return 'did not answer';
			    break;
			case 4:
				return 'was not';
				break;
			default:
			    return 'is not';
		}
	}
	
	public function levelsOfEngagement()
	{
		// map of levels of engagement names
		return array(
			1 => 'Name',
		);
	}
	
	public function levelOfEngagement($id)
	{
		$loes = $this->levelsOfEngagement();
		
		return $loes[$id];
	}

	

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search() {

	
	}

	
}