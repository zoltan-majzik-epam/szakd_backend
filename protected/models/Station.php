<?php

Yii::import('application.models._base.BaseStation');

class Station extends BaseStation
{
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}
}