<?php

class GraphsController extends Controller {

	/**
	 *
	 * @var int the selected station id
	 */
	public $station;

	/**
	 *
	 * @var int timestamp, query from date
	 */
	private $startTime;

	/**
	 *
	 * @var int timestamp, query end date
	 */
	private $endTime;

	public function init() {
		parent::init();
	}

	public function actionIndex() {
		$user = Yii::app()->user;

		$stationID = $user->getState('selected-station');

		$startTime = $user->getState('selected-time');
		$endTime = 0;

		switch ($user->getState('selected-interval')) {
			case 'daily':
				$endTime = strtotime('+1 day', $startTime);
				break;
			case 'weekly':
				$startTime = strtotime('-6 day', $startTime);
				$startTime = strtotime('Monday', $startTime);
				$endTime = strtotime('+1 week', $startTime);
				break;
			case 'monthly':
				$startTime = strtotime('first day of', $startTime);
				$endTime = strtotime('+1 month', $startTime);
				break;
			case 'yearly':
				$startTime = strtotime('first day of January', $startTime);
				$endTime = strtotime('+1 year', $startTime);
				break;
		}

		$this->render(
			'index');
	}

	/**
	 * Returns the selected from time, if there is a valid measurement data at that time
	 * 
	 * @return type
	 */
	public function getFromTime($station, $fromTime) {
		$fromTime = strtotime("midnight", $fromTime);
		$lastTime = Measurements::getLastMeasurementTimestamp($station);
		if ($lastTime && $lastTime < $fromTime) {

			$lastTime = strtotime("midnight", $lastTime);

			Yii::app()->user->setState('selected-time', $lastTime);
			Yii::app()->user->setFlash("nWarning", Yii::t("app", "No data available on {date}. Selected day changed to {newDate}.", array("{date}" => date("Y-m-d", $fromTime), "{newDate}" => date("Y-m-d", $lastTime))
				)
			);
			return $lastTime;
		} else {
			return $fromTime;
		}
	}

	public function beforeAction($action) {
		/* @var $cs CClientScript */
		$cs = Yii::app()->clientScript;
		$cs->registerPackage('graphs');

		/* @var $user WebUser */
		$user = Yii::app()->user;
		if (isset($_GET['station'])) {
			if (in_array($_GET['station'], $user->getStations()))
				$user->setState('selected-station', $_GET['station']);
			else
				Yii::app()->user->setFlash('nFailure', Yii::t('app', "You can't select this station!"));
		}
		$station = $user->getState('selected-station', false);
		if (!$station) {
			$stations = $user->getStations(true);
			if (!empty($stations)) {
				$user->setState('selected-station', $stations[0]);
			}
			else {
				$this->redirect($user->returnUrl);
			}
		}

		
		if (isset($_GET['time'])) {
			if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['time'])) {
				$time = strtotime($_GET['time'] . ' 00:00:00');
			} else {
				$time = $_GET['time'];
			}
			$fromTime = $this->getFromTime($station, $time);
			$user->setState('selected-time', $fromTime);
		}

		
		
		if (isset($_GET['interval'])) {
			$user->setState('selected-interval', $_GET['interval']);
		}
		if (!$user->getState('selected-interval', false)) {
			$user->setState('selected-interval', 'daily');
		}
		if ($user->getState('selected-interval') === "last") {
			$user->setState('selected-interval', 'daily');
			$user->setState('selected-time', time());
		}
		
		//echo Yii::app()->user->getState('selected-time'); exit();
		/**
		 * Check te selected time. If it's after the last valid measurements, then update it
		 */
		$user->setState('selected-time', $this->getFromTime($station, Yii::app()->user->getState('selected-time', time())));

		$cs->registerScript(
			'setDatePicker','
				$("#dateSelector").datepicker("setDate", "' . date('Y-m-d', Yii::app()->user->getState('selected-time')) . '");
			', CClientScript::POS_READY);

		$this->station = Station::model()->findByPk($user->getState('selected-station'));
		$this->pageTitle .= ' - ' . $this->station->name;

		return parent::beforeAction($action);
	}

	/**
	 * @return array a list of filter configurations.
	 */
	public function filters() {
		return array_merge(parent::filters(), array(
			'accessControl',
		));
	}

	
	/**
	 * @return array list of access rules.
	 */
	/*
	public function accessRules() {
		return array_merge(parent::accessRules(), array(
			array(
				'deny',
				'actions' => array('index'),
				'users' => array('?'),
			),
			array(
				'allow',
				'actions' => array('index'),
				'roles' => array('Graphs'),
			),
			array(
				'deny',
				'actions' => array('index'),
				'users' => array('*'),
			),
		));
	}*/

}

?>
