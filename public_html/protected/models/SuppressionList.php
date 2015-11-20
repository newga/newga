<?php


class SuppressionList extends CActiveRecord
{
	
	// Types to indicate why the row is in the supression list
	const 
		TYPE_UNSUBSCRIBE = 1,
		TYPE_BOUNCE = 2;
	
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
		return 'suppression_list';
	}
	
	public function types()
	{
		return array(
			SuppressionList::TYPE_UNSUBSCRIBE => 'Unsubscribe',
			SuppressionList::TYPE_BOUNCE => 'Bounce',
		);
	}
	
	public function getSuppressionType()
	{
		$types = $this->types();
		
		return $types[$this->type];
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('date', 'required'),
			array('warehouse_id, store2contact_id, store_id', 'humanRequired'),
			array('date', 'date', 'format'=>'yyyy-M-d H:m:s'),
			array('campaign_id, type', 'numerical', 'integerOnly' => true),
		);
	}

	// either warehouse_id or store_id is required
	public function humanRequired($attribute_name, $params){
		if(!(int)$this->warehouse_id && !(int)$this->store2contact_id && !(int)$this->store_id){

				$this->addError($attribute_name, 'A connection to an invite contact or campaign contact is required.');

      	return false;
		}

		return true;
	}

	public function beforeValidate(){

		// need now as the unsubscribed time?
		if($this->isNewRecord && is_null($this->date)){
			$this->date = date("Y-m-d H:i:s");
		}

		return parent::beforeValidate();
	}


	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
			'store' => array(self::BELONGS_TO, 'Store', 'store_id'),
			'store2contact' => array(self::BELONGS_TO, 'Store2Contact', 'store2contact_id'),
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

	public function createUnsubscribeUrl($warehouse_id)
	{
		$contact = CleanWarehouse::model()->findByAttributes(array('warehouse_id'=>$warehouse_id));
		if(count($contact) > 0)
		{
			//contact is is clean warehouse
			return "/unsubscribe/" .$contact->hash;
		} 
		else
		{
			return false;
		}
	}
	
	public function search() {

		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria = new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('warehouse_id',$this->warehouse_id);
		$criteria->compare('store_id',$this->store_id);
		$criteria->compare('store2contact_id',$this->store2contact_id);
		$criteria->compare('campaign_id',$this->campaign_id);
		$criteria->compare('type', $this->type);
	
		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
			'Pagination' => array (
          'PageSize' => 20
       ),
		));
	}
}