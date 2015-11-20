<p>
<?php
	$booleanLogicText = "Or";

	if ($and_choice == 1)
	{
		$booleanLogicText = "And";
	}

	$questionLangText = $Question->negativeLang;

	if ($bool_choice == 1)
	{
		$questionLangText = $Question->positiveLang;
	}


	if($textFirstRow)
	{
		print "Each contact ".$questionLangText." ";
	}
	else
	{
		print $booleanLogicText." each contact ".$questionLangText." ";
	}



	$currentType = 0;
	foreach ($QueryQuestions as $QueryQuestion)
	{

		if($query_choice == $QueryQuestion->id)
		{
			print $QueryQuestion->question;
		}

	}

	$this->renderPartial('_textOptions',array(
		'Question' => $Question,
		'query_number' => $query_number,
		'query_option' => $query_option,
		'rowNumber' => $rowNumber,
	));
?>
</p>
