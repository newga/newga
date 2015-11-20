<?php

/**
 * UserIdentity represents the data needed to identity a user.
 * It contains the authentication method that checks if the provided
 * data can identity the user.
 */
class UserIdentity extends CUserIdentity
{
	/**
	 * Authenticates a user.
	 * @return boolean whether authentication succeeds.
	 */

	// stores $_id for getId() or ->user->id	 
	private $_id;
	
	public $userType;
	public $storeId;
	
	public function authenticate()
	{
		// Try to get user from User table - this will be an administrator
		$User = User::model()->findByAttributes(
			array(
				'email' => strtolower($this->username),
				'verified' => 1,
				'mothballed' => 0,
			)
		);
		
		if(!is_null($User))
		{
			// We have an admin user
			if(isset($User->password))
			{
				if($User->password === hash('sha256', $this->password . SHASALT))
				{
					$this->errorCode=self::ERROR_NONE;
					$this->_id = $User->id;
					$this->userType = 'admin';
					
				}
				else
				{
					$this->errorCode = self::ERROR_PASSWORD_INVALID;
				}
			}
		}
		else
		{
			// For encryption
			$Store = new Store;
			
			$Criteria = new CDbCriteria;
			$Criteria->condition = "
				email = :email AND 
				origin_organisation_id = :origin_organisation_id AND 
				password IS NOT NULL 
			";
			
			$Criteria->params = array(
				':email' => $Store->encryptEmail($this->username),
				':origin_organisation_id' => 10,
			);
			
			$Store = Store::model()->with('store2contact','store2contact.accession')->find($Criteria);
			
			if(!is_null($Store))
			{
				// We have a user from THE LIST
				// Does the password match?
				if($Store->store2contact->accession->password === hash('sha256', $this->password . SHASALT))
				{
					$this->errorCode=self::ERROR_NONE;
					$this->_id = $Store->id;
					$this->userType = 'contact';
				}
				else
				{
					$this->errorCode = self::ERROR_PASSWORD_INVALID;
				}
			}
		}
		
		
		
		
		
		return !$this->errorCode;
	}
	
	public function getId()
	{
		return $this->_id;
	}
}