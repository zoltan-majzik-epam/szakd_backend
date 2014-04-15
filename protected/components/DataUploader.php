<?php

class DataUploader {
	
	const LOGS_DIR = "logs";
	const MEASUREMENTS_DIR = "logs";
	
	private static $_VALID_TYPE = array(
		"mes" => "measurements", 
		"log" => "logs");
	
	public function saveToFile($data, $stationid, $filename) {
		
		$path = $this->makePath($stationid, $filename);
		
		if ($path === false) return false;
		
		$path .= DIRECTORY_SEPARATOR . $filename;
		//echo $path; return false;
		
		return (file_put_contents($path, $data, FILE_APPEND) == strlen($data)) ? true : false;
	}
	
	
	public function makePath($stationid, $filename) {
		//directorys
		$path = Yii::app()->params["dataDir"] . DIRECTORY_SEPARATOR . $stationid;
		
		//test stations dir
		if (!$this->createDirectory($path)) return false;
		
		//get the file extension
		$ext = explode(".", $filename);
		$type = $ext[1];
		//check file extension
		if (!array_key_exists($type, DataUploader::$_VALID_TYPE)) return false;
			
		//create path to type
		$path .= DIRECTORY_SEPARATOR . DataUploader::$_VALID_TYPE[$type];
		//test type dir
		if (!$this->createDirectory($path)) return false;
		
		return $path;
	}

	
	/**
	 * Creates the directory if dosn't exist
	 * 
	 * @param path to dir $path
	 * @return boolean
	 */
	public function createDirectory($path) {
		//test path
		if (file_exists($path)) {
			return true;
		}
		return mkdir($path, 0755, true);
	}
	
}


