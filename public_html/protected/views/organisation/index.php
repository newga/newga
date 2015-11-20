<div class="page-header">
	<h1>Organisations</h1>
</div>
<!--
<div class="row">
	<div class="col-xs-12">
		<?php echo CHtml::link('Create new Organisation', array('organisations/create'), array('class' => 'btn btn-primary pull-right')); ?>
	</div>
</div>
-->
<?php
$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'blog-grid',
	'dataProvider'=> $Organisation->search(),
	'filter'=>$Organisation,
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
			'value' => function($Organisation) {
				return CHtml::encode($Organisation->title);
			}
		),
		array(
			'name' => 'view_name',
			'type' => 'html',
			'value' => function($Organisation) {
				return CHtml::encode($Organisation->view_name);
			}
		),
		array(
			'name' => 'active',
			'type' => 'html',
			'value' => function($Organisation) {
				return ($Organisation->active ? 'Yes' : 'No');
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
			'value' => function($Organisation) {
				return CHtml::link('Edit',array('organisation/update','id'=>$Organisation->id), array('class' => 'btn btn-default'));
			},
			'htmlOptions' => array('class'=>'center', 'style' => 'width: 40px;'),
		),
	),
)); 
?>
