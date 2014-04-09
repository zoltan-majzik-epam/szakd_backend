<?php

/**
 * Contains the algorithms for parsing the measurement data rows.
 */
/* private static */ class InsertFileAlgorithms {
	
	
	/**
	 * Converts kOhm values to the scalar, 0-15 values requried by the algorithms.
	 * 
	 * @param float $kohm
	 * @return int
	 */
	public static function kOhmToScalar($kohm) {
		/*
		 * The value, from the leaf is wet.
		 * 0, 10000, 1500, 700, 440, 300, 200, 160, 120, 90, 60, 50, 40, 30, 20, 10 kOhm
		 * 0,     1,    2,   3,   4,   5,   6,   7,   8,  9, 10, 11, 12, 13, 14, 15
		 * 
		 * http://agromet-cost.bo.ibimet.cnr.it/fileadmin/cost718/repository/report_siversten.pdf
		 */

		if ($kohm > 10000)
			return 0;
		if ($kohm > 1500)
			return 1;
		if ($kohm > 700)
			return 2;
		if ($kohm > 440)
			return 3;
		if ($kohm > 300)
			return 4;
		if ($kohm > 200)
			return 5;
		if ($kohm > 160)
			return 6;
		if ($kohm > 120)
			return 7;
		if ($kohm > 90)
			return 8;
		if ($kohm > 60)
			return 9;
		if ($kohm > 50)
			return 10;
		if ($kohm > 40)
			return 11;
		if ($kohm > 30)
			return 12;
		if ($kohm > 20)
			return 13;
		if ($kohm > 10)
			return 14;
		return 15;
	}

	/**
	 * Returns the parsed measurement data for the specified row and version.
	 * 
	 * @param int $rowVersion Row data version
	 * @param string[] $rowData The unparsed measurement row
	 * @return mixed An associative array containing the data of the measurement, or false on error.
	 */
	public static function getDataForRow($rowVersion, $rowData) {
		$className = get_class();
		$methodBaseName = 'algorithmV';
		try {
			$rMethod = new ReflectionMethod($className, $methodBaseName . $rowVersion);
			$requiredParams = $rMethod->getNumberOfParameters();
			$suppliedParams = count($rowData);
			if ($requiredParams !== $suppliedParams) {
				Yii::log("Invalid row, required param count is $requiredParams, $suppliedParams was given", CLogger::LEVEL_WARNING, 'insertfiles');
			} else {
				return $rMethod->invokeArgs(null, $rowData);
			}
		} catch (ReflectionException $ex) {
			Yii::log("Error for algorithm version $rowVersion: " . $ex->getMessage(), CLogger::LEVEL_WARNING, 'insertfiles');
		}
		return false;
	}

	/**
	 * 
	 * @param int $timestamp Timestamp
	 * @param float $leafResistance Leaf sensor resistance (kΩ)
	 * @param float $temperature Temperature (°C), or '?' if not available.
	 * @param float $humidity Relative humidity (%), or '?' if not available.
	 * @return mixed An associative array with the data
	 */
	public static function algorithmV1($timestamp, $leafResistance, $temperature, $humidity) {
		if (!intval($timestamp))
			return false;
		$temperatureValue = false;
		if (is_numeric($temperature)) {
			$temperatureValue = floatval($temperature);
			if ($temperatureValue <= -40 || $temperatureValue >= 50) {
				$temperatureValue = false;
			}
		}

		$leafWetnessValue = InsertFileAlgorithms::kOhmToScalar($leafResistance);

		$realTimestamp = $timestamp - ($timestamp % 60);
		return array(
			'time' => $realTimestamp,
			'temperature' => $temperatureValue,
			'humidity' => is_numeric($humidity) ? floatval($humidity) : false,
			'leaf_wetness' => is_numeric($leafWetnessValue) ? intval($leafWetnessValue) : 0
		);
	}

	/*
	 * To extend the class with new Algorithms:
	 *   Create a new method with the name algorithmVxxxx($arg1, $arg2, ... $argN), where
	 *     xxxx is the algorithm version
	 *     $arg1, $arg2... $argN are the values in the measurement row (all, except the version number) as string.
	 *   The return value is an assocative array, where the keys are the database columns.
	 *   If a measurement value is invalid (i.e. temp. sensor was unplugged and column is null), do not include it in the returned array.
	 *   If the whole row is invalid, return false.
	 * 
	 *   You can extend a prevoius algorithm by calling it directly and adding the additional columns to the array, e.g.
	 *   public static function algorithmV43($arg1, $arg2, $arg3) {
	 *     $data = self::algorithmV42($arg1, $arg2);
	 *     if ($data === false) return false;
	 *     $data['data3'] = floatval($arg3);
	 *     return $data;
	 *   }
	 * 
	 *   Don't forget to document the measurement row!
	 */
}
