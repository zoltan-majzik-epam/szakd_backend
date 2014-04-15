<?php

class GraphsApiController extends Controller {

	// TODO: remove columnsettings
	private $seriesSettings;
	private $graphs = array(
		'weather' => array('temperature', 'humidity', 'leaf_wetness'),
	);

	/**
	 * Initializes this class.
	 * 
	 * Sets the series parameters
	 */
	public function init() {
		parent::init();

		$measurementQueryHelper = function($seriesSettings, $userSettings, &$sqlParams) {
			return $this->measurementQuery($seriesSettings, $userSettings, $sqlParams);
		};

		if (isset($_GET['sid']))
			Yii::app()->user->setState('selected-station', intval($_GET['sid']));

		$params = array('sid' => Yii::app()->user->getState('selected-station'));

		$this->seriesSettings = array(
			/*
			 * Each array value is one type of row in the graphs. The following
			 * properties are recognized:
			 * 
			 * access (required): bool
			 *   Checks if the user has enough privileges to view the data row.
			 * color (required): string
			 *   the HTML color of the line.
			 * dashStyle: string
			 *   Probably a display setting for the HighCharts API.
			 *   FIXME: does no seem to work
			 * dbColumn (required): string
			 *   Identifies the data row. If dbSettings is not present, it
			 *   it specifies the column of the data in the DB.
			 * dbSettings: array
			 *   name: string
			 *     Must be equal to dbColumn.
			 *   singular:
			 *     The expression to use in the query when a single data point is needed
			 *   plural:
			 *     The expression to use in the query when multiple points are returned
			 *     as one point (e.g. with a grouping function).
			 * dbSelectProtected: array
			 *   It has the same structure as dbSettings, but is applied only if
			 *   the graph shows a disease and the "Hide protected" option is set.
			 * disease: string
			 *   If the graph is a disease, it sets the disease type to
			 *   use when displaying the protection.
			 * enableMouseTracking: bool
			 *   Enables or disables the mouse "popup" for the graph. Default is true.
			 * grouping: string
			 *   If dbSettings is not set, it specifies the grouping function
			 *   when multiple points are returned as one data point.
			 * marker: Callable
			 *   It must the following signature:
			 *     string function($dataPoint)
			 *   It must a return a string that will be used as a marker (image on
			 *   the data line), or null if a marker is not required for the data point.
			 * max (required): int or null
			 *   The max. value of the y axis. Is null, it is calculated automatically.
			 * min (required): int or null
			 *   The min. value of the y axis. Is null, it is calculated automatically.
			 * multiplier (required): int
			 *   All the data points will be multiplied with this value.
			 * postProcess: callable
			 *   Runs a function on all the data points.
			 *   The function must have a following signature:
			 *     void function(&$dataPoint, &$date)
			 *   As the points are accepted by reference, changes can be made to them too.
			 * query (required): string or Callable
			 *   If it is a string, it is interpreted as the SQL query that returns the data.
			 *     It must have three params: :sid (station id), :from (start date) and :to (end date)
			 *   If it is a Callable it must have the following signature:
			 *     mixed function($seriesSettings, $userSettings, &$sqlParams).
			 *     If this function returns a CDbCommand, it is run with the params
			 *     specified in $sqlParams.
			 *     Else the returned data is used as is.
			 * text (required): string
			 *   The title of the data row.
			 * type (required): string
			 *   The type of the data row, as specified in the HighCharts API
			 * unit (required): string
			 *   The unit of the data row
			 */

			// WEATHER //

			'temperature' => array(
				'access' => true,
				'color' => '#F00A0A',
				'dbColumn' => 'temperature',
				'grouping' => 'AVG',
				'max' => null,
				'min' => null,
				'multiplier' => 1,
				'query' => $measurementQueryHelper,
				'text' => Yii::t('app', 'Temperature'),
				'type' => 'line',
				'unit' => '°C',
				"lineWidth" => 10
			),
			'humidity' => array(
				'access' => true,
				'color' => '#B606C9',
				'dbColumn' => 'humidity',
				'grouping' => 'AVG',
				'max' => 120,
				'min' => 0,
				'multiplier' => 1,
				'query' => $measurementQueryHelper,
				'text' => Yii::t('app', 'Humidity'),
				'type' => 'line',
				'unit' => '%',
			),
			'leaf_wetness' => array(
				'access' => true,
				'color' => '#24A800',
				'dbColumn' => 'leaf_wetness',
				'grouping' => 'MAX',
				'max' => 20,
				'min' => 0,
				'multiplier' => 1,
				'query' => $measurementQueryHelper,
				'text' => Yii::t('app', 'Leaf Wetness'),
				'type' => 'line',
				'unit' => null,
			),
		);
	}

