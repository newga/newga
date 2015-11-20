<?php

/**
 * This is the model class for table "login".
 *
 * The followings are the available columns in table 'login':
 * @property string $id
 * @property string $ip
 * @property integer $success
 * @property string $id
 * @property string $date
 *
 * The followings are the available model relations:
 * @property User $user
 */
class Login extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Login the static model class
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
		return 'login';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('user_id, success', 'numerical', 'integerOnly'=>true),
			array('ip', 'length', 'max'=>20),
			array('date', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, ip, success, user_id, date', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'user' => array(self::BELONGS_TO, 'User', 'id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'Login',
			'ip' => 'Ip',
			'success' => 'Success',
			'user_id' => 'User',
			'date' => 'Date',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('ip',$this->ip);
		$criteria->compare('success',$this->success);
		$criteria->compare('user_id',$this->user_id);
		$criteria->compare('date',$this->date);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
	
	public function IPBanned()
	{
		if(Yii::app()->params['ipLoginFiltering'])
		{
			// Check IP isn't banned
			$ip = IpBan::model()->findByAttributes(array('ip' => $_SERVER['REMOTE_ADDR']));
		
			if(sizeof($ip))
			{
				// this IP is banned
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}
}