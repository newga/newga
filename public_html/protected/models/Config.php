<?php


class Config extends CActiveRecord
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
		return 'config';
	}

    public function rules()
    {
        return array(
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
		if($this->isNewRecord){
			$this->created = date('Y-m-d H:i:s');
		}

		return parent::beforeSave();

	}

	public function search() {
	
	}

}

?>