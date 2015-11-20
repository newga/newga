<?php

class Accession extends CActiveRecord
{
	public $salutation;
	public $first_name;
	public $last_name;
	public $email;
	public $mobile;
	public $other_phone;
	public $dob;

	public $address1;
	public $address2;
	public $address3;
	public $address4;
	public $town;
	public $postcode;
	public $county;

	public $age;

	public $password;
	public $password_repeat;


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
		return 'accession';
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
			array('terms_agreed', 'required', 'message' => 'To join, please tick the box to say that you agree with our terms and conditions.'),
			
			array('store2contact_id, original_store2contact_id, children', 'numerical'),
			array('password', 'length', 'min' => 8),
			array('children', 'required', 'on' => 'stepThree'),
			array('terms_agreed, password_repeat', 'safe'),
			array('accession_hash', 'length', 'max' => 40, 'min' => 40),
			array('reset_hash', 'length', 'max' => 64),
			array('culture_segment, level_of_engagement', 'length', 'max' => 30),
			array('warehouse_id,children,step,complete,invite_id', 'numerical'),
			array('child_ages,cs_answers', 'length', 'max' => 255),
			array('id', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
			'contact2venue' => array(self::HAS_ONE, 'Contact2Venue', 'accession_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'age' => 'Your age',
		);
	}


}