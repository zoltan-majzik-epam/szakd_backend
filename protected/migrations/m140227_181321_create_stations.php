<?php

class m140227_181321_create_stations extends CDbMigration
{
	public function up()
	{
		$this->createTable("{{station}}", array(
			"id"	=> "int NOT NULL",
			"name"	=> "varchar(64) NOT NULL",
			"token"	=> "varchar(32) NOT NULL",
			"phone"	=> "varchar(16)",
		));
		$this->addPrimaryKey("pk_station", "{{station}}", "id");
		
	}

	public function down()
	{
		$this->dropPrimaryKey("pk_station", "{{station}}");
		$this->dropTable("{{station}}");
		
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