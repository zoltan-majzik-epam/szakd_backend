<?php
header('Content-Type: application/json');


//if the graphs are corrupted, then use this code, and remove the setOption line from the setGraph script

//date_default_timezone_set('UTC');
//$utcMidnight = strtotime('today');

//date_default_timezone_set(Yii::app()->user->getState('timezone', 'UTC'));
//$localMidnight = strtotime('today');
/*
$timeDiff = $localMidnight - $utcMidnight;

for ($i = 0; $i < count($data['series']); $i++) {
	for ($j = 0; $j < count($data['series'][$i]['data']); $j++) {
		if (is_array($data['series'][$i]['data'][$j]))
			$data['series'][$i]['data'][$j][0] -= ($timeDiff * 1000);
		if (is_object($data['series'][$i]['data'][$j]))
			$data['series'][$i]['data'][$j]->x -= ($timeDiff * 1000);
	}
}*/

if (isset($data["series"]) && is_array($data["series"]) && !empty($data["series"])) {
	$validData = false;
	foreach ($data["series"] as $serie) {
		if (isset($serie["data"]) && is_array($serie["data"]) && !empty($serie["data"]))
			$validData = true;
	}
	if (!$validData) {
		$data["emptyData"] = true;
		$data["emptyDataMessage"] = Yii::t("app", "No data in selected interval.") . " ";
		$data["emptyDataMessage"] .= CHtml::link(Yii::t("app", "Show latest data..."), Yii::app()->createAbsoluteUrl("graphs/index", array("time" => time())));
	}
}
echo json_encode($data);