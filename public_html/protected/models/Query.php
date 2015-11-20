<?php

class Query extends CActiveRecord
{
	public $filterName;
	public $filterOrganisation;
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
		return 'query';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name,description', 'required'),
			array('name', 'length', 'max' => 100),
			array('name', 'uniqueQueryName', 'on'=>'insert'),

			array('description', 'length', 'max' => 10000),

			//make sure there is actually a rule
			array('JSON', 'length', 'min' => 50, 'tooShort'=>'Please add at least one rule.'),
			array('invite', 'in', 'range' => array('0', '1')),

			array('id,num_contacts,user_id', 'numerical', 'integerOnly'=>true),

			// array('active', 'numerical', 'integerOnly'=>true),

			// array('title', 'length', 'max'=>100),
			array('id, name, active, num_contacts, user, filterName, invite', 'safe', 'on'=>'search'),
		);
	}


	/**
	* Custom validation for unqiue name for query and user *
	**/

	public function uniqueQueryName($attribute,$params)
	{

	    $user_id = Yii::app()->user->getId();

	    $result = Query::model()->findByAttributes(array(
	    	'user_id' => $user_id,
	    	'name'=>$this->name
	    ));

	    //check name is unqie
	    if ($result) {

	    	$this->addError($attribute, 'You already have a query with that name.');

	    }

	}

	public function getCanUserEdit() {
		return (!($this->user_id != Yii::app()->user->getId() && Yii::app()->user->role < User::ROLE_MANAGER));

	}


	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
			'user'=>array(self::BELONGS_TO, 'User', 'user_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'created' => 'Created date',
			'num_contacts' => 'No. of contacts',
			'filterName' => 'Author',
			'filterOrganisation' => 'Organisation'
		);
	}


	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search() {

		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria = new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('invite',$this->invite);
		//$criteria->compare('author',$this->author->FullName,true);

		$criteria->with = 'user';

		if(trim($this->filterName))
		{
			$criteria->compare('CONCAT_WS(\' \', user.first_name, user.last_name)', $this->filterName, true);
		}


		return new CActiveDataProvider($this, array(
		'criteria'=>$criteria,
		'pagination' => array(
			'pageSize' => 10,
		),
		'sort'=>array(
				'attributes'=>array(
					'filterName'=>array(
						'asc' => 'CONCAT_WS(\' \', user.first_name, user.last_name)',
						'desc' => 'CONCAT_WS(\' \', user.first_name, user.last_name) DESC',
					),
					'*',
				),
				'defaultOrder'=>'name ASC',
			),
		));

	}

	/*
	 For invite queries, this returns just one column for counting purposes
	*/
	public function runInviteCountQuery()
	{
		return $this->run(array('contact_warehouse_id'), null, true, false, true, false);
	}

	/*
	 For invite queries, this returns just one column for counting purposes
	*/
	public function runCampaignCountQuery()
	{
		return $this->run(array('contact_warehouse_id'), null, false, false, true);
	}

	/*
	 For invite queries, this returns all the required columns to create invites
	*/
	public function runInviteContactQuery()
	{
		return $this->run(array(
			'contact_warehouse_id',
			'store2contact_id',
			'store_id',
			'email',
			'first_name',
			'last_name',
			'origin_organisation_id'
		), null, true, true, false);
	}

	/*
	 For campaign queries, this returns all the required columns to create the campaign
	*/
	public function runCampaignQuery($selectFields = array('contact_warehouse_id'))
	{
		return $this->run($selectFields, null, false, true, false, true, true);
	}


	public function run($selectFields = array('contact_warehouse_id'), $filterByOrganisation = null, $invite = false, $random = false, $count = false, $accessionOnly = true)
	{

		$start = microtime(true);

		// Sort out select fields
		foreach($selectFields as $k => $selectField)
		{
			$selectFields[$k] = 'clean_warehouse.' . $selectField;
		}

		// Make them into a string
		$selectFields = implode(',', $selectFields);

		// Set up the command
		$command = Yii::app()->db->createCommand();
		$params = array();

		$conditions = json_decode($this->JSON, true);
		$whereSql ='';
		$joinSQL = '';

		// Var to identify if we've already joined the invite table into the query
		$invitesJoined = false;
		$i = 0;

		if(sizeof($conditions['rows']))
		{
			foreach($conditions['rows'] as $key => $row)
			{
				//print_r($row);
				// $key is used for $params array.


				//get question from the database
				$QueryQuestion = QueryQuestion::model()->findByPk($row['query_choice']);

				// after the first item we need an AND / OR to link them
				if ($i > 0)
				{
					if ($row['and_choice']) {
						$whereSql .= "\n AND";
					}
					else
					{
						$whereSql .= "\n OR";
					}
				}

				// not all questions make use of a field name, check if we have one
				if($QueryQuestion->field_name)
				{
					// we start with the field name
				 	$whereSql .= ' `' . $QueryQuestion->field_name . '`';
				}

			 	//if there is no comparison option we check for null or empty
			 	if (!$QueryQuestion->has_value) {


			 		switch($QueryQuestion->id)
			 		{
				 		case 20:
				 		{
		 					// Each contact has any previous invites
		 					if($row['bool_choice'])
		 					{
			 					$joinSQL .= "\n LEFT JOIN `invite` AS pendingInvites ON pendingInvites.`contact_warehouse_id` = `clean_warehouse`.`contact_warehouse_id`" . "\n ";
								$whereSql .= " pendingInvites.`id` IS NOT NULL";

			 					$invitesJoined = true;
			 				}
			 				// Each contact does not have any previous invites
			 				else
			 				{

				 				$joinSQL .= "\n LEFT JOIN `invite` AS pendingInvites ON pendingInvites.`contact_warehouse_id` = `clean_warehouse`.`contact_warehouse_id`" . "\n ";
								$whereSql .= " pendingInvites.id IS NULL";

			 					$invitesJoined = true;

			 				}
				 		
				 			break;
			 			}

			 			case 21:
			 			{
			 				if($row['bool_choice'])
			 				{
			 					// 1+
			 					$whereSql .= ' > 0';
			 				}
			 				else
			 				{
			 					// 0 OR NULL due to error with initial value being set to 0
			 					$whereSql .= ' < 1 OR ' . $QueryQuestion->field_name . ' IS NULL';
			 				}

			 				break;
			 			}

				 		case 26:
				 		{
				 			// has completed all assession forms

							if ($row['bool_choice'])
							{
								$whereSql .= ' = 1';
							}
							else
							{
								$whereSql .= ' != 1';
							}

							break;
						}

				 		default:
				 		{
					 		//TRUE
					 		if ($row['bool_choice']) {

					 			$whereSql .= ' IS NOT NULL';

					 			// Add empty string check for email
					 			if($QueryQuestion->id == 6)
					 			{
						 			$whereSql .= ' AND email != "" ';
					 			}

							}
							//FALSE
							else {

								$whereSql .= ' IS NULL';
							}

							break;
						}
					}
					//$whereSql .= "--\n\nOption query\n\n";
			 	}
			 	else
			 	{
			 		switch ($QueryQuestion->id) {

			 			//older than
			 			case 4:

				 			if ($row['bool_choice']) {
				 				$whereSql .= ' < :date' . $key;
				 			}
				 			else
				 			{
				 				$whereSql .= ' > :date' . $key;
				 			}

				 			$birth = strtotime('today - '.(int)$row['query_number'].' years');
				 			$params[':date' . $key] = "'" . date('Y-m-d', $birth) . "'";

			 			break;


			 			//younger than
			 			case 3:

				 			if ($row['bool_choice']) {
				 				$whereSql .= ' > :date' . $key;
				 			}
				 			else
				 			{
				 				$whereSql .= ' < :date' . $key;
				 			}

							$birth = strtotime('today - '.(int)$row['query_number'].' years');
				 			$params[':date' . $key] = "'" . date('Y-m-d', $birth) . "'";

			 			break;


			 			//culture segment
			 			case 5:

				 			if ($row['bool_choice']) {
				 				$whereSql .= ' = :text' . $key;
				 			}
				 			else
				 			{
				 				$whereSql .= ' != :text' . $key . ' OR ' . $QueryQuestion->field_name . ' IS NULL';
				 			}

			 				$CS = CultureSegment::model()->findByPk($row['query_option']);
							$params[':text' . $key] = "'" . $CS->name . "'";

			 			break;

			 			//level of engagement of
			 			case 10:

				 			if ($row['bool_choice']) {
				 				$whereSql .= ' = :engage' . $key;
				 			}
				 			else
				 			{
				 				$whereSql .= ' != :engage' . $key;
				 			}

							$params[':engage' . $key] = '"' . QueryQuestion::model()->levelOfEngagement($row['query_option']) . '"';

			 			break;

						// Visited a venue
						case 11:
						case 12:
						case 13:
						case 14:
							/*
							1 = I've visited
							2 = I've visited in last 3 years
							3 = Never been but I would
							4 = Never been and don't plan to
							*/

							switch($QueryQuestion->id)
							{
								case 11:
									$venueVisitedValue = 1;
								break;

								case 12:
									$venueVisitedValue = 2;
								break;

								case 13:
									$venueVisitedValue = 3;
								break;

								case 14:
									$venueVisitedValue = 4;
								break;

								default:
									$venueVisitedValue = 1;
								break;
							}

							$joinSQL .= "

LEFT JOIN contact2venue join".$key." ON (

	join".$key.".accession_id = clean_warehouse.accession_id

	AND

	join".$key.".venue_id = ".(int)$row['query_option']."

)";

							if ($row['bool_choice']) {
				 				$whereSql .= "  join".$key.".visited = " . (int)$venueVisitedValue;
				 			}
				 			else
				 			{
				 				$whereSql .= "  join".$key.".visited != " . (int)$venueVisitedValue;
				 			}



						break;

						case 15:

							$joinSQL .= "

LEFT JOIN campaign_contact join".$key." ON contact_warehouse_id = join".$key.".warehouse_id

";

							if ($row['bool_choice'])
							{
								$whereSql = " campaign_id = :campaign_id".$key;
							}
							else
							{
								$whereSql = " campaign_id != :campaign_id".$key." OR campaign_id IS NULL ";
							}

							$params[":campaign_id".$key] = (int)$row['query_option'];

						break;

						//outcomes
						case 27:

							if ($row['bool_choice'])
							{
							
								$whereSql .= " clean_warehouse.contact_warehouse_id IN (SELECT warehouse_id FROM campaign_contact2outcome JOIN campaign_contact ON campaign_contact2outcome.campaign_contact_id = campaign_contact.id AND campaign_outcome_id = :campaign_outcome_id AND outcome IS NOT NULL)";
							}
							else {

								$whereSql .= " clean_warehouse.contact_warehouse_id NOT IN (SELECT warehouse_id FROM campaign_contact2outcome JOIN campaign_contact ON campaign_contact2outcome.campaign_contact_id = campaign_contact.id AND campaign_outcome_id = :campaign_outcome_id AND outcome IS NOT NULL)";

							}
							$params[":campaign_outcome_id"] = (int)$row['query_option'];
						break;

			 			//origin id
			 			case 16:

				 			if ($row['bool_choice']) {
				 				$whereSql .= ' = :origin' . $key;
				 			}
				 			else
				 			{
				 				$whereSql .= ' != :origin' . $key;
				 			}

			 				$orgId =  preg_replace("/[^0-9]/","", $row['query_option']);
				 			$params[':origin' . $key] = (int)$orgId;

			 			break;


			 			// invited date
			 			case 18:


			 			break;

						// was / not invited before in a particular invite query
			 			case 19:


			 			break;



						// Visited an artform
						case 22:
						case 23:
						case 24:
						case 25:
							/*
							1 = I've visited
							2 = I've visited in last 3 years
							3 = Never been but I would
							4 = Never been and don't plan to
							*/

							switch($QueryQuestion->id)
							{
								case 22:
									$artformVisitedValue = 1;
								break;

								case 23:
									$artformVisitedValue = 2;
								break;

								case 24:
									$artformVisitedValue = 3;
								break;

								case 25:
									$artformVisitedValue = 4;
								break;

								default:
									$artformVisitedValue = 1;
								break;
							}

							$joinSQL .= " 
								LEFT JOIN contact2artform join".$key." ON join".$key.".accession_id = clean_warehouse.accession_id
									AND join".$key.".artform_id = ".(int)$row['query_option'] . "
							";

							if ($row['bool_choice'] == 1)
							{
				 				$whereSql .= "  join".$key.".visited = " . (int)$artformVisitedValue;
				 			}
				 			else
				 			{
				 				$whereSql .= "  (join".$key.".visited IS NULL OR join".$key.".visited != " . (int)$artformVisitedValue . ') ';
				 			}



						break;

			 		}


			 	}


			 	$i++;

			} // foreach
		}


		if(strlen($whereSql))
		{
			$whereSql = '(' . $whereSql . ')';
		}

		if((int)$filterByOrganisation)
		{
			if(strlen($whereSql))
			{
				$whereSql .= ' AND ';
			}

			// filter down to a single organisation outside the saved query.
			// Used when an organisation downloads query results which aren't filtered to only their own organisation already.
			$whereSql .= ' (origin_organisation_id = :originOrganisation)';
			$params[':originOrganisation'] = (int)$filterByOrganisation;
		}

		// Are we querying people in the list, or not?
		// Regular campaign query = Contacts in the List
		// Invite query = Contacts not in the List
		if(!$invite) // false or null or 0
		{
			// All records, no restrictions

			$joinSQL .= "
				LEFT JOIN `suppression_list` sup1 ON sup1.`store2contact_id` = `clean_warehouse`.`store2contact_id`
				LEFT JOIN `suppression_list` sup2 ON (sup2.`warehouse_id` = `clean_warehouse`.`contact_warehouse_id`)
			" . " \n";

			if(strlen($whereSql))
			{
				$whereSql .= ' AND ';
			}

			$whereSql .= ' ( sup1.`id` IS NULL) AND ( sup2.`id` IS NULL)';

		}
		elseif($invite)
		{
			if(strlen($whereSql))
			{
				$whereSql .= ' AND ';
			}


			// standard invite exclusions - not agreed terms, not insider row, has got an email
			$whereSql .= "\n (terms_agreed IS NULL AND email IS NOT NULL AND origin_organisation_id != :inviteOrgOrganisation)" . " \n";
			$params[':inviteOrgOrganisation'] = 10;


			// suppression list exclusions. We do two joins here for speed

			//$joinSQL .= "\n LEFT JOIN `suppression_list` ON `suppression_list`.`store_id` = `clean_warehouse`.`store_id`" . " \n";
			$joinSQL .= "
				LEFT JOIN `suppression_list` sup1 ON sup1.`store2contact_id` = `clean_warehouse`.`store2contact_id`
				LEFT JOIN `suppression_list` sup2 ON (sup2.`warehouse_id` = `clean_warehouse`.`contact_warehouse_id`)
			" . " \n";
			$whereSql .= ' AND ( sup1.`id` IS NULL) AND ( sup2.`id` IS NULL)';


			//exclusion - prevent pending invite contacts being included

			if(!$invitesJoined)
			{
				$joinSQL .= "\n LEFT JOIN ( SELECT * FROM `invite` WHERE `invite`.`status` = 0 ) AS pendingInvites ON pendingInvites.`contact_warehouse_id` = `clean_warehouse`.`contact_warehouse_id`" . " \n";
				$whereSql .= " AND pendingInvites.`status` IS NULL ";
			}
			// End invite exclusion
		}

		// Order randomly
		if($random) // This is a function param - default false
		{
			$command->order("RAND()");
		}
		elseif($count !== true)
		{
			$command->order('clean_warehouse.contact_warehouse_id ASC');
		}

		if($count)
		{
			$command->select('clean_warehouse.contact_warehouse_id');
		}
		else
		{
			// all the defined fields
			$command->select($selectFields);
		}

		if ($accessionOnly) {
			if(strlen($whereSql))
			{
				$whereSql .= ' AND ';
			}

			$whereSql .= 'terms_agreed IS NOT NULL';
		}


		$command->from('clean_warehouse');
		$command->where($whereSql, $params);

		if(strlen($joinSQL))
		{
			$command->join = $joinSQL;
		}

		$sql_string = $command->text;
		$query = str_replace(
			array_keys($command->params),
			array_values($command->params),
			$command->text
		);

		if($count)
		{
			// only return the total
			$command = Yii::app()->db->createCommand(
				"SELECT COUNT(*) AS rowCount FROM (
					SELECT contact_warehouse_id FROM (" . $query . ") AS shuffleStore2Contact GROUP BY contact_warehouse_id
					" . (isset($conditions['limit']) && $conditions['limit'] > 0 ? ' limit ' . (int)$conditions['limit'] : '') . "
				) AS t
				"
			);
			//exit($command->text);
			$result = $command->queryRow();
			$contactCount = $result['rowCount'];
		}
		else
		{
			// return the full row. Group by contact_warehouse_id to remove dupe rows
			$command = Yii::app()->db->createCommand(
				"SELECT * FROM (".$query.") AS shuffleStore2Contact GROUP BY contact_warehouse_id" .
				($random ? " ORDER BY RAND()":"") .
				(isset($conditions['limit']) && $conditions['limit'] > 0 ? ' limit ' . (int)$conditions['limit'] : '')

			);



			//print $command->text;exit();

			$contacts = $command->queryAll();
			$contactCount = count($contacts);
		}

		$sql_string = $command->text;
		//exit($sql_string);

		unset($whereSql, $command);

		$returnArray = array(
			'rows' => $contacts,
			'count' => $contactCount
		);

		if(ENVIRONMENT === 'LOCAL')
		{
			$returnArray['sql'] = $sql_string;
			$returnArray['params'] = $params;
		}

		$end = microtime(true);

		$returnArray['queryTime'] = round($end - $start, 4);

		$returnArray['ip'] = $_SERVER['REMOTE_ADDR'];

		return $returnArray;

	}



}