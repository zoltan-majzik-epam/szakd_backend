<?php

class StationApiController extends Controller {

	public function actionIndex() {
		$this->renderPartial("plain", array("answer" => "Plain text answer from StationApi."));
	}

	public function actionUploadFile($filename, $stationid, $token) {

		$answer = "ERROR";
		//validate
		if ($this->authUpload($stationid, $token)) {
			//the stream
			//$data = file_get_contents('php://input');
			$data = Yii::app()->getRequest()->getRawBody();
			//stop when the filename or data is empty
			//if (strpos($filename, ".mes") > 0) echo $data;
			if ($filename !== null && $data !== null) {
				//save the file
				$uploader = new DataUploader();
				$ret = $uploader->saveToFile($data, $stationid, $filename);
				if ($ret === true) $answer = "OK";
			}
		}
		
		$this->renderPartial("plain", array("answer" => $answer));
	}

	/**
	 * Chacks that the id and token are valid
	 * 
	 * @param int $stationid
	 * @param string $token
	 * @return boolean
	 */
	private function authUpload($stationid, $token) {
		/* @var Station $station */
		$station = Station::model()->findByPk($stationid);
		if ($station && $station->token == $token)
			return true;
		else
			return false;
	}

}
