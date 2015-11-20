<div class="page-header">
	<h1>Venues</h1>
</div>

<div class="row">
	<div class="col-xs-12">
		<?php echo CHtml::link('Create new venue', array('venues/create'), array('class' => 'btn btn-primary pull-right')); ?>
	</div>
</div>

<?php
$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'blog-grid',
	'dataProvider'=> $Venue->search(),
	'filter'=>$Venue,
	'filterCssClass'=> 'form-controls',
	'pagerCssClass'=> 'yiipager',
	'itemsCssClass' => 'table table-bordered table-striped table-hover table-responsive',
	'pager' => array(
		'header' => false,
		'htmlOptions'=> array('class'=>'pagination'),
		'hiddenPageCssClass' => 'disabled',
		'maxButtonCount' => 3,
        'cssFile' => false,
   
	),
	'cssFile' => false,
	'columns'=>array(
		
		array(
			'name' => 'title',
			'type' => 'html',
			'value' => function($Venue) {
				return $Venue->title;
			}
		),
		array(
			'name' => 'active',
			'type' => 'html',
			'value' => function($Venue) {
				return ($Venue->active ? 'Yes' : 'No');
			},
			'filter' => array(
				1 => 'Yes',
				0 => 'No',
			),
			'htmlOptions' => array('style' => 'text-align: center; width: 40px;'),
		),
		array(
			'header' => 'Edit',
			'filter' => false,
			'type' => 'html',
			'value' => function($Venue) {
				return CHtml::link('Edit',array('venue/update','id'=>$Venue->id), array('class' => 'btn btn-default'));
			},
			'htmlOptions' => array('class'=>'center', 'style' => 'width: 40px;'),
		),
	),
)); 
?>
