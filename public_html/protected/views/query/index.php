<div class="page-header">
	<h1>Queries</h1>
</div>
<?php if(Yii::app()->user->hasFlash('success')) { ?>
	<div class="alert alert-success alert-vanish"><?php print Yii::app()->user->getFlash('success'); ?></div>
<?php } ?>

<div class="row">
	<div class="col-xs-12">
		<?php echo CHtml::link('Build new Query', array('query/create'), array('class' => 'btn btn-primary pull-right')); ?>
	</div>
</div> 

<?php
$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'blog-grid',
	'dataProvider'=> $Query->search(),
	'filter'=>$Query,
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
			'name' => 'name',
			'type' => 'html',
			'value' => function($Query){
				return CHtml::link($Query->name, array(
						'query/update',
						'id' => $Query->id,
					),
					
					array('class' => '')
				);
			}
		),
		array(
			'type' => 'html',
			'name' => 'filterName',
			'value' => function($Query)
			{
				if($Query->user->mothballed)
				{
					return '<span class="text-muted">' . CHtml::encode($Query->user->fullName) . ' (archived)</span>';
				}

				return $Query->user->FullName;
			}
		),
		array(
			'name' => 'filterOrganisation',
			'value' => '$data->user->OrganisationName',
			'type' => 'html',
		),
		array(
			'name' => 'created',
			'type' => 'html',
			'value' => 'CHtml::encode(date("'.Yii::app()->params['dateFormat'].'", strtotime($data->created)))',
		),
		array(
			'name' => 'num_contacts',
			'type' => 'html',
			'value' => function($Query) {
				return $Query->num_contacts;
			}
		),
		array(
			'header' => '',
			'filter' => false,
			'type' => 'html',
			'value' => function($Query) {
				//logic to deterimine if query is view or run
				if ($Query->canUserEdit) {
					return CHtml::link('Edit',array('query/update','id'=>$Query->id), array('class' => 'btn btn-default'));
				}
				else {
					return CHtml::link('Run',array('query/run','id'=>$Query->id), array('class' => 'btn btn-primary'));
				}
			},
			'htmlOptions' => array('class'=>'center', 'style' => 'width: 40px;'),
		),
	),
)); 
?>