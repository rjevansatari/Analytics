<?php
	ini_set('memory_limit', '128M');
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

	if ( isset($_POST['report']) ) {
		$path = "/tmp/".$_POST['report'];
		$mm_type="application/octet-stream"; 
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: public");
		header("Content-Description: File Transfer");
		header("Content-Type: " . $mm_type);
		header("Content-Length: " .(string)(filesize($path)) );
		header('Content-Disposition: attachment; filename="'.basename($path).'"');
		header("Content-Transfer-Encoding: binary\n");

		readfile($path); // outputs the content of the file

		exit();
	}
	else {
		echo "<p>ERROR: Invalid report specification.</p>\n";
	}
	exit();
?>
