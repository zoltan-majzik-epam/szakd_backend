<?php
/* @var $this GraphsController */
/* @var $events Event[] */
$this->breadcrumbs = array(
	Yii::t('app', 'Graphs') => null,
	Yii::t('app', Yii::app()->user->getState('selected-interval')) => null,
	Yii::t('app', Yii::app()->user->getState('selected-station'))
);
$params = array('sid' => Yii::app()->user->getState('selected-station'));
?>


<div class="highcharts chartheight" id="weather-container">
	<img src="images/loader.gif" style="position:relative;margin-top:130px;" />
</div>
