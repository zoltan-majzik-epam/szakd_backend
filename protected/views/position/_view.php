<div class="view">

	<?php echo GxHtml::encode($data->getAttributeLabel('id')); ?>:
	<?php echo GxHtml::link(GxHtml::encode($data->id), array('view', 'id' => $data->id)); ?>
	<br />

	<?php echo GxHtml::encode($data->getAttributeLabel('station_id')); ?>:
		<?php echo GxHtml::encode(GxHtml::valueEx($data->station)); ?>
	<br />
	<?php echo GxHtml::encode($data->getAttributeLabel('date')); ?>:
	<?php echo GxHtml::encode($data->date); ?>
	<br />
	<?php echo GxHtml::encode($data->getAttributeLabel('lat')); ?>:
	<?php echo GxHtml::encode($data->lat); ?>
	<br />
	<?php echo GxHtml::encode($data->getAttributeLabel('lng')); ?>:
	<?php echo GxHtml::encode($data->lng); ?>
	<br />

</div>