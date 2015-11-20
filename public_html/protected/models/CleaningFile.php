<?php



class CleaningFile extends CActiveRecord
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
		return 'csv_cleaning_file';
	}

    public function rules()
    {
        return array(
        	// array('uuid', 'length', 'max'=>'13'),
        	array('status', 'in', 'range'=>array(0,1,2)),
        	array('file,import_date','safe')


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
		if($this->isNewRecord) {
			$this->created = date('Y-m-d H:i:s');
			$this->uuid = uniqid(); //this is being used to name csv and associate contact record with source file. 
			
		}
		return parent::beforeSave();
	}

	public function search() {

		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria = new CDbCriteria;


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