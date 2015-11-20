<div class="page-header">
	<h1>Invites</h1>
</div>

<?php

foreach(Yii::app()->user->getFlashes() as $state => $message){

	?><p class="alert alert-<?= $state; ?>"><?= $message; ?></p><?php
}

?>
<div class="row">
	<div class="col-xs-12">
		<?php echo CHtml::link('Create invite', array('query/invite'), array('class' => 'btn btn-primary pull-right')); ?>
	</div>
</div> 

<?php

$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'campaign-list',
	'dataProvider' => $Campaign->search(1),
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
				return $Campaign->name;
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

				return $Campaign->creator->fullName;
			}
		),
		array(
			//'name' => 'created',
			'header' => 'Created On',
			'type' => 'html',
			'value' => 'CHtml::encode(date("jS M Y H:i", strtotime($data->created)))',
		),
		array(
			'header' => 'Contacts',
			'type' => 'html',
			'value' => function($Campaign) {
				return number_format($Campaign->query->num_contacts);
			}
		),
		
		array(
			'header' => 'Status',
			'type' => 'html',
			'value' => function($Campaign){
				return $Campaign->statusHTML;
			}
		),
		array(
			'header' => 'Options',
			'type' => 'html',
			'value' => function($Campaign){
				return $Campaign->status == Campaign::STATUS_NOT_RUN ? '<a href="/invites/'.$Campaign->id.'" class="btn btn-default" title="Edit '.$Campaign->name.'">Edit</a>':'<a href="/invites/'.$Campaign->id.'/view" class="btn btn-default">View</a>';
			},
			'htmlOptions' => array(
				'style' => 'width: 50px;'
			),
		),
		array(
			'header' => 'Send',
			'type' => 'html',
			'value' => function($Campaign){
				return (strlen($Campaign->invite_email_subject && $Campaign->status == Campaign::STATUS_NOT_RUN) ? '<a href="/invites/'.$Campaign->id.'/send" class="btn btn-default">Send</a>':'');
			},
			'htmlOptions' => array(
				'style' => 'width: 50px;'
			),
		),
	),
));

?>