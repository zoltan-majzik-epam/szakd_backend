<?php

class m140409_220347_measurements extends CDbMigration
{
	public function safeUp()
	{
                $this->createTable('{{measurements}}', array(
                    'stationid'         => 'integer',
                    'date'              => 'integer',
                    'temperature'       => 'decimal(5,2)',
                    'humidity'          => 'decimal(5,2)',
                    'leaf_wetness'      => 'tinyint',
                    'PRIMARY KEY (stationid,date)',
                ));
                
                $this->addForeignKey('fk_measurements_stationid', '{{measurements}}', 'stationid', '{{station}}', 'id', 'cascade', 'cascade');
	}

	public function safeDown()
	{
                $this->dropForeignKey('fk_measurements_stationid', '{{measurements}}');
                $this->dropTable('{{measurements}}');
	}
}