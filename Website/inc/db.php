<?php
	require_once 'PEAR.php';
	require_once 'MDB2.php';

function db_connect() {
	$dbhost='localhost';
	$dbuser='analytics';
	$dbpass='4T4r!an@lyt5';

	if ( __PEAR__ ) {	
		$dsn = array(
		'phptype'  => 'mysql',
		'username' => $dbuser,
		'password' => $dbpass,
		'hostspec' => $dbhost);

		$options = array(
		'debug'       => 2,
		'portability' => MDB2_PORTABILITY_ALL,
		);

		// uses MDB2::factory() to create the instance
		// and also attempts to connect to the host
		$mdb2 = MDB2::connect($dsn, $options);
		if (PEAR::isError($mdb2)) {
			die($mdb2->getMessage());
		}
		return $mdb2;
	}
	else {
		$mysql_conn = mysqli_connect($dbhost, $dbuser, $dbpass);
		if (mysqli_connect_errno()) {
    			die("ERROR: MySQL : Connect failed: %s\n" . mysqli_connect_error());
		}
  		return $mysql_conn;
	}
}

function run_sql($db, $sql) {

	$results=array();
	
	if ( __PEAR__ ) {
		$results = $mdb2->query($sql);

	        if (PEAR::isError($results)) {
			die("ERROR: MySQL : SQL : " . $sql . "\n" . "ERROR: MSG: " . $results->getMessage() . "\n");
       		}
	}
	else {
		if ( $db->multi_query($sql) ) {

			$results[] = $db->store_result();

		   	while ($db->more_results()) {

				$db->next_result();
				
				if ( $db == FALSE || $db->errno != 0 ) {
					die("ERROR: MySQL : SQL : " . str_replace("\n","<br>",$sql) . "<br>" . "ERROR: MSG: " . $db->error . "<br>");
				}
				else {
					$results[] = $db->store_result();
				}
			}
			//Check there are no errors
			if ( $db->errno > 0 ) {
				die("ERROR: MySQL : SQL : " . str_replace("\n","<br>",$sql) . "<br>" . "ERROR: MSG: " . $db->error . "<br>");
			}
		}
		else {
			die("ERROR: MySQL : SQL : " . str_replace("\n","<br>",$sql) . "<br>" . "ERROR: MSG: " . $db->error . "<br>");
		}
	}
	return $results;
}
