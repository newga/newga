<?php

class Store extends CActiveRecord
{
	public $dob_day;
	public $dob_month;
	public $dob_year;
	
	protected $key1;
	protected $key2;
	protected $key3;
	protected $key4;
	protected $key5;
	protected $iv;
	protected $cipher;
	protected $mode;
	
	protected $encrypt = true;
	
	/*
	 * contact_email column values:
	 * 0 = do not contact
	 * 1 = contact accepted
	 * 2 = unknown if we can contact - we assume yes we can
	 */
	
	
	public function init()
	{
		$this->setClassVars();
	}
	
	public function setClassVars()
	{
		$this->key1 = '';
		$this->key2 = '';
		$this->key3 = '';
		$this->key4 = '';
		$this->key5 = '';
		$this->cipher = MCRYPT_RIJNDAEL_256;
		$this->mode = MCRYPT_MODE_ECB;
		$this->iv = mcrypt_create_iv(mcrypt_get_iv_size($this->cipher, $this->mode), MCRYPT_RAND);
	}
	
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
		return 'store';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			//array('origin_organisation_id', 'required'),
			array('first_name,last_name,email,address_postcode', 'required', 'on' => 'accessionPersonalDetails'),
			array('first_name,last_name,email,address_postcode', 'required', 'on' => 'updateDetails'),
			
			array('address_postcode,salutation', 'length', 'max' => 20),
			array('first_name,last_name,address_line_1, address_line_2, address_line_3, address_line_4, address_town, origin_unique_id', 'length', 'max' => 100),
			array('email', 'length', 'max' => 255),
			array('address_county', 'length', 'max' => 50),
			array('mobile,phone, origin_unique_id', 'length', 'max' => 100),
			array('csv_file_uuid', 'length', 'max' => 13),
			
			array('dob', 'isDate'),
			
