<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'accession-form-1',
	'enableAjaxValidation'=>false
)); ?>

<?php echo $form->errorSummary($Accession, '', '', array('class' => 'alert alert-danger')); ?>

<h1>Join Application Name</h1>
<hr>
<div class="row">
	<div class="col-sm-6">
		Welcome
	</div>
	<div class="col-sm-6">

		<div class="checkbox">
			<label for="tandc">I agree to my information being added to and used as part of <?php print Yii::app()->name; ?>.
				<?php print CHtml::activeCheckBox($Accession, 'terms_agreed', array('id' => 'tandc')); ?>
				<span class="tick">
					<img src="/css/assets/pink-tick-90x90.png" height="45" alt="">
				</span>
			</label>
		</div>
		
		
		
	

	</div>
</div>

<div class="form-actions">
	<?php echo CHtml::submitButton('Join Application Name', array('class' => 'btn pull-right')); ?>
</div>

<?php $this->endWidget(); ?>
