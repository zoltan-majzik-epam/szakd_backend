<?php

/**
 * This is the model base class for the table "{{measurements}}".
 * DO NOT MODIFY THIS FILE! It is automatically generated by giix.
 * If any changes are necessary, you must set or override the required
 * property or method in class "Measurements".
 *
 * Columns in table "{{measurements}}" available as properties of the model,
 * followed by relations of table "{{measurements}}" available as properties of the model.
 *
 * @property integer $stationid
 * @property integer $date
 * @property string $temperature
 * @property string $humidity
 * @property integer $leaf_wetness
 *
 * @property Station $station
 */
abstract class BaseMeasurements extends GxActiveRecord {

	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	public function tableName() {
		return '{{measurements}}';
	}

	public static function label($n = 1) {
		return Yii::t('app', 'Measurements|Measurements', $n);
	}

	public static function representingColumn() {
		return 'temperature';
	}

	public function rules() {
		return array(
			array('stationid, date, leaf_wetness', 'numerical', 'integerOnly'=>true),
			array('temperature, humidity', 'length', 'max'=>5),
			array('stationid, date, temperature, humidity, leaf_wetness', 'default', 'setOnEmpty' => true, 'value' => null),
			array('stationid, date, temperature, humidity, leaf_wetness', 'safe', 'on'=>'search'),
		);
	}

	public function relations() {
		return array(
			'station' => array(self::BELONGS_TO, 'Station', 'stationid'),
		);
	}

	public function pivotModels() {
		return array(
		);
	}

	public function attributeLabels() {
		return array(
			'stationid' => null,
			'date' => Yii::t('app', 'Date'),
			'temperature' => Yii::t('app', 'Temperature'),
			'humidity' => Yii::t('app', 'Humidity'),
			'leaf_wetness' => Yii::t('app', 'Leaf Wetness'),
			'station' => null,
		);
	}

	public function search() {
		$criteria = new CDbCriteria;

		$criteria->compare('stationid', $this->stationid);
		$criteria->compare('date', $this->date);
		$criteria->compare('temperature', $this->temperature, true);
		$criteria->compare('humidity', $this->humidity, true);
		$criteria->compare('leaf_wetness', $this->leaf_wetness);

		return new CActiveDataProvider($this, array(
			'criteria' => $criteria,
		));
	}
}