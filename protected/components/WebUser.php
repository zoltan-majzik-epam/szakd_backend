<?php

//namespace application\components;

use \Yii;

/**
 * Provides extended functionality to Yii::app()->user
 * 
 * @property-read string[] $stations The user's stations' IDs
 * @property-read \Users $model The user's associated model object
 */
class WebUser extends \CWebUser {

	/**
	 * Returns the user's stations' IDs
	 * 
	 * @return string[]
	 */
	public function getStations($forceUpdate = false) {
		
		if ($this->isGuest)
			return array();
		
		$station_list = $this->getState('stationids', false);
		//var_dump($station_list);
		if ($station_list === false || $forceUpdate) {
			// If it's not in the session cache, load it from DB
			// FIXME: The cache may become invalid, if another user adds/removes a station.
			$stations = array();
			$sql = 'SELECT id FROM {{station}}';
			$station_list = Yii::app()->db->createCommand($sql)->queryColumn();

			$this->setState('stationids', $station_list);
		}
		return $station_list;
	}


	public function checkAccess($operation, $params = array(), $allowCaching = true) {
		//echo "<span style='background: red; border:1px solid black;'>aut</span>";
		if ($allowCaching && !$this->getIsGuest() && isset(Yii::app()->session['access'][$operation]) && count($params) === 0) {
			return Yii::app()->session['access'][$operation];
		}
		$checkAccess = Yii::app()->getAuthManager()->checkAccess($operation, $this->getId(), $params);
		if ($allowCaching && !$this->getIsGuest() && count($params) === 0) {
			$access = isset(Yii::app()->session['access']) ? Yii::app()->session['access'] : array();
			$access[$operation] = $checkAccess;
			Yii::app()->session['access'] = $access;
		}
		return $checkAccess;
	}

	function getModel() {
		if ($this->isGuest)
			return null;
		return \Users::model()->findByPk($this->id);
	}

	/**
	 * Regenerates the list of the user's stations.
	 * Should be called if a station is created, deleted or the permissions are changed.
	 */
	public function flushStations() {
		$this->setState('stationids', null);
	}

	public function setState($key, $value, $defaultValue = null) {
		parent::setState($key, $value, $defaultValue);
		/*if ($key === "selected-station") {
			//echo "setState $key $value";
		}*/
	}
}
