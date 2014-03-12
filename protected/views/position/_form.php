<div class="form">


<?php $form = $this->beginWidget('GxActiveForm', array(
	'id' => 'position-form',
	'enableAjaxValidation' => false,
));
?>

	<p class="note">
		<?php echo Yii::t('app', 'Fields with'); ?> <span class="required">*</span> <?php echo Yii::t('app', 'are required'); ?>.
	</p>

	<?php echo $form->errorSummary($model); ?>

		<div class="row">
		<?php echo $form->labelEx($model,'station_id'); ?>
		<?php echo $form->dropDownList($model, 'station_id', GxHtml::listDataEx(Station::model()->findAllAttributes(null, true))); ?>
		<?php echo $form->error($model,'station_id'); ?>
		</div><!-- row -->
		<div class="row">
		<?php echo $form->labelEx($model,'date'); ?>
		<?php echo $form->textField($model, 'date'); ?>
		<?php echo $form->error($model,'date'); ?>
		</div><!-- row -->
		<div class="row">
		<?php echo $form->labelEx($model,'lat'); ?>
		<?php echo $form->textField($model, 'lat'); ?>
		<?php echo $form->error($model,'lat'); ?>
		</div><!-- row -->
		<div class="row">
		<?php echo $form->labelEx($model,'lng'); ?>
		<?php echo $form->textField($model, 'lng'); ?>
		<?php echo $form->error($model,'lng'); ?>
		</div><!-- row -->


<?php
echo GxHtml::submitButton(Yii::t('app', 'Save'));
$this->endWidget();
?>
</div><!-- form -->