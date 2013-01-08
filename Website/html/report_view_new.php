<?php
	ini_set('memory_limit', '128M');
	//ini_set("display_errors", "1");
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
	
	// Config
	require_once (__ROOT__.'/inc/db.php');
	require_once (__ROOT__.'/inc/config.php');

	// Include the report class
	require_once (__ROOT__.'/inc/Report_View.php');

	// These are default values if none passed
	$report='';

	if ( isset($_GET['_report']) ) {
		$report=$_GET['_report'];
	};
	if ( isset($_GET['_cache']) ) {
		$report=$_GET['_cache'];
	};
		
	// Connect to the DB
	$db = db_connect();

	// Build the menu
	$report_view = new Report_View($report, $db);
	$report_view->toHTML();

?>
