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

	$db = db_connect();

	// If no parameters passed, set default report for testing
	if ( !isset($_GET['_report']) ) {
		echo "ERROR: No report name passed.<br>\n";
		exit;
	}
	else {
		// Create report and pass the parameters if any
		$reportName = ($_GET['_report']);
		if ( isset($_GET['_cache']) ) {

			$report_startts=$_GET['_cache'];
			$sql = "SELECT report_html from reporting.report_log
				WHERE report_name='".$reportName."'
				AND report_startts='".$report_startts."';";

			$result = run_sql($db, $sql);

			while ($row = $result[0]->fetch_assoc()) {
				echo $row['report_html'];
				exit;
			}
		}
	}

	// Connect to DB and set connection pointers
	// Header
        require_once(__ROOT__.'/tpl/view_hdr.tpl');

	$sql = "SELECT * from reporting.report_log
		WHERE report_name='".$reportName."'
		ORDER by report_startts desc";	

	$result = run_sql($db, $sql);

	echo "<table>\n";

	while ($row = $result[0]->fetch_assoc()) {

		echo "<tr><td><a href='report_log.php?_report=".$reportName."&_cache=".$row['report_startts']."'>".$row['report_startts']."</td></tr>\n";
	}

	echo "</table>\n";
        require_once(__ROOT__.'/tpl/view_ftr.tpl');

?>
