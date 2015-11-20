<?php
$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'blog-grid',
	'dataProvider'=> $SuppressionList->search(),
	'filter'=>$SuppressionList,
	'filterCssClass'=> 'form-controls',
	'pagerCssClass'=> 'yiipager',
	'itemsCssClass' => 'table table-bordered table-striped table-hover',
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
			'name' => 'store.email',
			'type' => 'html',
			'value' => function($SuppressionList) {
				return CHtml::encode($SuppressionList->store->email);
			}
		),
		array(
			'name' => 'store.origin_organisation_id',
			'type' => 'html',
			'value' => function($SuppressionList) {
				return CHtml::encode($SuppressionList->store->organisation->title);
			}
		),
		array(
			'name' => 'warehouse_id',
			'type' => 'html',
			'value' => function($SuppressionList) {
				return CHtml::encode($SuppressionList->warehouse_id);
			}
		),
		array(
			'name' => 'store_id',
			'type' => 'html',
			'value' => function($SuppressionList) {
				return CHtml::encode($SuppressionList->store_id);
			}
		),
		array(
			'name' => 'store2contact_id',
			'type' => 'html',
			'value' => function($SuppressionList) {
				return CHtml::encode($SuppressionList->store2contact_id);
			}
		),
		array(
			'name' => 'type',
			'type' => 'html',
			'value' => function($SuppressionList) {
				return $SuppressionList->suppressionType;
			},
			'filter' => $SuppressionList->types(),
		),
	),
)); 
?>
