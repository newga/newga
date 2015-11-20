<div class="page-header">
	<h1>Campaigns</h1>
</div>
<?php

foreach(Yii::app()->user->getFlashes() as $state => $message){

	?><p class="alert alert-<?= $state; ?>"><?= $message; ?></p><?php
}

?>
<div class="row">
	<div class="col-xs-12">
		<?php echo CHtml::link('Create campaign', array('campaign/createUpdate'), array('class' => 'btn btn-primary pull-right')); ?>
	</div>
</div> 

<?php

$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'campaign-list',
	'dataProvider' => $Campaign->search(),
	'filter' => $Campaign,
	'filterCssClass' => 'form-controls',
	'pagerCssClass' => 'yiipager',
	'itemsCssClass' => 'table table-bordered table-striped table-hover table-responsive',
	'pager' => array(
		'header' => false,
		'htmlOptions'=> array('class'=>'pagination'),
		'hiddenPageCssClass' => 'disabled',
		'maxButtonCount' => 3,
        'cssFile' => false,
	),
	'cssFile' => false,
	'columns' => array(
		
		array(
			'name' => 'name',
			'type' => 'html',
			'value' => function($Campaign){
				return CHtml::link(CHtml::encode($Campaign->name), array('campaign/createUpdate', 'id' => $Campaign->id), array('class' => ''));
			}
		),

		array(
			'type' => 'html',
			'header' => 'Created by',
			'value' => function($Campaign){
				if($Campaign->creator->mothballed)
				{
					return '<span class="text-muted">' . CHtml::encode($Campaign->creator->fullName) . ' (archived)</span>';
				}

				return CHtml::encode($Campaign->creator->fullName);
			}
		),

		array(
			'header' => 'Groups',
			'value' => function($Campaign){
				return sizeof($Campaign->groups);
			},
		),
		array(
			//'name' => 'created',
			'header' => 'Created On',
			'type' => 'html',
			'value' => 'CHtml::encode(date("'.Yii::app()->params['dateFormat'].'", strtotime($data->created)))',
		),
		array(
			'header' => 'Status',
			'type' => 'html',
			'value' => function($Campaign){
				return $Campaign->statusHTML;
			}
		),
	),
));

?>