	/**
	 * Returns the data for the selected graph in JSON format.
	 */
	public function actionGetseries($graph) {
		$data = $this->getSeries($graph);
		$this->renderPartial('getseries', array('data' => $data));
	}
	
	/**
	 * Returns the data to the graph in arrays
	 * 
	 * @param string $graphs
	 * @return array
	 * @throws CException
	 */
	public function getSeries($graph) {
		$userSettings = array(
			'sid' => !isset($_GET['sid']) ? intval(Yii::app()->user->getState('selected-station')) : intval($_GET['sid']),
			'station' => !isset($_GET['sid']) ? intval(Yii::app()->user->getState('selected-station')) : intval($_GET['sid']),
			'interval' => Yii::app()->user->getState('selected-interval'),
			'time' => Yii::app()->user->getState('selected-time', false) ? intval(Yii::app()->user->getState('selected-time')) : time(),
		);


		// Intervallum alapján kezdő dátum és vége timestamp beállítása
		$userSettings = $this->setFromTo($userSettings);
		// Állomásnév beállítása
		$userSettings = $this->setStationName($userSettings);


		if (isset($graph) && array_key_exists($graph, $this->graphs)) {
			$data = array();
			$data = $this->setGraphTitle($userSettings, $graph);
			$userSettings = $this->setGraphTitle($userSettings, $graph);
			// Collect the series that belong to the graph
			$index = 0;
			foreach ($this->graphs[$graph] as $seriesID) {
				$settings = $this->seriesSettings[$seriesID];
				if (!$settings['access'])
					continue;

				$this->getSeriesData($settings, $userSettings, $index++, /* inout */ $data);
				$this->setSubtitle(/* inout */ $data);
			}


			return $data;
		} else {
			throw new CException('Invalid graph type');
		}
	}

	/**
	 * Returns the data for one series.
	 * 
	 * @param type $seriesSettings
	 * @param type $userSettings
	 * @param type $seriesIndex
	 * @param array &$name The output is added to this array.
	 * @throws CException
	 */
	public function getSeriesData($seriesSettings, $userSettings, $seriesIndex, /* inout */ &$data) {
		// Default SQL params
		$sqlParams = array(
			':sid' => $userSettings['station'],
			':from' => $userSettings['from'],
			':to' => $userSettings['to']);

		// If query is string, it is an SQL query
		if (is_string($seriesSettings['query'])) {
			$queryStr = $seriesSettings['query'];
			$query = Yii::app()->db->createCommand($queryStr);
			$rawData = $query->queryAll(true, $sqlParams);
		}
		// If it is Callable, it a function returning...
		elseif (is_callable($seriesSettings['query'])) {
			$query = $this->measurementQuery($seriesSettings, $userSettings, /* inout */ $sqlParams);
			// ...an SQL command, run it.
			if ($query instanceof CDbCommand) {
				$rawData = $query->queryAll(true, $sqlParams);
			}
			// ...the data to display.
			else {
				$rawData = $query;
			}
		} else
			throw new CException('Invalid query');

		// Common settings
		$data['yaxis'][$seriesIndex]['title']['text'] = $seriesSettings['text'];
		$data['yaxis'][$seriesIndex]['title']['text'] .= $seriesSettings['unit'] !== null ? ' [' . $seriesSettings['unit'] . ']' : '';
		$data['yaxis'][$seriesIndex]['title']['style']['color'] = $seriesSettings['color'];
		$data['yaxis'][$seriesIndex]['showEmpty'] = false;
		$data['yaxis'][$seriesIndex]['min'] = $seriesSettings['min'];
		$data['yaxis'][$seriesIndex]['max'] = $seriesSettings['max'];
		$data['yaxis'][$seriesIndex]['opposite'] = $seriesIndex % 2 ? true : false;
		if (isset($seriesSettings['tickInterval']))
			$data['yaxis'][$seriesIndex]['tickInterval'] = $seriesSettings['tickInterval'];
		if (isset($seriesSettings['plotBands']))
			$data['yaxis'][$seriesIndex]['plotBands'] = $seriesSettings['plotBands'];

		$data['series'][$seriesIndex]['type'] = $seriesSettings['type'];
		$data['series'][$seriesIndex]['yAxis'] = $seriesIndex;
		$data['series'][$seriesIndex]['name'] = $seriesSettings['text'];
		$data['series'][$seriesIndex]['color'] = $seriesSettings['color'];
		$data['series'][$seriesIndex]['enableMouseTracking'] = isset($seriesSettings['enableMouseTracking']) ? $seriesSettings['enableMouseTracking'] : true;

		$data['series'][$seriesIndex]['data'] = array();
		//var_dump($rawData);
		foreach ($rawData as $row) {

			$dataPoint = $row[$seriesSettings['dbColumn']];
			// If there is a postporcess function, run it on the data point.
			if (isset($seriesSettings['postProcess'])) {
				$seriesSettings['postProcess'](/* ref */ $dataPoint, /* ref */ $row['date']);
			}

			$data['series'][$seriesIndex]['data'][] = array(
				$row['date'] * 1000,
				floatval($dataPoint) * $seriesSettings['multiplier'],
			);
		}
	}

