<div class="survey hide-radios">
<?php print $this->renderPartial('_progressbar', array(
	'progress' => $progress,
)); ?>

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'accession-form-3',
	'enableAjaxValidation'=>false
)); ?>

<hr>
<?php //echo $form->errorSummary($CSSurvey, '', '', array('class' => 'alert alert-danger')); 

if(sizeof($CSSurvey->errors))
{
?>
<div class="alert alert-danger">
Please answer all the questions
</div>
<?php
}


?>

<div class="row">
	<div class="col-sm-10">
		<h3>The following statements refer to your personal approach to life.<br />Please indicate the degree to which you agree or disagree with each statement:</h3>
	</div>
</div>


<?php


for($q = 1; $q <= 8; $q++)
{
?>
	<div class="question<?php print $CSSurvey->hasErrors('q2_' . $q) ? ' has-error':''; ?>">
		<h3><?php print $CSSurvey->getAttributeLabel('q2_' . $q); ?></h3>
		
		<div class="answers">
			<div class="labels">
				<span class="first"><span>Definitely Agree</span></span>
				<span class="second">Neither</span>
				<span class="last"><span>Definitely Disagree</span></span>
			</div>
			<div class="hr"></div>
<?php
	for($a = 1; $a < 6; $a++)
	{
?>
			<div class="answer answer<?php print $a; ?><?php print $CSSurvey['q2_' . $q] == $a ? ' ticked':''; ?>">
				<label for="q2_<?php print $q; ?>_<?php print $a; ?>">
					<?php print $form->radioButton($CSSurvey, ('q2_' . $q), array('value' => $a, 'uncheckValue' => null, 'id' => 'q2_'.$q.'_'.$a)); ?>
					<span class="tick">
						<img src="/css/assets/pink-tick-90x90.png" height="45" alt="">
					</span>
				</label>
			</div>
<?php
	}
?>
		</div>
	</div>
	
	
<?php
}

?>
	<div class="question<?php print $CSSurvey->hasErrors('q17') ? ' has-error':''; ?>">
		<h3><?php print $CSSurvey->getAttributeLabel('q17'); ?></h3>
		
		<div class="answers">
			<div class="labels">
				<span class="first"><span>Definitely Agree</span></span>
				<span class="second">Neither</span>
				<span class="last"><span>Definitely Disagree</span></span>
			</div>
			<div class="hr"></div>
<?php
	for($a = 1; $a < 6; $a++)
	{
?>
			<div class="answer answer<?php print $a; ?><?php print $CSSurvey['q17'] == $a ? ' ticked':''; ?>">
				<label for="q17_<?php print $a; ?>">
					<?php print $form->radioButton($CSSurvey, ('q17'), array('value' => $a, 'uncheckValue' => null, 'id' => 'q17_'.$a)); ?>
					<span class="tick">
						<img src="/css/assets/pink-tick-90x90.png" height="45" alt="">
					</span>
				</label>
			</div>
<?php
	}
?>
		</div>
	</div>
	
	<div class="question<?php print $CSSurvey->hasErrors('q18') ? ' has-error':''; ?>">
		<h3><?php print $CSSurvey->getAttributeLabel('q18'); ?></h3>
		
		<div class="answers">
			<div class="labels">
				<span class="first"><span>Definitely Agree</span></span>
				<span class="second">Neither</span>
				<span class="last"><span>Definitely Disagree</span></span>
			</div>
			<div class="hr"></div>
<?php
	for($a = 1; $a < 6; $a++)
	{
?>
			<div class="answer answer<?php print $a; ?><?php print $CSSurvey['q18'] == $a ? ' ticked':''; ?>">
				<label for="q18_<?php print $a; ?>">
					<?php print $form->radioButton($CSSurvey, ('q18'), array('value' => $a, 'uncheckValue' => null, 'id' => 'q18_'.$a)); ?>
					<span class="tick">
						<img src="/css/assets/pink-tick-90x90.png" height="45" alt="">
					</span>
				</label>
			</div>
<?php
	}
?>
		</div>
	</div>

	<h2>Have you ever been to any of these events or places?</h2>

<?php

for($q = 1; $q < 4; $q++)
{
?>
	<div class="question<?php print $CSSurvey->hasErrors('q1_' . $q) ? ' has-error':''; ?>">
		<h3><?php print $CSSurvey->getAttributeLabel('q1_' . $q); ?></h3>
		<table>
			<tr>
				<td class="<?php print $CSSurvey['q1_' . $q] == '1a' ? 'ticked':''; ?>">
					<label for="q1_<?php print $q; ?>_1">
						<?php print $form->radioButton($CSSurvey, 'q1_' . $q, array('value' => '1a', 'uncheckValue' => null, 'id' => 'q1_'.$q.'_1')); ?>
						<span class="tick">
							<img src="/css/assets/pink-tick-90x90.png" height="45" alt="">
						</span>
						I have been
					</label>
				</td>
			</tr>
			<tr>
				<td class="<?php print $CSSurvey['q1_' . $q] == '1b' ? 'ticked':''; ?>">
					<label for="q1_<?php print $q; ?>_2">
						<?php print $form->radioButton($CSSurvey, 'q1_' . $q, array('value' => '1b', 'uncheckValue' => null, 'id' => 'q1_'.$q.'_2')); ?>
						<span class="tick">
							<img src="/css/assets/pink-tick-90x90.png" height="45" alt="">
						</span>
						I would be interested
					</label>
				</td>
			</tr>
			<tr>
				<td class="<?php print $CSSurvey['q1_' . $q] == '0' ? 'ticked':''; ?>">
					<label for="q1_<?php print $q; ?>_3">
						<?php print $form->radioButton($CSSurvey, 'q1_' . $q, array('value' => '0', 'uncheckValue' => null, 'id' => 'q1_'.$q.'_3')); ?>
						<span class="tick">
							<img src="/css/assets/pink-tick-90x90.png" height="45" alt="">
						</span>
						I'm not interested
					</label>
				</td>
			</tr>
		</table>
	</div>
<?php
}

?>

<div class="form-actions">
	<div class="form-actions-col">
<?php

if(!$Accession->children)
{
	$prevStep = $progress - 2;
}
else
{
	$prevStep = $progress - 1;
}

?>
		<a href="/accession/<?php print $prevStep; ?>/<?php print $Accession->accession_hash; ?>" class="btn btn-default">Previous</a> <br /><span class="text-muted">(Changes on this page will not be saved)</span>
	</div>
	<div class="form-actions-col">
		<?php echo CHtml::submitButton('Next', array('name' => 'submit-cs-form', 'class' => 'btn pull-right')); ?>
	</div>
</div>

<?php $this->endWidget(); ?>

</div>
