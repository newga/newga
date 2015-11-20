<?php

class CultureSegmentsForm extends CFormModel
{
	// Question vars defined by previous devs for their calculation logic
	// Question 1
	public $q1_1;
	public $q1_2;
	public $q1_3;
	
	// Question 2
	public $q2_1;
	public $q2_2;
	public $q2_3;
	public $q2_4;
	public $q2_5;
	public $q2_6;
	public $q2_7;
	public $q2_8;
	
	public $q17;
	public $q18;
	
	public $error;
	
	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		return array(
			// Required values for each step
			array('q1_1, q1_2, q1_3, q2_1, q2_2, q2_3, q2_4, q2_5, q2_6, q2_7, q2_8, q17, q18', 'required', 'on' => 'step5'),
			
			// Question 1 - 'I have been' and 'I would be interested' count as 1, 'Not interested' counts as 0
			array('q1_1, q1_2, q1_3', 'in', 'range'=> array('1a','1b','0')),
			
			// Question 1 - All other answers can be between 1 and 5
			array('q2_1, q2_2, q2_3, q2_4, q2_5, q2_6, q2_7, q2_8', 'in', 'range' => array(1,2,3,4,5)),
		);
	}
	
	public function allQuestionsComplete($attribute,$params)
	{
		$allQuestionsComplete = true;
		
		foreach($this->fields() as $field)
		{
			if(!strlen($this->$field))
			{
				$allQuestionsComplete = false;
			}
		}
		
		if(!$allQuestionsComplete && !isset($this->errors['submit']))
		{
			$this->addError('submit', Yii::t('cultureSegments/survey','Please complete all the questions'));
		}
	}

	/**
	 * These are the questions
	 */
	public function attributeLabels()
	{
		return array(
			'q1_1' => 'Question text',
			'q1_2' => 'Question text',
			'q1_3' => 'Question text',
			
			'q2_1' => 'Question text',
			'q2_2' => 'Question text',
			'q2_3' => 'Question text',
			'q2_4' => 'Question text',
			'q2_5' => 'Question text',
			'q2_6' => 'Question text',
			'q2_7' => 'Question text',
			'q2_8' => 'Question text',
			
			'q17' => 'Question text',
			'q18' => 'Question text',
		);
	}
	
	public function fields()
	{
		return array('q1_1', 'q1_2', 'q1_3', 'q2_1', 'q2_2', 'q2_3', 'q2_4', 'q2_5', 'q2_6', 'q2_7');
	}
}