	/**
	 * Returns the date component of the final data point
	 * 
	 * @param type $row
	 * @return float
	 */
	function dateOf($row) {
		if (is_object($row)) {
			return $row->x;
		} else
			return $row[0];
	}

	/**
	 * Sets the subtitle (the from and to dates) of the graph.
	 * 
	 * @param array $data
	 */
	function setSubtitle(&$data) {
		// Find min and max date
		$minDate = INF;
		foreach ($data['series'] as $series) {
			if (isset($series['data'][0])) {
				$date = $this->dateOf($series['data'][0]);
				if ($minDate > $date)
					$minDate = $date;
			}
		}
		$maxDate = 0;
		foreach ($data['series'] as $series) {

			if (isset($series['data'][0])) {
				$date = $this->dateOf($series['data'][count($series['data']) - 1]);
				if ($maxDate < $date)
					$maxDate = $date;
			}
		}
		$data['subtitle'] = date('Y.m.d H:i', $minDate / 1000) . ' - ' . date('Y.m.d H:i', $maxDate / 1000);
	}

	/**
	 * Returns the SQL command for measurement data.
	 * 
	 * @param type $seriesSettings
	 * @param type $userSettings
	 * @return CDbCommand
	 */
	private function measurementQuery($seriesSettings, $userSettings, &$sqlParams) {

		$allColumns = array(
			$this->seriesSettings['temperature'],
			$this->seriesSettings['humidity'],
			$this->seriesSettings['leaf_wetness'],
		);

		$select = $this->setQuerySelect($userSettings, $allColumns);
		$grouping = $this->setGrouping($userSettings);
		$measurements = Yii::app()->db->createCommand()
			->select($select)
			->from('{{measurements}}')
			->group($grouping)
			->where('stationid=:sid AND (date BETWEEN :from AND :to)')
			->queryAll(true, $sqlParams);
		return $measurements;
	}

	/**
	 * Sets the "real" from and to dates, to respect month and year boundaries.
	 * 
	 * @param type $data
	 * @return string
	 */
	private function setFromTo($data) {

		$interval = $data['interval'];
		$time = $data['time']; // Ez mindíg a kiválasztott nap 00:00:00 időpontja

		
		if ($interval == 'daily') {
			$data['from'] = $time;
			$data['to'] = strtotime('+1 day -1 minute', $time);
		} elseif ($interval == 'weekly') {
			// Ha a kiválasztott nap nem hétfő, akkor a legutóbbi hétfő idejét adja meg, egyébként a kiválasztott nap idejét
			$data['from'] = date('D', $time) !== 'Mon' ? strtotime('last Monday', $time) : $time;
			// Ha a kiválasztott nap nem vasárnap, akkor a következő vasárnap idejét adja meg, egyébként a kiválasztott napot
			$data['to'] = date('D', $time) !== 'Sun' ? strtotime('next Sunday 23:59:00', $time) : strtotime('+1 day -1 minute', $time);
		} elseif ($interval == 'monthly') {
			$data['from'] = strtotime('first day of this month', $time);
			$data['to'] = strtotime('last day of this month 23:59:00', $time);
		} elseif ($interval == 'yearly') {
			$data['from'] = strtotime('1/1 this year', $time);
			$data['to'] = strtotime('1/1 next year -1 minute', $time);
		} elseif ($interval == 'lastday') {
			$lastM = Yii::app()->db->createCommand('SELECT MAX(`date`) FROM {{measurements}} WHERE `stationid`=:sid')->queryScalar(array(':sid' => $data['sid']));
			$data['from'] = strtotime('-1 day', $lastM);
			$data['to'] = $lastM;
		} elseif ($interval == 'custom') {
			$data['from'] = $time;
			$data['to'] = $data["timeTo"];
		} else {
			$data['from'] = 0;
			$data['to'] = PHP_INT_MAX;
		}

		Yii::app()->user->setState('selected-time-to', $data["to"]);
		return $data;
	}

