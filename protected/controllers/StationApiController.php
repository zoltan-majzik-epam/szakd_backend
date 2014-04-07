<?php

class StationApiController extends Controller {
	
	public function actionIndex() {
		$this->renderPartial("plain", array("answer" => "Plain text answer from StationApi."));
	}

	public function actionUploadFile($filename, $stationid, $token) {

		$answer = "ERROR";
		//validate
		if ($this->autStation($stationid, $token)) {
			//the stream
			$data = Yii::app()->getRequest()->getRawBody();
			//stop when the filename or data is empty
			if ($filename !== null && $data !== null) {
				//save the file
				$uploader = new DataUploader();
				$ret = $uploader->saveToFile($data, $stationid, $filename);
				if ($ret === true) { $answer = "OK"; }
			}
		}
		
		$this->renderPartial("plain", array("answer" => $answer));
	}
	
	
	/**
	 * Api for the stations to upload there GPS position
	 * 
	 * @param type $lat
	 * @param type $lng
	 * @param type $stationid
	 * @param type $token
	 */
	public function actionTellPosition($stationid, $lat, $lng, $token) {
		
		if (!$this->autStation($stationid, $token)) {
			$this->renderPartial("plain", array("answer" => "ERROR - Authentication failed."));
		}
		
		$answer = "ERROR";
		$pos = new Position("insert");
		$pos->lat = $lat;
		$pos->lng = $lng;
		$pos->date = time();
		$pos->station_id = $stationid;
		if ($pos->validate() && $pos->save()) {
			$answer = "OK";
		}
		else {
			$answer = " - Save failed.";
		}
		
		$this->renderPartial("plain", array("answer" => $answer));
		
		if ($answer === "OK")
			return true;
		else return false;
		
	}

	/**
	 * Checks that the id and token are valid
	 * 
	 * @param int $stationid
	 * @param string $token
	 * @return boolean
	 */
	private function autStation($stationid, $token) {
		/* @var Station $station */
		$station = Station::model()->findByPk($stationid);
		if ($station && $station->token == $token)
			return true;
		else
			return false;
	}
	
	public function actionRequestNewStation() {
		$sql = "SELECT MAX(id) + 1 FROM {{station}}";
		$id = Yii::app()->db->createCommand($sql)->queryScalar();
		$token = md5($id . time());
		
		$station = new Station();
		$station->id = $id;
		$station->token = $token;
		$station->name = "$id - Station";
		$station->save();
		$this->renderPartial("plain", array("answer" => $id . "," . $token));
	}

}
