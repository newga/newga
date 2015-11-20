<?php

//var_dump($rowNumber);

?>
<li>
	<div class="row <?=($disabled ? 'disabled' : '');?>" data-row="<?php print $rowNumber; ?>">
		<div class="col-md-2">
			<div class="form-group first">
<?php

// Add hidden input to hold value of disabled select
if($disabled)
{
?>
					<input type="hidden" name="current[and_choice][<?php print $rowNumber; ?>]" value="<?php print $and_choice; ?>" />
<?php
}

?>
					<select class="form-control and_choice blue " <?=($disabled ? 'disabled' : '');?> name="current[and_choice][<?php print $rowNumber; ?>]" >
						<option class="and" value="1" <?php if ($and_choice==1) echo 'selected="selected"'; ?>>And</option>
						<option class="or" value="0" <?php if ($and_choice==0) echo 'selected="selected"'; ?>>Or</option>
					</select>	
					<p class="contact">Each contact</p>
				</div>
			</div>


			<div class="col-md-2">
				<div class="form-group">
<?php

// Add hidden input to hold value of disabled select
if($disabled)
{
?>
					<input type="hidden" name="current[bool_choice][<?php print $rowNumber; ?>]" value="<?php print $bool_choice; ?>" />
<?php
}

?>
					<select class="form-control bool_choice " <?=($disabled ? 'disabled' : '');?> name="current[bool_choice][<?php print $rowNumber; ?>]">
						<option class="positive" value="1" <?php if ($bool_choice==1) echo 'selected="selected"'; ?> ><?=$Question->positiveLang;?></option>
						<option class="negative" value="0" <?php if ($bool_choice==0) echo 'selected="selected"'; ?> ><?=$Question->negativeLang;?></option>
					</select>
				</div>
			</div>


		<div class="col-md-3">
			<div class="form-group">

				<select class="form-control query_choice " <?=($disabled ? 'disabled' : '');?> name="current[query_choice][<?php print $rowNumber; ?>]">
				<option> -- Choose -- </option>
	<?php
	$currentType = 0; 
	foreach ($QueryQuestions as $QueryQuestion) { 
		
		if (!(Yii::app()->user->role < User::ROLE_MANAGER && $QueryQuestion->id == 15))
		{

			if ($QueryQuestion->type != $currentType)
			{
?>
					<optgroup label="<?=$QueryQuestion->TypeName;?>">
<?php 
				$currentType = $QueryQuestion->type;
			}
			
			$currentValueSelected = $query_choice == $QueryQuestion->id;
			$selectedValue = $query_choice;
?>

					<option data-id="<?=$QueryQuestion->id;?>" data-has-value="<?=$QueryQuestion->has_value;?>" data-lang="<?=$QueryQuestion->lang_type;?>" value="<?=$QueryQuestion->id;?>" <?php if ($currentValueSelected) echo 'selected="selected"'; ?> ><?=$QueryQuestion->question;?></option>
			
			
<?php 

			if ($QueryQuestion->type != $currentType)
			{
?>
					</optgroup>
<?php 
			}
		}
	} 
	?>
				</select>
<?php

// Add hidden input to hold value of disabled select
if($disabled)
{
?>
					<input type="hidden" name="current[query_choice][<?php print $rowNumber; ?>]" value="<?php print $selectedValue; ?>" />
<?php
}

?>
			</div>
		</div>


		<div class="col-md-3 query_options <?=($disabled ? 'disabled' : '');?>" <?=($disabled ? 'disabled' : '');?> <?php if ($Question->has_value) echo 'style="display:block"';?> >
			<?php $this->renderPartial('_options',array(
				'Question' => $Question,
				'query_number' => $query_number,
				'query_option' => $query_option,
				'rowNumber' => $rowNumber,
			)); ?>
		</div>

		<?php //this spacer is so the move and delete tools are in the correct position ?>
		<div class="col-md-3 spacer" <?php if ($Question->has_value == NULL) echo 'style="display:block"';?> >
		</div>


		<div class="col-md-2">
			<?php if (!$disabled) { ?>
			<div class="form-group query_btns">
				<a href="#" class="query_row_up btn btn-default"><span class="glyphicon glyphicon-arrow-up"></span></a>
				<a href="#" class="query_row_down btn btn-default"><span class="glyphicon glyphicon-arrow-down"></span></a>
				<a href="#" class="query_row_delete btn btn-default"><span class="glyphicon glyphicon-trash"></span></a>
			</div>
			<?php } ?>
		</div>

	

	</div>
</li>
