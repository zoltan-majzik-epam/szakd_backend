<?php
/* @var $this UserController */
/* @var $model User */
/* @var $form CActiveForm */
?>

<div class="form">

	<?php
	$form = $this->beginWidget('CActiveForm', array(
		'id' => 'user-form',
		// Please note: When you enable ajax validation, make sure the corresponding
		// controller action is handling ajax validation correctly.
		// There is a call to performAjaxValidation() commented in generated controller code.
		// See class documentation of CActiveForm for details on this.
		'enableAjaxValidation' => false,
	));
	/* @var $form CActiveForm */
	?>

	<p class="note">Fields with <span class="required">*</span> are required.</p>

	<?php echo $form->errorSummary($model);?>

	<div class="row">
		<?php echo $form->labelEx($model, 'name'); ?>
		<?php echo $form->textField($model, 'name', array('size' => 60, 'maxlength' => 64)); ?>
		<?php echo $form->error($model, 'name'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model, 'email'); ?>
		<?php echo $form->textField($model, 'email', array('size' => 60, 'maxlength' => 64)); ?>
		<?php echo $form->error($model, 'email'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model, 'username'); ?>
		<?php echo $form->textField($model, 'username', array('size' => 32, 'maxlength' => 32)); ?>
		<?php echo $form->error($model, 'username'); ?>
	</div>
	<?php if ($model->scenario == 'insert') : ?>
		<div class="row">
			<?php echo $form->labelEx($model, 'password'); ?>
			<?php echo $form->passwordField($model, 'password', array('size' => 60, 'maxlength' => 128)); ?>
			<?php echo $form->error($model, 'password'); ?>
		</div>
		<div class="row">
			<?php echo $form->labelEx($model, 'repeat_password'); ?>
			<?php echo $form->passwordField($model, 'repeat_password', array('size' => 60, 'maxlength' => 128)); ?>
			<?php echo $form->error($model, 'repeat_password'); ?>
		</div>
	<?php endif; ?>

	<div class="row">
		<?php echo $form->labelEx($model, 'role'); ?>
		<?php echo $form->textField($model, 'role', array('size' => 16, 'maxlength' => 16)); ?>
		<?php echo $form->error($model, 'role'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model, 'lastLogin'); ?>
		<?php echo $form->textField($model, 'lastLogin'); ?>
		<?php echo $form->error($model, 'lastLogin'); ?>
	</div>

	<div class="row buttons">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save'); ?>
	</div>

	<?php $this->endWidget(); ?>

</div><!-- form -->