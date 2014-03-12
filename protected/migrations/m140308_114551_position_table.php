<?php

class m140308_114551_position_table extends CDbMigration
{
	public function up()
	{
		$this->createTable("{{position}}", array(
			"id"	=> "pk",
			"station_id" => "int",
			"date"	=> "int NOT NULL",
			"lat"	=> "float NOT NULL",
			"lng"	=> "float NOT NULL",
		));
		$this->addForeignKey("stations_position", "{{position}}", "station_id", "{{station}}", "id", "cascade");
		$this->createIndex("unique_station_date", "{{position}}", "station_id, date", true);
	}

	public function down()
	{
		$this->dropForeignKey("stations_position", "{{position}}");
		$this->dropIndex("unique_station_date", "{{position}}");
		$this->dropTable("{{position}}");
		return true;
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