
<?php
	ini_set('memory_limit', '128M');
	//ini_set("display_errors", "1");
	error_reporting(E_ALL);
	date_default_timezone_set('America/Los_Angeles');

	define('__ROOT__', '/var/www'); 
	define('__DEBUG__', TRUE);

	//Set up debugging
	require_once 'Log.php';

	// Config
	require_once (__ROOT__.'/inc/db.php');
	require_once (__ROOT__.'/inc/config.php');

	// Include the report class
	require_once (__ROOT__.'/inc/Email_Class.php');

	$query = TRUE;

	// Check passed parms
        $options = getopt("xs:");

        if ( array_key_exists('x',$options) ) {
                $query = FALSE;
        }
        if ( array_key_exists('s',$options) ) {
                $sub_id = $options['s'];
        }

	// Connect to the DB
	$db = db_connect();

	//Read the subscriptions list
	if ( isset($sub_id) ) { 
		$sql = "SELECT * from reporting.report_subscriptions
			WHERE (subscription_frequency='daily' or
			weekday(curdate()) = 0 )
			AND subscription_id=$sub_id
			ORDER by subscription_prty";
	}
	else {
		$sql = "SELECT * from reporting.report_subscriptions
			WHERE (subscription_frequency='daily' or
			weekday(curdate()) = 0 )
			AND subscription_code=0
			ORDER by subscription_prty";
	}

	// Get the results
	$result = run_sql($db, $sql);
	$subscription = new Subscription();

	// Read through the results
	while ($row = $result[0]->fetch_assoc()) {
		$subscriptions[]=$row;
	}
	
	// Close
	mysqli_close($db);

	// This runs the subscriptions

	if ( $query ) { 
		foreach ($subscriptions as $index => $value) {
			//print_r($value);
			// Run queries
			$subscription->run($value);
			// Email results
			$subscription->eMail($value);
		}
	}
	else {
		// This just outputs the subscription
		foreach ($subscriptions as $index => $value) {
			//print_r($value);
			$subscription->eMail($value);
		}
	}
?>
