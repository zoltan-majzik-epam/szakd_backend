<?php

/**
 * Konzolos script, ami a mérési fájlok javításáért és adatbázisba töltéséért felelős
 * komopnenst futtatja.
 * 
 * @author  Majzik Zoltán <zoltan.majzik@quantislabs.com>
 */
class InsertFilesCommand extends CConsoleCommand {

	protected $insertFiles;
	
	/**
	 * Beállítja a rendszeridőt UTC+0 -ra
	 * 
	 * @version v1.0
	 * @access public
	 * 
	 * @return void
	 */
	public function init() {
		date_default_timezone_set('UTC');
		$this->insertFiles = new \InsertFiles();
		$this->insertFiles->init();
		
		parent::init();
	}

	/**
	 * Ez vezényeli le a mérési adatok beolvasását, javítását, betöltését adatbázisba.
	 * 
	 * 
	 * 
	 * @version v2.0
	 * @access public
	 * @author Zoltan Majzik <zoltan.majzik@quantislabs.com>
	 * 
	 * @return void
	 */
	public function actionIndex($force = null) {

		$this->insertFiles->InsertAll($force);
	}
	/**
	 * Inserts the data for the selected stations
	 * 
	 * @param string $stations Comma separated list of station IDs
	 * @param timestamp $from
	 * @param timestamp $to 
	 */
	public function actionInsertOne($stations, $from = null, $to = null) {
		$this->insertFiles->InsertOne($stations, $from, $to);
	}
	
	/**
	 * Delete the station's measurements from database.
	 * The station id is the id of the station, or "ALL" string. 
	 * 
	 * @param int|string $stationID
	 */
	public function actionClear($stationID = null) {
		if ($stationID == "ALL") {
			Yii::app()->db->createCommand('DELETE FROM {{measurements}}')->execute();
			Yii::app()->db->createCommand('TRUNCATE TABLE {{measurements}}')->execute();
		} elseif (is_numeric($stationID)) {
			Yii::app()->db->createCommand('DELETE FROM {{measurements}} WHERE `stationid`=:stationID')->execute(array(':stationID' => $stationID));
		}
		else
			echo $this->help();
	}
}
