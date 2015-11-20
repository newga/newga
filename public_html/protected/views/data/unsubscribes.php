<?php

/* download the organisation unsubscribes */

?>
<div class="page-header">
	<h1>Download Unsubscribes</h1>
</div>

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'form-download-unsubscribes',
	'enableAjaxValidation'=>false,
	'htmlOptions' => array('enctype' => 'multipart/form-data')
)); ?>


	<div class="row">
		<div class="col-lg-12">

			<p>In the last 7 days <?= (int)$recent; ?> contacts have unsubscribed. In total <?= (int)$total; ?> contacts have unsubscribed.</p>

			<?php if($total >= 0){ ?>
			<?= CHtml::submitButton('Download all unsubscribes', array('name' => 'download', 'class' => 'btn btn-primary')); ?>
			<?php } ?>

		</div>
	</div>

<?php $this->endWidget(); ?>