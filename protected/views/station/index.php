<?php

$this->breadcrumbs = array(
	Station::label(2) => array('index'),
	Yii::t('app', 'List'),
);

$this->menu = array(
	array('label'=>Yii::t('app', 'Manage') . ' ' . Station::label(2), 'url' => array('admin')),
);
?>

<h1><?php echo GxHtml::encode(Station::label(2)); ?></h1>

<?php $this->widget('zii.widgets.CListView', array(
	'dataProvider'=>$dataProvider,
	'itemView'=>'_view',
)); 