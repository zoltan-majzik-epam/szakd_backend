<?php

/**
 * Használata:
 *      Yii::app()->clientScript->registerPackage( 'csomagneve' );
 * 
 */
class Packages extends CComponent {

	public static function register() {
		/* @var $cs CClientScript */
		$cs = Yii::app()->clientScript;
		$cs->packages = array(
			'jquery' => array(
				'baseUrl' => 'http://ajax.googleapis.com/ajax/libs/jquery/1.8.3',
				'js' => array('jquery.min.js'),
			),
			'jquery.ui' => array(
				'baseUrl' => 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.9.2',
				'js' => array('jquery-ui.min.js'),
				'depends' => array('jquery'),
			),
			'globals' => array(
				'baseUrl' => Yii::app()->getBaseUrl(false),
				'js' => array(
					'js/webapp/functions.js',
				),
				'depends' => array('jquery.ui'),
			),
			'google.maps.api' => array(
				'baseUrl' => 'http://maps.googleapis.com/maps/api',
				'js' => array('js?key=AIzaSyDDF93Y7C3UXUXh0Rkaho8NF9T02erqTds&sensor=false'),
				'depends' => array('globals'),
			),
			'highcharts' => array(
				'baseUrl' => Yii::app()->getBaseUrl(false) . '/js/highcharts',
				'js' => array(
					'highcharts.js',
					'highcharts-more.js',
					'modules/exporting.js',
				),
				'depends' => array('globals'),
			),
			'graphs' => array(
				'baseUrl' => Yii::app()->getBaseUrl(false) . '/js/webapp/graphs',
				'js' => array(
					'functions.js',
					'index.js'
				),
				'depends' => array('highcharts'),
			)
		);

		$cs->registerPackage('globals');
	}

}

?>