	/**
	 * Sets the station's name for the data row.
	 * 
	 * @param array $data
	 * @return array
	 */
	private function setStationName($data) {
		$sid = $data['station'];
		$record = Yii::app()->db->createCommand('SELECT `name` FROM {{station}} WHERE id=:id LIMIT 1')->queryRow(true, array(':id' => $sid));
		$data['stationname'] = $record['name'];
		return $data;
	}

	/**
	 * Sets the graph's name.
	 * 
	 * @param array $data
	 * @param type $graphname
	 * @return array
	 */
	private function setGraphTitle($data, $graphname) {
		$stationName = $data['stationname'];
		$tInterval = mb_ucfirst(Yii::t('app', $data['interval']), 'UTF-8');
		$tGraph = Yii::t('app', $graphname);
		$tData = Yii::t('app', 'data');

		$data['title'] = sprintf('%s - %s %s %s', $stationName, $tInterval, $tGraph, $tData);

		return $data;
	}

	/**
	 * Returns the "select" caluses for a query.
	 * If the timespan is larger that 2 days, the graphs should group the data;
	 * this function takes care of the select part.
	 * 
	 * @param type $data user settings
	 * @param type $column series settings
	 * @return type
	 */
	private function setQuerySelect($data, $columns) {
		$dateColName = 'date';
		$select = array(/* $dateColName */);
		$diff = $data['to'] - $data['from'];

		$columnList[] = array('name' => $dateColName, 'singular' => $dateColName, 'plural' => "MIN($dateColName)");

		if (isset($columns['dbColumn']) || isset($columns['dbSelect'])) {
			$columns = array($columns);
		}

		foreach ($columns as $column) {
			if (isset($column['dbSelect'])) {
				$columnList[] = $column['dbSelect'];
			} else {
				$columnList[] = array(
					'name' => $column['dbColumn'],
					'singular' => $column['dbColumn'],
					'plural' => (isset($column['grouping']) ? $column['grouping'] : 'AVG') . '(' . $column['dbColumn'] . ')');
			}
		}

		foreach ($columnList as $col) {
			if (isset($column['grouping']) && $diff > 172800) {
				$select[] = $col['plural'] . ' AS ' . $col['name'];
			} else {
				$select[] = $col['singular'] . ' AS ' . $col['name'];
			}
		}
		return $select;
	}

	/**
	 * Returns the "group by" clauses for a query.
	 * If the timespan is larger that 2 days, the graphs should group the data;
	 * this function takes care of the group by part.
	 * 
	 * @param type $data
	 * @param type $datePrefix
	 * @return string
	 */
	private function setGrouping($data, $datePrefix = false) {
		$dateColName = $datePrefix ? $datePrefix . '.date' : 'date';
		$grouping = false;

		$diff = $data['to'] - $data['from'];
		if ($diff > 5184000) {
			$grouping = 'FROM_UNIXTIME(' . $dateColName . ',"%Y%m%d")';
		} elseif ($diff > 172800) {
			$grouping = 'FROM_UNIXTIME(' . $dateColName . ',"%Y%m%d%H")';
		}
		return $grouping;
	}

}

function mb_ucfirst($string, $encoding) {
	$strlen = mb_strlen($string, $encoding);
	$firstChar = mb_substr($string, 0, 1, $encoding);
	$then = mb_substr($string, 1, $strlen - 1, $encoding);
	return mb_strtoupper($firstChar, $encoding) . $then;
}
