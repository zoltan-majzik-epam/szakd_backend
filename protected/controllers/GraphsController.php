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

		$user = Yii::app()->user;

		$this->trackingParams = array(
			'stationID' => $user->getState('selected-station'),
			'time' => strftime('%Y-%m-%d %H:%M:%d', $user->getState('selected-time')),
			'interval' => $user->getState('selected-interval'),
		);
	}

	public function actionIndex() {
		$user = Yii::app()->user;

		$stationID = $user->getState('selected-station');
		//region check for the station
		$user->checkValidRegion($stationID, $_GET["r"]);
		//$location = Stations::model()->findByPk($stationID)->tblLocations[0];

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
			case 'custom':
				$endTime = $user->getState('selected-time-to');
				break;
		}


		/*
		  $printDates = function($events) {
		  foreach ($events as $e) {
		  print $e->time . '<br>';
		  }
		  };
		 */
		/* @var $location Location */
//		$events = array_filter($location->tblEvents, function(Event $e) use($startTime, $endTime) {
//			$eventTime = strtotime($e->time);
//			if ($startTime <= $eventTime && $eventTime <= $endTime) {
//				return true;
//			} else if ($e->sprayevent != null) {
//				$eventEndTime = strtotime($e->sprayevent->end_time);
//				if ($startTime <= $eventEndTime && $eventEndTime <= $endTime)
//					return true;
//				else {
//					/* @var $chem SprayeventChemical */
//					foreach ($e->sprayevent->sprayeventChemicals as $chem) {
//						if ($startTime <= $eventTime + ($chem->duration * 60 * 60 * 24) && $endTime > $eventTime)
//							return true;
//					}
//				}
//			}
//			return false;
//		});
//		usort($events, function(Event $a, Event $b) use ($startTime) {
//			$aTime = strtotime($a->time);
//			$bTime = strtotime($b->time);
//			if ($a->sprayevent === null && $b->sprayevent !== null && $bTime < $startTime) {
//				return 1;
//			} elseif ($a->sprayevent !== null && $b->sprayevent === null && $aTime < $startTime) {
//				return -1;
//			} else {
//				return $aTime - $bTime;
//			}
//		});


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
		$cs->registerPackage('highcharts');
		$cs->registerScriptFile(Yii::app()->getBaseUrl() . '/js/smartvineyard/graphs/functions.js');
		$cs->registerScriptFile(Yii::app()->getBaseUrl() . '/js/smartvineyard/graphs/index.js');
		$cs->registerScriptFile(Yii::app()->getBaseUrl() . '/js/smartvineyard/graphs/highcharts/highcharts-tooltip-fix.js');
		$cs->registerPackage('jquery.forms');

		$cs->registerPackage('jquery.noty');

		//$cs->registerScriptFile('js/aquincum/files/functions.js');

		/* @var $user UserIdentity */
		$user = Yii::app()->user;
		if (isset($_GET['station'])) {
			if (in_array($_GET['station'], $user->stations))
				$user->setState('selected-station', $_GET['station']);
			else
				Yii::app()->user->setFlash('nFailure', Yii::t('app', "You can't select this station!"));
		}
		$station = $user->getState('selected-station', false);
		if (!$station) {
			if (!empty($user->stations))
				$user->setState('selected-station', $user->stations[0]);
			else
				$this->redirect($user->returnUrl);
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

		if (isset($_GET['totime'])) {
			if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['totime']))
				$user->setState('selected-time-to', strtotime($_GET['totime'] . ' 23:59:00'));
			else
				$user->setState('selected-time-to', $_GET['totime']);
		}

		/**
		 * Check te selected time. If it's after the last valid measurements, then update it
		 */
		$user->setState('selected-time', $this->getFromTime($station, Yii::app()->user->getState('selected-time', time())));

		if (isset($_GET['interval'])) {
			$user->setState('selected-interval', $_GET['interval']);
		}
		if (!$user->getState('selected-interval', false)) {
			$user->setState('selected-interval', 'daily');
		}

		if (isset($_GET['hideProtected'])) {
			($_GET['hideProtected'] == "true") ? $state = true : $state = false;
			$user->setState('hideProtected', $state);
		}
		if ($user->getState('hideProtected', -1) == -1) {
			$user->setState('hideProtected', true);
		}

		$cs->registerScript(
			'setDatePicker', '$("document").ready(function(){
					$(".inlinedate#graphs-inlinedate").datepicker( "setDate", "' . date('Y-m-d', Yii::app()->user->getState('selected-time')) . '" );
					$(".inlinedate#graphs-inlinedate-to").datepicker( "setDate", "' . date('Y-m-d', Yii::app()->user->getState('selected-time-to')) . '" );
					$("#hideProtectedCheckbox").change(function() {
						window.location.href = "index.php?r=graphs/index&hideProtected="+$("#hideProtectedCheckbox").is(":checked");
					});
					$("input,select").uniform();
				
				});', CClientScript::POS_END);

		$cs->registerScriptFile(Yii::app()->getBaseUrl() . '/js/smartvineyard/regional_datepicker/' . Yii::app()->language . '.js');

		$this->station = Stations::model()->findByPk($user->getState('selected-station'));
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
	}

}

?>
