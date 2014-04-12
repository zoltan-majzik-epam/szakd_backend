<?php

class m140412_093930_test_station extends CDbMigration
{

	
	// Use safeUp/safeDown to do migration with transaction
	public function safeUp()
	{
		$token = md5(1 . time());
		$this->insert("{{station}}", array(
			"id"	=> 1,
			"name"	=> "Test Station",
			"token"	=> $token,
			"phone"	=> ""
		));
	}

	public function safeDown()
	{
		$this->delete("{{station}}", array("id" => 1));
	}
	
}