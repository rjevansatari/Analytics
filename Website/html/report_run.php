<?php

	ini_set('memory_limit', '128M');
	ini_set("display_errors", "1");
	error_reporting(E_ALL);

	date_default_timezone_set('America/Los_Angeles');

	define('__ROOT__', dirname(dirname(__FILE__))); 
	define('__DEBUG__', TRUE);

	//HTML Table output
	require_once 'HTML/Table.php';

	//Set up debugging
	require_once 'Log.php';

	//XML Parser
	require_once 'XML/Unserializer.php';
	
	// Custom Report Class
	require_once (__ROOT__.'/inc/Report_Class.php');

	// Config
	require_once (__ROOT__.'/inc/db.php');
	require_once (__ROOT__.'/inc/config.php');
	//require_once (__ROOT__.'/inc/report_class.php');


	// If no parameters passed, set default report for testing
	if ( !isset($_GET['_report']) ) {

		$report = new Report('mobile_dau_by_date',array('gameid' =>1, 'clientid' => 1, 'startdate' => '2012-08-01', 'enddate' => '2012-08-25'));
	}
	else {
		// Create report and pass the parameters if any
		$report = new Report($_GET['_report'], $_GET);
	}

	// Connect to DB and set connection pointers
	$db = db_connect();
	$report->setDB($db); 


	// If the report has no passed parms, get them
	if ( $report->getNumberPassedParms() == 0 ) {
		$report->parms->toHTML();
		exit;
	}

	// Show the report results
	ob_start();
	$report->toHTML();
	ob_end_flush();

?>
