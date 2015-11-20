<?php

class View_Sitename extends CActiveRecord
{
    public $data;
   
 
    /**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'sitename';
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

	public function getOriginUniqueKey()
	{
		return "ExternalID";
	}

    public function primaryKey()
	{
	    return 'id';                
	}

	public function getContactEmail($data) {

		switch ($data['WishToReceiveEmail']) {
			case 'TRUE':
				return 1;
			break;

			case 'FALSE':
				return 0;
			break;

			default:
				return 2;
		}
	}

	public function getContactSMS($data) {
		
		switch ($data['WishToReceiveSMS']) {
			case 'TRUE':
				return 1;
			break;

			case 'FALSE':
				return 0;
			break;

			default:
				return 2;
		}
	}

	public function getContactPost($data) {

		switch ($data['WishToReceiveMail']) {
			case 'TRUE':
				return 1;
			break;

			case 'FALSE':
				return 0;
			break;

			default:
				return 2;
		}
	}


	public function beforeSave() 
	{
	    
	    if ($this->isNewRecord)
	    {
	        $this->date_imported = new CDbExpression('NOW()');
	        $this->origin_organisation_id = 9;
	    }
	 
	    return parent::beforeSave();
	}
}

?>