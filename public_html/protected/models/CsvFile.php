<?php


// PLEASE NOTE: csv_file_uuid FK was removed to allow people to signup without being imported




class CsvFile extends CActiveRecord
{
    public $data;
   

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
		return 'csv_file';
	}

    public function rules()
    {
        return array(
            array('data', 'file', 'types'=>'csv'),
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


	public function beforeSave() 
	{
		
		$this->created = date('Y-m-d H:i:s');
		$this->uuid = uniqid(); //this is being used to name csv and associate contact record with source file. 

		return parent::beforeSave();

	}

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

?>