			array('date_imported,date_expired', 'safe'),
			array('origin_organisation_id,contact_email,contact_sms,contact_post,deceased,ccr_duplicate_id,ccr_ind_dupe1', 'numerical', 'integerOnly' => true),
			array('id', 'safe', 'on'=>'search'),
		);
	}
	
	public function isDate($attribute, $params)
	{
		if(strlen($this->$attribute))
		{
			if(!strtotime($this->$attribute))
			{
				$this->addError($attribute, $this->getAttributeLabel($attribute) .' was not recognised as a date');
			}
		}
	}
	
	public function dobIsSet($attribute, $params)
	{
		if(!strlen($this->$attribute))
		{
			$this->addError('dob', 'You must choose your ' . $this->getAttributeLabel($attribute));
		}
	}
	
	public function beforeSave()
	{
		if(is_null($this->key1))
		{
			$this->setClassVars();
		}
		
		if($this->encrypt)
		{
			if(strlen($this->email))
			{
				$this->email = strtolower($this->email);
				$this->email = trim(base64_encode(mcrypt_encrypt($this->cipher, $this->key1, $this->email, $this->mode, $this->iv)));
			}
			
			if(strlen($this->address_line_1))
			{
				$this->address_line_1 = trim(base64_encode(mcrypt_encrypt($this->cipher, $this->key2, $this->address_line_1, $this->mode, $this->iv)));
			}
			
			if(strlen($this->last_name))
			{
				$this->last_name = trim(base64_encode(mcrypt_encrypt($this->cipher, $this->key3, $this->last_name, $this->mode, $this->iv)));
			}
			
			if(strlen($this->mobile))
			{
				$this->mobile = trim(base64_encode(mcrypt_encrypt($this->cipher, $this->key4, $this->mobile, $this->mode, $this->iv)));
			}
			
			if(strlen($this->phone))
			{
				$this->phone = trim(base64_encode(mcrypt_encrypt($this->cipher, $this->key5, $this->phone, $this->mode, $this->iv)));
			}
		}
		
		return parent::beforeSave();
	}
	
	public function afterFind()
	{
		if(is_null($this->key1))
		{
			$this->setClassVars();
		}
		
		if(strlen($this->email))
		{
			$this->email =  trim(mcrypt_decrypt($this->cipher, $this->key1, base64_decode($this->email), $this->mode, $this->iv));
		}
		
		if(strlen($this->address_line_1))
		{
			$this->address_line_1 =  trim(mcrypt_decrypt($this->cipher, $this->key2, base64_decode($this->address_line_1), $this->mode, $this->iv));
		}
		
		if(strlen($this->last_name))
		{
			$this->last_name =  trim(mcrypt_decrypt($this->cipher, $this->key3, base64_decode($this->last_name), $this->mode, $this->iv));
		}
		
		if(strlen($this->mobile))
		{
			$this->mobile =  trim(mcrypt_decrypt($this->cipher, $this->key4, base64_decode($this->mobile), $this->mode, $this->iv));
		}
		
		if(strlen($this->phone))
		{
			$this->phone =  trim(mcrypt_decrypt($this->cipher, $this->key5, base64_decode($this->phone), $this->mode, $this->iv));
		}
		
		return parent::afterFind();
	}
	
	public function encryptEmail($email)
	{
		if(is_null($this->key1))
		{
			$this->setClassVars();
		}
		
		$email = strtolower($email);
		
		if(strlen($email))
		{
			return trim(base64_encode(mcrypt_encrypt($this->cipher, $this->key1, $email, $this->mode, $this->iv)));
		}
		else
		{
			return null;
		}
	}
	
	public function decryptEmail($email)
	{
		if(is_null($this->key1))
		{
			$this->setClassVars();
		}
		
		if(strlen($email))
		{
			return trim(mcrypt_decrypt($this->cipher, $this->key1, base64_decode($email), $this->mode, $this->iv));
		}
		else
		{
			return null;
		}
	}
	
	public function decryptLastName($lastName)
	{
		if(is_null($this->key1))
		{
			$this->setClassVars();
		}
		
		if(strlen($lastName))
		{
			return trim(mcrypt_decrypt($this->cipher, $this->key3, base64_decode($lastName), $this->mode, $this->iv));
		}
		else
		{
			return null;
		}
	}


	public function decryptPhone($phone)
	{
		if(is_null($this->key5))
		{
			$this->setClassVars();
		}
		
		if(strlen($phone))
		{
			return trim(mcrypt_decrypt($this->cipher, $this->key5, base64_decode($phone), $this->mode, $this->iv));
		}
		else
		{
			return null;
		}
	}

	public function decryptMobile($mobile)
	{
		if(is_null($this->key4))
		{
			$this->setClassVars();
		}
		
		if(strlen($mobile))
		{
			return trim(mcrypt_decrypt($this->cipher, $this->key4, base64_decode($mobile), $this->mode, $this->iv));
		}
		else
		{
			return null;
		}
	}

	public function decryptAddress1($line)
	{
		if(is_null($this->key2))
		{
			$this->setClassVars();
		}
		
		if(strlen($line))
		{
			return trim(mcrypt_decrypt($this->cipher, $this->key2, base64_decode($line), $this->mode, $this->iv));
		}
		else
		{
			return null;
		}
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
			'store2contact' => array(self::HAS_ONE, 'Store2Contact', 'store_id'),
			'organisation' => array(self::BELONGS_TO, 'Organisation', 'origin_organisation_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'salutation' => 'Title',
			'address_line_1' => 'Address',
			'address_town' => 'Town / City',
			'address_postcode' => 'Postcode',
			'address_county' => 'County',
			'dob' => 'Date of Birth',
			'dob_day' => 'Day of Birth',
			'dob_month' => 'Month of Birth',
			'dob_year' => 'Year of Birth',
		);
	}
	
	public function dobDays()
	{
		return array(
			'01' => '01',
			'02' => '02',
			'03' => '03',
			'04' => '04',
			'05' => '05',
			'06' => '06',
			'07' => '07',
			'08' => '08',
			'09' => '09',
			'10' => '10',
			'11' => '11',
			'12' => '12',
			'13' => '13',
			'14' => '14',
			'15' => '15',
			'16' => '16',
			'17' => '17',
			'18' => '18',
			'19' => '19',
			'20' => '20',
			'21' => '21',
			'22' => '22',
			'23' => '23',
			'24' => '24',
			'25' => '25',
			'26' => '26',
			'27' => '27',
			'28' => '28',
			'29' => '29',
			'30' => '30',
			'31' => '31',
		);
	}
	
	public function dobMonths()
	{
		return array(
			'01' => 'January',
			'02' => 'February',
			'03' => 'March',
			'04' => 'April',
			'05' => 'May',
			'06' => 'June',
			'07' => 'July',
			'08' => 'August',
			'09' => 'September',
			'10' => 'October',
			'11' => 'November',
			'12' => 'December',
		);
	}
	
	public function dobYears()
	{
		$years = range(date('Y'),1900);
		$yearsArray = array();
		
		foreach($years as $year)
		{
			$yearsArray[$year] = $year;
		}
		
		return $yearsArray;
	}


	public function getFullName() {

		return $this->first_name . ' ' . $this->last_name;
	}


	/**
	 * Update and return a new reset hash
	 */
	public function updateResetHash() {
		
		$Accession = $this->store2contact->accession;
		$Accession->reset_hash = substr(hash('sha256', uniqid('', true)), 0, 16);
		$Accession->save(true, array('reset_hash'));
		return $Accession->reset_hash;
	}
	
	public function sendPasswordResetEmail()
	{
		$mailgunApi = new MailgunApi(Yii::app()->params['insiderEmailDomain'], Yii::app()->params['mailgun']['key']);
		$message = $mailgunApi->newMessage();
		
		$message->setFrom(Yii::app()->params['fromEmail'], Yii::app()->name);
		$message->addTo($this->email, $this->fullName);
		$message->setSubject('Your password reset request');
		
		$renderedView = Yii::app()->controller->renderPartial('//mail/resetPassword', array(
			'name' => $this->first_name,
			'resetLink' => 'http://' . Yii::app()->request->serverName . '/reset-password?hash=' . $this->updateResetHash(),
			
		), true);
		
		$message->setHtml($renderedView);
		
		$message->addTag('Password reset');
		$message->send();

	}
	
	public function getCounties()
	{
		return array(
'North East Counties' => array(
	"County Durham"=>"County Durham",
	"Northumberland"=>"Northumberland",
	"Tyne and Wear"=>"Tyne and Wear",
),

'All Counties' => array(
"Aberdeenshire"=>"Aberdeenshire",
"Angus/Forfarshire"=>"Angus/Forfarshire",
"Argyllshire"=>"Argyllshire",
"Ayrshire"=>"Ayrshire",
"Banffshire"=>"Banffshire",
"Bedfordshire"=>"Bedfordshire",
"Berkshire"=>"Berkshire",
"Berwickshire"=>"Berwickshire",
"Blaenau Gwent"=>"Blaenau Gwent",
"Bridgend"=>"Bridgend",
"Buckinghamshire"=>"Buckinghamshire",
"Buteshire"=>"Buteshire",
"Caerphilly"=>"Caerphilly",
"Caithness"=>"Caithness",
"Cambridgeshire"=>"Cambridgeshire",
"Cardiff"=>"Cardiff",
"Carmarthenshire"=>"Carmarthenshire",
"Ceredigion"=>"Ceredigion",
"Cheshire"=>"Cheshire",
"Clackmannanshire"=>"Clackmannanshire",
"Conwy"=>"Conwy",
"Cornwall"=>"Cornwall",
"Cromartyshire"=>"Cromartyshire",
"Cumberland"=>"Cumberland",
"Denbighshire"=>"Denbighshire",
"Derbyshire"=>"Derbyshire",
"Devon"=>"Devon","Dorset"=>"Dorset",
"Dumfriesshire"=>"Dumfriesshire",
"Dunbartonshire/Dumbartonshire"=>"Dunbartonshire/Dumbartonshire",
"Durham"=>"Durham",
"East Lothian/Haddingtonshire"=>"East Lothian/Haddingtonshire",
"Essex"=>"Essex",
"Fife"=>"Fife",
"Flintshire"=>"Flintshire",
"Gloucestershire"=>"Gloucestershire","Greater Manchester"=>"Greater Manchester","Greater London"=>"Greater London","Gwynedd"=>"Gwynedd","Hampshire"=>"Hampshire",
"Herefordshire"=>"Herefordshire","Hertfordshire"=>"Hertfordshire","Huntingdonshire"=>"Huntingdonshire",
"Inverness-shire"=>"Inverness-shire","Isle of Anglesey"=>"Isle of Anglesey","Kent"=>"Kent",
"Kincardineshire"=>"Kincardineshire","Kinross-shire"=>"Kinross-shire","Kirkcudbrightshire"=>"Kirkcudbrightshire",
"Lanarkshire"=>"Lanarkshire","Lancashire"=>"Lancashire","Leicestershire"=>"Leicestershire",
"Lincolnshire"=>"Lincolnshire","Merthyr Tydfil"=>"Merthyr Tydfil","Middlesex"=>"Middlesex",
"Midlothian/Edinburghshire"=>"Midlothian/Edinburghshire","Monmouthshire"=>"Monmouthshire",
"Morayshire"=>"Morayshire","Nairnshire"=>"Nairnshire","Neath Port Talbot"=>"Neath Port Talbot",
"Newport"=>"Newport","Norfolk"=>"Norfolk","Northamptonshire"=>"Northamptonshire",
"Northumberland"=>"Northumberland","Nottinghamshire"=>"Nottinghamshire","Orkney"=>"Orkney",
"Oxfordshire"=>"Oxfordshire","Peeblesshire"=>"Peeblesshire","Pembrokeshire"=>"Pembrokeshire",
"Perthshire"=>"Perthshire","Powys"=>"Powys","Renfrewshire"=>"Renfrewshire",
"Rhondda Cynon Taff"=>"Rhondda Cynon Taff","Ross-shire"=>"Ross-shire","Roxburghshire"=>"Roxburghshire",
"Rutland"=>"Rutland","Selkirkshire"=>"Selkirkshire","Shetland"=>"Shetland","Shropshire"=>"Shropshire",
"Somerset"=>"Somerset","Staffordshire"=>"Staffordshire","Stirlingshire"=>"Stirlingshire","Stockton-on-Tees" => "Stockton-on-Tees","Suffolk"=>"Suffolk",
"Surrey"=>"Surrey","Sussex"=>"Sussex","Sutherland"=>"Sutherland","Swansea"=>"Swansea","Torfaen"=>"Torfaen","Tyne and Wear"=>"Tyne and Wear",
"Vale of Glamorgan"=>"Vale of Glamorgan","Warwickshire"=>"Warwickshire",
"West Lothian/Linlithgowshire"=>"West Lothian/Linlithgowshire","Westmorland"=>"Westmorland",
"Wigtownshire"=>"Wigtownshire","Wiltshire"=>"Wiltshire",
"Worcestershire"=>"Worcestershire","Wrexham"=>"Wrexham","Yorkshire"=>"Yorkshire"));
	}
	
	public function getSalutations()
	{
		return array(
			'Mr' => 'Mr',
			'Mrs' => 'Mrs',
			'Ms' => 'Ms',
			'Miss' => 'Miss',
			'Dr' => 'Dr',
			'Prof' => 'Prof',
		);
	}
}