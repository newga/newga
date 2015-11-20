<?php



class EmailTemplate extends CActiveRecord
{

	public $file;
	public $noticeArray = [];
	public $email_test_recipient;

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
		return 'email_template';
	}

	public function rules()
	{
        return array(
			array('file', 'file', 'types'=>'zip'),
			array('campaign_group_id', 'required')
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
		//$this->uuid = uniqid(); //this is being used to name csv and associate contact record with source file.

		return parent::beforeSave();

	}

	public function addNotice($notice) {


		$this->noticeArray[]=$notice;

	}
	
	public function parsedHtml($configParams)
	{
		$html = $this->html;
		
		$html = str_ireplace("src='", "src='".($configParams['https'] ? 'https':'http')."://" . $configParams['host'] . "/templates/".$this->folder."/", $html);
		$html = str_ireplace('src="', 'src="'.($configParams['https'] ? 'https':'http').'://' . $configParams['host'] . '/templates/'.$this->folder.'/', $html);
		
		$html = str_ireplace('url("', 'url("'.($configParams['https'] ? 'https':'http').'://' . $configParams['host'] . '/templates/'.$this->folder.'/', $html);
		
		$html = str_ireplace("url('", "url('".($configParams['https'] ? 'https':'http').'://' . $configParams['host'] . '/templates/'.$this->folder.'/', $html);

		return $html;
	}

	public function getExampleEmail() {

		//$html = utf8_encode($this->html);
		$html = $this->html;
		//replace image urls
		$html = str_ireplace("src='", "src='http://" . $_SERVER['SERVER_NAME'] . "/templates/".$this->folder."/", $html);
		$html = str_ireplace('src="', 'src="http://' . $_SERVER['SERVER_NAME'] . '/templates/'.$this->folder.'/', $html);
		$html = str_ireplace('%recipient.first_name%', Yii::app()->user->first_name, $html);

		$html = str_ireplace('%recipient.last_name%', Yii::app()->user->last_name, $html);
		$html = str_ireplace('%recipient.email%', Yii::app()->user->email, $html);
		$html = str_ireplace('%recipient.insider_unsubscribe%', 'http://example.com/unsubscribe', $html);


		return $html;
	}



	// return a url for replacing a [[outcome-1234]] style tag with.
	public function returnOutcomeTagsToUrls($configParams, $Campaign, $Contact2Outcomes) {

		$parsedTags = [];
		preg_match_all("@%recipient.outcome_([1-9][0-9]*)%@", $this->html, $matches);

		$urlBase = ($configParams['https'] ? "https" : "http") . "://" . $configParams['host'] . '/l/' . $Campaign->id . '-' . $Campaign->hash . '-';


		// get contact outcomes for this contact
		foreach($matches[0] as $key => $match) // $key is 0 indexed
		{
			// replace with complete url. /l/[campaign-id]-[campaign-hash]-[contact2outcome-id]-[contact2outcome-hash]
			$parsedTags['outcome_' . $matches[1][$key]] = $urlBase . $Contact2Outcomes[$matches[1][$key]]->id . '-' . $Contact2Outcomes[$matches[1][$key]]->hash;
		}

		return $parsedTags;
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