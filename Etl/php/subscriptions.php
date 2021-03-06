
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

	$query = FALSE;
	$email = FALSE;
	$user='';

	// Check passed parms
        $options = getopt("exr:s:u:");

        if ( array_key_exists('r',$options) ) {
                $run_level = $options['r'];
        }
        if ( array_key_exists('e',$options) ) {
                $email = TRUE;
        }
        if ( array_key_exists('x',$options) ) {
                $query = TRUE;
        }
        if ( array_key_exists('s',$options) ) {
                $sub_id = $options['s'];
        }
        if ( array_key_exists('u',$options) ) {
                $user = $options['u'];
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
	else if ( isset($run_level) ) {
		$sql = "SELECT * from reporting.report_subscriptions
			WHERE (subscription_frequency='daily' or
			weekday(curdate()) = 0 )
			AND subscription_run=$run_level
			AND subscription_code=0
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

	if ( $result[0]->num_rows <= 0 ) {
		error("Zero rows returned from subscription report table. Exiting...");
	}
	$subscription = new Subscription();

	// Read through the results
	while ($row = db_fetch_assoc($result[0])) {
		$subscriptions[]=$row;
	}
	
	// Close
	mysqli_close($db);

	// This runs the subscriptions

	$rc_return=TRUE;

	if ( $query ) { 
		foreach ($subscriptions as $index => $value) {
			//print_r($value);
			// Run queries
			$subscription->run($value);
			if ( $email ) {
				$rc=$subscription->eMail($value, $user);
				if ( $rc == FALSE ) {
					$return_rc=FALSE;
				}
			}

		}
	}
	elseif ( $email ) {
		// This just outputs the subscription
		foreach ($subscriptions as $index => $value) {
			//print_r($value);
			$rc=$subscription->eMail($value, $user);
			if ( $rc == FALSE ) {
				$return_rc=FALSE;
			}
		}
	}

	if ( $rc_return == FALSE ) {
		return 4;
	}
	else {
		return 0;
	}
?>
