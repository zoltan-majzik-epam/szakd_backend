<?php

class m140407_224229_add_users_table extends CDbMigration
{
	public function up()
	{
		$this->createTable("{{user}}", array(
			"id"	=> "pk",
			"name"	=> "VARCHAR(64) NOT NULL",
			"email"	=> "VARCHAR(64) NOT NULL",
			"password"	=> "VARCHAR(128) NOT NULL",
			"username"	=> "VARCHAR(32) NOT NULL",
			"role"	=> "VARCHAR(16) DEFAULT 'user'",
			"lastLogin"	=> "int"	
		));
		
		$params = array(
			"name"		=> "Majzik Zoltán",
			"email"	=> "spiru89@gmail.com",
			"username"	=> "admin",
			"password"	=> '$2a$13$x7Cd5b94rHRiyr9JqMu8v.eGgH/BFPnX2NgkO3V8ovFdjM0qIk1Ze',
			"role"		=> "admin"
		);
		$this->dbConnection->createCommand()->insert("{{user}}", $params);
	}

	public function down()
	{
		$this->dropTable("{{user}}");
	}

	/*
	// Use safeUp/safeDown to do migration with transaction
	public function safeUp()
	{
	}

	public function safeDown()
	{
	}
	*/
}