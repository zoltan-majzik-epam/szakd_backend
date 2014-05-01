<?php

/**
 * Fájlok javításáért és adatbázisba töltéséért felelős
 * 
 * Használat:
 * <pre>
 * php {applicationDirectory}/protected/yiic.php insertfiles
 * </pre>
 * 
 * @version v1.0
 * @property array $_databaseColumnList Adattábla oszlopainak listája, sorrendben
 * @property array $_improveValues Azon mezők listája, amit lehet javítani
 * @property string $_dirNew Az új állomások által feltöltött mérési fájlok mappája
 * a script futási idejét.
 * 
 * @author  Majzik Zoltán <zoltan.majzik@quantislabs.com>
 */
class InsertFiles {

	protected $_databaseColumnList = array();
	protected $_improveValues;
	protected $_dirNew;
	protected $_sqlInsertQuerys;
	protected $_sqlInsertRows;

	/**
	 * MAGIC STRING
	 * 
	 * represents the parameter for the strtotime function, which will set the last
	 * measurement date timestamp relativ to the firstTime timestamp, or NOW, if set to now.
	 * 
	 * eg: now, +1 day, tomorrow, +2 days, +1 hour...
	 */
	const MEASUREMENT_DATA_LAST_TIME_STRING = "now";

	/**
	 * Beállítja a rendszeridőt UTC+0 -ra a futás idejéig, a mezők listáját és a mérési fájlok mappáit.
	 * 
	 * @version v1.0
	 * @access public
	 * 
	 * @return void
	 */
	public function init() {
		date_default_timezone_set('UTC');
		ini_set("memory_limit", "-1");
		$this->_sqlInsertQuerys = 0;
		$this->_sqlInsertRows = 0;

		$this->_databaseColumnList = array(
			'stationid', 'temperature', 'humidity',
			'leaf_wetness'
		);
		$this->_improveValues = array(
			'temperature',
			'humidity',
			'leaf_wetness',
		);
		$this->_dirNew = Yii::app()->params['dataDir'];
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
	public function InsertAll($force = null) {

		$delHours = 1;
		if ($force || date('H') == 11 || date('H') == '11') {
			$delHours = 48;
		}

		//delete the last hours
		$this->DelPreviousHours($delHours);

		$stations = $this->getStations();
		foreach ($stations as $station) {
			$this->insertStation($station);
			echo "\n\n";
		}

		if (defined("BENCHMARK") && BENCHMARK) {
			echo "Insert querys: " . $this->_sqlInsertQuerys;
			echo "\nInserted rows: " . $this->_sqlInsertRows . "\n\n";
		}
	}

	/**
	 * Inserts the data for the selected stations
	 * 
	 * @param string $stations Comma separated list of station IDs
	 */
	public function InsertOne($stations, $from = null, $to = null) {
		ini_set("memory_limit", "-1"); //because we don't want to run out of memory

		$stationIDs = explode(',', $stations);

		foreach ($stationIDs as $rawID) {
			$id = trim($rawID);
			$station = $this->getStationByID($id);
			if ($station === false) {
				Yii::log("Insert: invalid station with id $id", CLogger::LEVEL_WARNING);
			} else {
				$this->DelPreviousHours(1, $station->id);
				$this->insertStation($station);
			}
		}
	}

	/**
	 * Inserts the data for a single station.
	 * 
	 * @param array $station The station data, as returned by getStation() or getStations()
	 */
	private function insertStation($station, $from = null, $to = null) {
		ini_set("memory_limit", "-1"); //because we don't want to run out of memory
		foreach ($station as $key => $val) {
			$unwanted_array = array('Š' => 'S', 'š' => 's', 'Ž' => 'Z', 'ž' => 'z', 'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'A', 'Ç' => 'C', 'È' => 'E', 'É' => 'E',
				'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O', 'Ù' => 'U',
				'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ý' => 'Y', 'Þ' => 'B', 'ß' => 'Ss', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'a', 'ç' => 'c',
				'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ð' => 'o', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o',
				'ö' => 'o', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ý' => 'y', 'ý' => 'y', 'þ' => 'b', 'ÿ' => 'y');
			$station[$key] = strtr($val, $unwanted_array);
		}
		echo $station['name'] . "\n";
		echo str_repeat('-', strlen($station['name'])) . "\n";


		$lastTime = $this->insertMeasurementsFromFiles($station);
	}

	/**
	 * Delete the measurements and sensorerror for $hours.
	 * 
	 * @param int $hours
	 * @return type
	 */
	protected function DelPreviousHours($hours, $station = false) {

		if ($hours == 0)
			return;

		$time = time() - ($hours * 60 * 60);

		echo "Delete last " . ((time() - $time) / 60 / 60) . " hour measurements.\n";

		if (!$station) {
			$stations = Yii::app()->db->createCommand("SELECT id FROM {{station}} GROUP BY id")->queryColumn();
			$station_list = implode(",", $stations);
		} else {
			$station_list = $station;
		}
		//fast little bitch!! faster than the old one
		//use the full key for speed
		Yii::app()->db->createCommand('
			DELETE FROM {{measurements}} 
			WHERE stationid IN (' . $station_list . ') AND `date` >= :time')
			->execute(array(':time' => $time));
	}

	/**
	 * Insert the measurements from the measurements files for the station.
	 * 
	 * @param array $station the stations
	 * @return timestamp The last valid measurement timestamp
	 */
	private function insertMeasurementsFromFiles($station) {

		$path = $this->checkDirectory($station['id']);

		if ($path === FALSE) {
			echo "The stations folder doesn't exist.\n";
			return;
		}

		$files = $this->getFiles($path);
		if (empty($files)) {
			echo "Measurement file not found.\n";
			return;
		}

		$lastTime = null;
		foreach ($files as $fileName) {
			$fileInsertionTime = microtime(true);
			$filePath = $this->getInsertableFilePath($fileName, $path, $station);

			if ($filePath !== FALSE) {
				echo $fileName . " - ";
				$fileData = $this->getFileData($filePath);
				if ($fileData !== FALSE) {
					$firstTime = $this->getFirstTime($fileName, $station);
					$lastTime = $this->getLastTime($fileData, $fileName);

					$this->dataToMeasurements($station, $firstTime, $lastTime, $fileData);
					$fileData = null;
				}
				echo number_format((microtime(true) - $fileInsertionTime) * 1000, 2) . "ms\n";
			}
		}
		return $lastTime;
	}

	/**
	 * Creates the measurements data from the $data array.
	 * Inserts the data after repair.
	 * 
	 * 
	 * @param Station $station
	 * @param timestamp $firstTime the first measurement timestamp
	 * @param timestamp $lastTime the last measurement timestamp
	 * @param array $data The measurement data from file/forecast
	 */
	public function dataToMeasurements($station, $firstTime, $lastTime, &$data) {
		
		$emptyArray = $this->allocEmptyArray($firstTime, $lastTime);
		//var_dump($data);
		$dataArray = $this->mergeData($data, $emptyArray);
		
		$this->repairData($data, $dataArray, $firstTime);
		
		$this->insertRepairedData($station, $dataArray);
	}

	/**
	 * @version v1.0
	 * @access protected
	 * 
	 * @return array Állomások listája, ha jó a lekérdezés.
	 * @return boolean FALSE ha nem jó a lekérdezés, vagy nincs állomás az adatbázisban.
	 */
	protected function getStations() {
		$query = "
			SELECT 
				id, 
				name,
				IFNULL(last_upload, 0) AS last_upload
			FROM {{station}} s LEFT JOIN (
				SELECT 
					stationid,
					MAX(date) AS last_upload
					FROM tbl_measurements
					GROUP BY stationid
				) m ON (s.id = m.stationid)";

		return Yii::app()->db->createCommand($query)->queryAll();
	}

	/**
	 * Returns the station data for a single station
	 * 
	 * @param string $stationID
	 * @return array
	 */
	private function getStationByID($stationID) {
		$query = <<<SQL
			SELECT 
				id, 
				name, 
				lat,
				lon,
				IFNULL(last_upload, 0) AS last_upload
			FROM {{station}} s LEFT JOIN (
				SELECT 
					stationid,
					MAX(date) AS last_upload
					FROM tbl_measurements
					GROUP BY stationid
				) m ON (s.id = m.stationid)
			WHERE id = :id
SQL;
		return Yii::app()->db->createCommand($query)->queryRow(true, array(':id' => $stationID));
	}

	/**
	 * Megnézi, hogy az adott állomás mérési fájljai a régi, vagy az új helyen vannak-e, illetve hogy egyáltalán létezik-e.
	 * 
	 * @version v1.0
	 * @param int $stationID Állomás azonosító
	 * @access protected
	 * 
	 * @return array Ha az adott állomáshoz léteznek mérési fájlok, akkor a tartalmazó mappa elérési útját adja vissza.
	 * @return boolean Ha az adott állomáshoz nem léteznek mérési fájlok, akkor FALSE.
	 */
	protected function checkDirectory($stationID) {
		$dir = $this->_dirNew . DIRECTORY_SEPARATOR . $stationID . DIRECTORY_SEPARATOR . "measurements";

		if (is_dir($dir))
			return $dir;
		return FALSE;
	}

	/**
	 * A paraméterben kapott mappát bejárja, és visszaadja a mérési fájlok listáját.
	 * 
	 * @version v1.0
	 * @param string $path Bejárandó mappa, ahol a mérési fájlok találhatók.
	 * @access protected
	 * 
	 * @return array Mérési fájlok listája
	 */
	protected function getFiles($path) {
		$files = scandir($path, 0);
		$return = array();
		foreach ($files as $file) {
			if (strlen($file) > 5  && strpos($file, ".mes"))
				$return[] = $file;
		}
		return $return;
	}

	/**
	 * Megvizsgálja, hogy az adott fájl feldolgozható-e, ha igen, visszaadja a fájlt teljes elérési útvonalát, egyébként FALSE
	 * a visszatérési érték.
	 * 
	 * @version v1.0
	 * @param string $fileName Fájl neve (csak a neve, elérési út nélkül)
	 * @param string $directory Fájlt tartalmazó mappa teljes elérési útja
	 * @param array $station Állomás tömb, azonosítóval, telepítési és utolsó feltöltési idővel
	 * @access protected
	 * 
	 * @return string Fájl teljes elérési útja, ha az feldolgozható
	 * @return boolean FALSE ha az nem feldolgozható 
	 */
	protected function getInsertableFilePath($fileName, $directory, $station) {
		$fileDate = $this->getFileTimeFromFileName($fileName);
		if ($fileDate >= $station['last_upload'] - 24 * 60 * 60 && $fileDate < time())
			return $directory . DIRECTORY_SEPARATOR . $fileName;
		return FALSE;
	}

	/**
	 * A fájl nevét feldolgozza, és visszaadja annak időbélyegét, ami az aznap 0 óra 0 perc 0 másodperc
	 * ideje.
	 * 
	 * @version v1.0
	 * @param string $fileName Fájl neve (csak a neve, elérési út nélkül)
	 * @access protected
	 * 
	 * @return integer A fájl nevéből kapott éjféli időbélyeg.
	 */
	protected function getFileTimeFromFileName($fileName) {
		if (!preg_match('/^log/', $fileName))
			$fileDate = strtotime(substr($fileName, 0, -4));
		else
			$fileDate = strtotime(substr($fileName, 4, 10));
		return $fileDate;
	}

	/**
	 * Felolvas egy adott mérési fájlt, és a nyers adatokat adja vissza egy tömbként. Ha a tömb üres, akkor FALSE a visszatérési
	 * érték.
	 * 
	 * @version v1.0
	 * @param string $filePath Mérési fájl teljes elérési útvonala
	 * @access protected
	 * 
	 * @return array A fájl nyers mérési adatainak tömbje
	 * @return boolean FALSE ha a fájl üres
	 */
	protected function getFileData($filePath) {
		$return = array();
		$handle = @fopen($filePath, "r");
		if ($handle) {
			while (($buffer = fgets($handle, 4096)) !== false) {
				$rowData = $this->getRowData($buffer);
				if ($rowData !== FALSE) {
					$t = $rowData['time'];
					$return[$t]['temperature'] = $rowData['temperature'];
					$return[$t]['humidity'] = $rowData['humidity'];
					$return[$t]['leaf_wetness'] = $rowData['leaf_wetness'];
				} else {
					Yii::log("Error in file $filePath", CLogger::LEVEL_WARNING, 'insertfiles');
				}
			}
			if (!feof($handle)) {
				echo "Error: unexpected fgets() fail\n";
			}
			fclose($handle);
		}
		if (empty($return)) {
			return FALSE;
		}
		return $return;
	}

	/**
	 * Egy fájl sorát a vessző karakterek mentén felbontja tömbbé, majd ellenőrzi, hogy a sor "jó"-e. A sor jóságát
	 * az határozza meg, hogy a dátum értékek (év, hónap, nap, óra, perc) léteznek és helyesek. Ha a sor jó, akkor
	 * a többi értéket is betölti a tömbbe. Ha a fájl sorából egy vagy több adat hiányzik, akkor azt 0-val helyettesíti,
	 * ezáltal kiküszöbölhetőek a kompatibilitási problémák (a régebbi állomások mérési adataiban nincs benne például a
	 * board hőmérő).
	 * Ha a sor jó, akkor tömbként visszaadja, ellenkező esetben FALSE a visszatérési értéke.
	 * 
	 * @version v1.0
	 * @param string $sRow Egy sornyi mérés a fájlból
	 * @access protected
	 * 
	 * @return array Egy sornyi mérési adat átalakítva tömbbé
	 * @return boolean FALSE ha a sor nem jó
	 */
	protected function getRowData($sRow) {
		$ver = self::getRowVersion($sRow);
		if ($ver !== false) {
// The row is versioned
			$aRow = str_getcsv(trim($sRow), ";");
			$data = InsertFileAlgorithms::getDataForRow($ver, array_slice($aRow, 1));
		} else {
			echo "Invalid row version";
			return false;
		}

		if ($data === false) {
			return false;
		}

		if (!isset($data['time']))
			return false; // To maintain compatibility, time should always be set
		if (!isset($data['temperature']))
			$data['temperature'] = false;
		if (!isset($data['humidity']))
			$data['humidity'] = false;
		if ($data['humidity'] > 100.0)
			$data['humidity'] = 100.0;
		if (!isset($data['leaf_wetness']))
			$data['leaf_wetness'] = false;
		return $data;
	}

	/**
	 * Adott fájl éjféli idejét adja vissza, ha az nagyobb mint az állomás által feltöltött utolsó
	 * mérési adat időbélyege. Ellenkező esetben az utolsó feltöltés idejét adja vissza.
	 * Gyakorlatilag ez a metódus határozza meg, hogy mikortól kell feltölteni az adott fájl mérési adatait.
	 * 
	 * @version v1.0
	 * @param string $fileName Fájl neve (csak a neve, elérési út nélkül)
	 * @param array $station Állomás tömb, azonosítóval, telepítési és utolsó feltöltési idővel
	 * @access protected
	 * 
	 * @return integer Az adott mérési fájl "első" ideje, vagy az állomás utolsó feltöltési ideje
	 */
	protected function getFirstTime($fileName, $station) {
		$fileTime = 0;
		if ($fileName != null)
			$fileTime = $this->getFileTimeFromFileName($fileName);
		if ($fileTime > $station['last_upload'])
			return $fileTime - ($fileTime % 60);
		else
			return $station['last_upload'] - ($station['last_upload'] % 60);
	}

	/**
	 * Adott fájlban található utolső mérés idejét adja vissza akkor, ha a fájl mai napi.
	 * 
	 * @version v1.0
	 * @param array $fileData Nyers mérési adatok tömbje
	 * @access protected
	 * 
	 * @return integer Az adott mérési fájl "utolsó" ideje
	 */
	protected function getLastTime($fileData) {
		//var_dump($fileData);
		$keys = array_keys($fileData);
		return end($keys);
	}

	/**
	 * Lefoglal egy mérési tömböt úgy, hogy abból egy darab mérési sor sem hiányzik, viszont az értékek false-ok.
	 * Ezeket kell majd javítani.
	 * A tömb méretét a @see getFirstTime() és a @see getLastTime() metódusok visszatérési értéke határozza meg.
	 * Ezzel kiküszöbölhető az, ha a mérési fájlból hiányzik pár sor.
	 * 
	 * @version v1.0
	 * @param integer $firstTime @see getFirstTime()
	 * @param integer $lastTime @see getLastTime()
	 * @access protected
	 * 
	 * @return array Üres mérési tömb
	 */
	protected function allocEmptyArray($firstTime, $lastTime) {
		//echo $firstTime . " - " . $lastTime . "\n";
		$return = array();
		for ($t = $firstTime; $t <= $lastTime; $t += 60) {
			$return[$t]['temperature'] = false;
			$return[$t]['humidity'] = false;
			$return[$t]['leaf_wetness'] = false;
		}
		return $return;
	}

	/**
	 * A nyers mérési adatokat (hiányos sorokkal, hibás adatokkal) összefésüli az üres értékekkel rendelkező tömbbel.
	 * Ezzel gyakorlatilag a hiányzó sorokat false értékekkel helyettesíti.
	 * 
	 * @version v1.0
	 * @param array $fileArray @see getFileData()
	 * @param array $emptyArray @see allocEmptyArray()
	 * @access protected
	 * 
	 * @return array Mérési tömb, amiben a fájlban hiányzó sorok nem hiányoznak, viszont nincs értékük
	 */
	protected function mergeData($fileArray, $emptyArray) {
		foreach ($emptyArray as $time => $data) {
			//echo $time . "\n";
			if (array_key_exists($time, $fileArray)) {
				$emptyArray[$time] = $fileArray[$time];
			}
		}
		return $emptyArray;
	}

	/**
	 * Feladata, hogy a már hiánytalan sorokkal rendelkező mérési adat tömbben megszámolja, és kijavítsa a hibákat.
	 * A @see $_improveValues tömbben található mezőket javítja és számolja, plusz a hiányzó sorokat
	 * 
	 * @version v1.0
	 * @param array $fileData @see getFileData()
	 * @param type $dataArray @see mergeData()
	 * @param int $firstTime
	 * @access protected
	 * @author Zoltán Majzik <zoltan.majzik@quantislabs.com>
	 * 
	 * 
	 * @return array Hibák tömbje array('columnName1'=>array('rowTime1'=>true,'rowTime2'=>true),'columnName2'=>array('rowtime1'=>true))
	 */
	protected function repairData(&$fileData, &$dataArray, $firstTime) {
		$countImprovedValues = count($this->_improveValues);
		$return['sumrows'] = count($fileData);
		$return['fullrow'] = array();
		$return += array_combine($this->_improveValues, array_fill(0, count($this->_improveValues), array()));

		foreach ($this->_improveValues as $val)
			$return[$val] = 0;
		$return ["fullrow"] = 0;

		foreach ($dataArray as $data) {
			$corruptedColumns = $this->isCorruptedRow($data);
			if ($corruptedColumns !== FALSE) {
				if (count($corruptedColumns) == $countImprovedValues)
					$return['fullrow'] ++;
				foreach ($corruptedColumns as $cCol) {
					$return[$cCol] ++;
				}
			}
		}

// REPAIR ERRORS
		$beforeTime = 0;
		reset($dataArray);
		$firstTime = key($dataArray);
		$validDiff = false;
		$diff = 0.00; //ez kvázi a meredekség, amivel változni fognak 
		foreach ($this->_improveValues as $column) {
			$beforeTime = $firstTime;
			if (count($return[$column]) > 0) {
				$validDiff = false;
				$diff = 0.00;
				foreach ($dataArray as $time => $data) {
					if ($data[$column] !== false) {
						$beforeTime = $time;
						$validDiff = false;
					} else {
						if (!$validDiff) {
							$diff = $this->countDiff($beforeTime, $column, $dataArray);
							$validDiff = true;
						}

						$dataArray[$time][$column] = $dataArray[$beforeTime][$column] + $diff;
						$beforeTime = $time;
					}
				}
			}
		}
		return $return;
	}

	/**
	 * Megkeresei a meredekséget, amivel az értékek változni fognak
	 * a javítás során
	 * 
	 * @param int $time
	 * @param string $column
	 * @param array $dataArray
	 * 
	 * @access protected
	 * @author Majzik Zoltán <zoltan.majzik@quantoslabs.com>
	 * 
	 * @return float A meredekség, amivel változtatni kell az értéket.
	 */
	protected function countDiff($time, $column, &$dataArray) {
		$beforeTime = $time;
		$beforeValue = $dataArray[$time][$column];
		$diff = 0.00;
		$afterValue = 0.00;
		$time += 60;
		while (isset($dataArray[$time][$column])) {
			if ($dataArray[$time][$column] === false) {
//itt egy tovább ciklus, nézi a következő elemet
				$time += 60;
			} else {
//van valid after érték.
				$afterValue = $dataArray[$time][$column];
				if ($time - $beforeTime == 0)
					break;
				$diff = (($afterValue - $beforeValue) / ($time - $beforeTime)) * 60;
				break;
			}
		}
		return $diff;
	}

	/**
	 * Meghatározza, hogy az adott sorban van-e hibás adat, ha van akkor melyik oszlopok azok. Csak a @see $_iproveValues
	 * listában található mezőket vizsgálja.
	 * 
	 * @version v1.0
	 * @param array $dataRow Egy mérési adatsor tömb
	 * @access protected
	 * 
	 * @return array Hibás mezők listája, ha van benne hiba
	 * @return boolean FALSE ha nincs hiba
	 */
	protected function isCorruptedRow($dataRow) {
		$corruptedColumns = array();
		foreach ($this->_improveValues as $key) {
			if ($dataRow[$key] === false)
				$corruptedColumns[] = $key;
		}
		if (empty($corruptedColumns))
			return FALSE;
		else
			return $corruptedColumns;
	}

	/**
	 * Megvizsgálja, hogy adott időpontban, adott mező, adott mérési adatban megtalálható-e és az értéke nem nulla.
	 * Ebben az esetben ezen mező értékével tér vissza, egyébként FALSE-szal.
	 * 
	 * @version v1.0
	 * @param integer $time Keresett sor időbélyege
	 * @param string $columnName Keresett mező neve
	 * @param array $dataArray @see mergeData()
	 * @access protected
	 * 
	 * @return mixed Adott mező értéke
	 * @return boolean FALSE ha a mező nem létezik, vagy nincs értéke
	 */
	protected function repairedDataValue($time, $columnName, &$dataArray) {
		if (isset($dataArray[$time]) && isset($dataArray[$time][$columnName])) {
			return $dataArray[$time][$columnName];
		}
		return FALSE;
	}

	/**
	 * Betölti adatbázisba a javított mérési adattömböt, egy lekérdezésben.
	 * 
	 * @version v1.0
	 * @param array $station @see getStations()
	 * @param array $dataArray @see mergeData()
	 * @access protected
	 * @author Majzik Zoltán <zoltan.majzik@quantislabs.com>
	 * 
	 * @return void
	 */
	protected function insertRepairedData($station, &$dataArray) {
		$sid = $station['id'];
		$insert = false;
		$_query = 'INSERT IGNORE {{measurements}} (`date`,`' . implode('`,`', $this->_databaseColumnList) . '`) VALUES ';

		$packetSize = 1440;
		$packetIndex = 0;
		$count = 0;
		$packets = array();
		$packets[0] = "";

		foreach ($dataArray as $time => $data) {
//var_dump($data);
			$data['stationid'] = $sid;

			$packets[$packetIndex] .= "($time";
			foreach ($this->_databaseColumnList as $column) {
				$packets[$packetIndex] .= ",'" . $data[$column] . "'";
			}
			$packets[$packetIndex] .= '),';

			$count++;
			if ($count > $packetSize) {
				$packetIndex++;
				$count = 0;
				$packets[$packetIndex] = "";
			}

			$insert = true;

			if (defined("BENCHMARK") && BENCHMARK)
				$this->_sqlInsertRows++;
		}
		foreach ($packets as $packet) {
			if ($packet == "")
				continue;
			$query = $_query . rtrim($packet, ',');
			if ($insert) {
				if (defined("BENCHMARK") && BENCHMARK)
					$this->_sqlInsertQuerys++;

				Yii::app()->db->createCommand($query)->execute();
			}
		}
	}

	/**
	 * Ez a metódus az "akciók" előtt fut le. Ez állítja be a futtatás kezdetének az idejét.
	 * 
	 * @link http://www.yiiframework.com/doc/api/1.1/CConsoleCommand#beforeAction-detail CConsoleCommand::beforeAction()
	 */
	public function beforeAction($action, $params) {
		$this->_startTime = microtime(true);
		return parent::beforeAction($action, $params);
	}

	/**
	 * Parancs lefutása után kiírja, hogy mennyi ideig tartott.
	 * 
	 * @link http://www.yiiframework.com/doc/api/1.1/CConsoleCommand#afterAction-detail CConsoleCommand::afterAction()
	 */
	public function afterAction($action, $params, $exitCode = 0) {
		echo number_format((microtime(true) - $this->_startTime) * 1000, 2) . "ms";
		return parent::afterAction($action, $params, $exitCode);
	}

	/**
	 * Returns the version of the row, or FALSE if the row is unversioned or invalid
	 * 
	 * @param string $sRow
	 * @return mixed The version as int or FALSE
	 */
	public static function getRowVersion($sRow) {
		if (!is_string($sRow))
			return false;
		if (strlen($sRow) < 2)
			return false;
		if ($sRow[0] !== 'V')
			return false;

		$row = explode(',', $sRow, 2); // We need only the first column
		$version = substr($row[0], 1);
		return intval($version);
	}

}
