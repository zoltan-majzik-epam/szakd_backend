<div class="wide form">

<?php $form = $this->beginWidget('GxActiveForm', array(
	'action' => Yii::app()->createUrl($this->route),
	'method' => 'get',
)); ?>

	<div class="row">
		<?php echo $form->label($model, 'id'); ?>
		<?php echo $form->textField($model, 'id'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model, 'station_id'); ?>
		<?php echo $form->dropDownList($model, 'station_id', GxHtml::listDataEx(Station::model()->findAllAttributes(null, true)), array('prompt' => Yii::t('app', 'All'))); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model, 'date'); ?>
		<?php echo $form->textField($model, 'date'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model, 'lat'); ?>
		<?php echo $form->textField($model, 'lat'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model, 'lng'); ?>
		<?php echo $form->textField($model, 'lng'); ?>
	</div>

	<div class="row buttons">
		<?php echo GxHtml::submitButton(Yii::t('app', 'Search')); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- search-form -->
