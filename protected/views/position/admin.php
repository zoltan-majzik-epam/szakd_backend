<?php

$this->breadcrumbs = array(
	$model->label(2) => array('index'),
	Yii::t('app', 'List'),
);

$this->menu = array(
		array('label'=>Yii::t('app', 'List') . ' ' . $model->label(2), 'url'=>array('index')),
	);
?>

<h1><?php echo Yii::t('app', 'List') . ' ' . GxHtml::encode($model->label(2)); ?></h1>


<?php $this->widget('zii.widgets.grid.CGridView', array(
	'id' => 'position-grid',
	'dataProvider' => $model->search(),
	'columns' => array(
		'id',
		array(
				'name'=>'station_id',
				'value'=>'GxHtml::valueEx($data->station)',
				'filter'=>GxHtml::listDataEx(Station::model()->findAllAttributes(null, true)),
				),
		'date',
		'lat',
		'lng',
	),
)); ?>