<?php
	error_reporting(E_ALL);
	ini_set('memory_limit', '-1');
	//date_default_timezone_set('America/New_York');

	//Set up debugging

	require_once 'inc/config.php';

	//check options
	//d-debug
	//s-startdate
	//e-enddate
	//z-gzip input
	//r-resource
	//k-api key
	//g-game id
	//c-client id
	//u-only do sessions (no events)
	//x-do not parse file, only x-tract
	
	$options = getopt("ds:e:z:r:k:g:c:o:u:x");

	if ( array_key_exists('z',$options) ) {

		$file=$options['z'];
		parse_file($file);
		exit;
	}
	else {
		die ("ERROR: No input file passed. Exiting...");
	}

function parse_file($file) {

    # Do we have a zipped file or not?
    $buffer='';
    if ( substr($file, -3, 3) == '.gz' ) {
	$gz = @gzopen($file, 'rb');

	if ($gz) {

		# Check to see if we got a report
    		# Check to make sure we have a valid file
		# Read the file
		while (!gzeof($gz)) {
	        	$buffer .= gzread($gz, 4096);
        	}
        	gzclose($gz);
    	} 
	else {
		die("Could not open gzip file. Exiting...");
	}

	var_dump(json_decode($buffer),TRUE);
   }
   else {
	die("Wrong file type. Exiting...");
   }
}
?>
