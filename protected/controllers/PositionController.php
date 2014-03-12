<?php

class PositionController extends GxController {

	public function actionIndex() {
		$model = new Position('search');
		$model->unsetAttributes();

		if (isset($_GET['Position']))
			$model->setAttributes($_GET['Position']);

		$this->render('admin', array(
			'model' => $model,
		));
	}

}