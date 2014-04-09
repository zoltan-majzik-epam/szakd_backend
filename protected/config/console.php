<?php

// This is the configuration for yiic console application.
// Any writable CConsoleApplication properties can be configured here.
$config = array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'MeasureStation',
	'import' => array(
		'application.models.*',
		'application.components.*'
	),

	// preloading 'log' component
	'preload'=>array('log'),

	// application components
	'components'=>array(
		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'error, warning',
				),
			),
		),
	),
);

$local = require_once 'console-local.php';

return array_merge_recursive($local, $config);