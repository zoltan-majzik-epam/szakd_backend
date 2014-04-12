<?php

Yii::import('application.models._base.BaseMeasurements');

class Measurements extends BaseMeasurements
{
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}
	
	/**
	 * Returns the last measurement timestamp for the station
	 * 
	 * @param int $stationid
	 */
	public static function getLastMeasurementTimestamp($stationid) {
		$sql = "SELECT date
			FROM {{measurements}}
			WHERE stationid = :stationid
			ORDER BY date DESC
			LIMIT 1";
		return Yii::app()->db->createCommand($sql)->queryScalar(array("stationid" => $stationid));		
	}
}