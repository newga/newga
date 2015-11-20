<div class="page-header">
	<h1>Users</h1>
</div>

<?php 

if(Yii::app()->user->hasFlash('success'))
{
?>
	<div class="alert alert-success"><?php print Yii::app()->user->getFlash('success'); ?></div>
<?php 
}
?>

<div class="row">
	<div class="col-xs-12">
		<?php echo CHtml::link('Create new User', array('user/create'), array('class' => 'btn btn-primary pull-right')); ?>
	</div>
</div>


<?php
$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'blog-grid',
	'dataProvider'=> $User->search(),
	'filter'=>$User,
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
			'name' => 'filterFullName',
			'type' => 'html',
			'value' => function($User) {
				if($User->mothballed){
					return '<span class="text-muted">' . CHtml::encode($User->FullName) . ' (archived)</span>';
				}
				return CHtml::encode($User->FullName);
			}
		),
		array(
			'name' => 'role',
			'type' => 'html',
			'value' => function($User) {
				if($User->mothballed){
					return '<span class="text-muted">' . CHtml::encode($User->AdminType) . ' (archived)</span>';
				}
				return CHtml::encode($User->AdminType);
			}
		),
		array(
			'name' => 'organisation_id',
			'type' => 'html',
			'value' => function($User) {
				if($User->mothballed){
					return '<span class="text-muted">' . $User->OrganisationName . ' (archived)</span>';
				}
				return $User->OrganisationName;
			},
			'filter' => CHtml::listData(Organisation::model()->findAll(array('condition' => 'id != 10')), 'id', 'title')
		),
		array(
			'header' => 'Edit',
			'filter' => false,
			'type' => 'html',
			'value' => function($User) {
				if($User->mothballed){ return ''; }
				return CHtml::link('Edit',array('user/update','id'=>$User->id), array('class' => 'btn btn-default'));
			},
			'htmlOptions' => array('class'=>'center', 'style' => 'width: 40px;'),
		),
	),
)); 
?>
