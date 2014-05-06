<?php

Yii::import('application.models._base.BaseUser');

class User extends BaseUser {

	public $repeat_password;

	public static function model($className = __CLASS__) {
		return parent::model($className);
	}

	public function rules() {
		return array_merge(array(
			array('email', 'email'),
			array("email", "unique", "className" => "User", "attributeName" => "email", 'on' => 'create insert'),
			array('repeat_password', 'required', 'on' => 'create insert'),
			array('repeat_password, password', 'length', 'min' => 6, 'max' => 128, 'on' => 'create insert'),
			array('password', 'compare', 'compareAttribute' => 'repeat_password', 'on' => 'create insert'),
			array('role', 'default', 'value' => 'User', 'setOnEmpty' => true),
			array('username', 'unique', 'on' => 'insert'),
			), parent::rules());
	}

	public function beforeSave() {
		if ($this->isNewRecord) {
			$this->password = UserIdentity::getPasswordHash($this->username, $this->password);
		}
		parent::beforeSave();
	}
}
