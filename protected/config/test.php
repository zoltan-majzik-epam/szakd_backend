<?php

return CMap::mergeArray(
		//require(dirname(__FILE__) . '/main-local.php'), 
		require(dirname(__FILE__) . '/main.php'), 
		//require(dirname(__FILE__) . '/console.php'), 
		array(
		'components' => array(
			'fixture' => array(
				'class' => 'system.test.CDbFixtureManager',
			),
		/* uncomment the following to provide test database connection
		  'db'=>array(
		  'connectionString'=>'DSN for test database',
		  ),
		 */
		))
);
