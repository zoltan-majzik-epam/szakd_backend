<?php

class PositionController extends GxController {

	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
			'postOnly + delete', // we only allow deletion via POST request
		);
	}
	
	public function actionIndex() {
		$model = new Position('search');
		$model->unsetAttributes();

		if (isset($_GET['Position']))
			$model->setAttributes($_GET['Position']);

		$this->render('admin', array(
			'model' => $model,
		));
	}
	
	public function accessRules() {
		return array(
			array('allow', // allow admin user to perform 'admin' and 'delete' actions
				'actions'=>array('index'),
				'users'=>array('@'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

}