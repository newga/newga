<?php

class CampaignFile extends CActiveRecord
{


	// used to store the actual file
	public $newFile;


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
		return 'campaign_file';
	}


	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(

			array('name', 'required'),
			array('name', 'length', 'max' => 50),
			array('secret', 'length', 'max' => 6),
			array('extension', 'length', 'max' => 10),
			array('newFile', 'file', 'types' => 'pdf'),
			array('uploaded_by', 'numerical', 'integerOnly' => true),

			array('campaign_id, name, secret', 'safe', 'on'=>'search'),
		);
	}



	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
			'campaign' => array(self::BELONGS_TO, 'Campaign', 'campaign_id'),
		);
	}



	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'campaign_id' => 'Campaign',
			'newFile' => 'New pdf file',
		);
	}



	public function beforeSave(){

		if($this->isNewRecord){
			$this->secret = substr( md5(rand()), 0, 6);
			$this->uploaded_by = Yii::app()->user->id;
			$this->uploaded_at = new CDbExpression("NOW()");
			$this->extension = $this->newFile->extensionName;
		}

		return parent::beforeSave();

	}



}

?>