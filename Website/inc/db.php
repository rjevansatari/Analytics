<?php
	//require_once 'PEAR.php';
	//require_once 'MDB2.php';

function note($msg) {
   echo "NOTE: " . date("Y-m-d H:i:s") . ": $msg\n";
}

function var_dump_to_string($var_to_dump) {
	ob_start();
	var_dump($var_to_dump);
	$result = ob_get_clean();
	return $result;
}

function debugger($msg) {

   global $options;

   if ( array_key_exists('d',$options)) {
        echo "DEBUG: " . date("Y-m-d H:i:s") . " $msg\n";
	flush();
   }
}


function error($msg) {
   echo "ERROR: " . date("Y-m-d H:i:s") . ": $msg\n";
   exit(4);
}

function db_connect($dbtype='mysql') {
	$dbhost='localhost';
	$dbuser='analytics';
	$dbpass='4T4r!an@lyt5';

	if ( $dbtype == 'pear' ) {	
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
			error($mdb2->getMessage());
		}
		return $mdb2;
	}
	else if ( $dbtype == 'mysql' ) {
		$mysql_conn = mysqli_connect($dbhost, $dbuser, $dbpass);
		if (mysqli_connect_errno()) {
    			error("MySQL : Connect failed: %s\n" . mysqli_connect_error());
		}
  		return $mysql_conn;
	}
	else if ( $dbtype == 'postgres' ) {
		$postgres_conn = pg_connect("host=$dbhost user=$dbuser password=$dbpass");
		if (!$postgres_conn) {
    			error("PostGres : Connect failed: %s");
		}
  		return $postgres_conn;
	}
}

function db_fetch_assoc($results, $dbtype='mysql') {
	if ( $dbtype == 'mysql' ) {
		$row=$results->fetch_assoc();
	}
	else if ( $dbtype == 'postgres' ) {
		$row=pg_fetch_assoc($results);
	}
	return $row;
}
function data_seek($result, $row=0, $dbtype='mysql') {
	if ( $dbtype == 'mysql' ) {
 		mysqli_data_seek($result, $row);
	}
	else if ( $dbtype == 'postgres' ) {
 		pg_result_seek($result, $row);
	}
}

function run_sql($db, $sql, $dbtype='mysql') {

	$results=array();
	
	if ( $dbtype == 'pear' ) {
		$results = $mdb2->query($sql);

	        if (PEAR::isError($results)) {
			error("MySQL : SQL : " . $sql . "\n" . "ERROR: MSG: " . $results->getMessage());
       		}
	}
	else if ( $dbtype='mysql' ) {
		
		if ( $db->multi_query($sql) ) {

			$results[] = $db->store_result();

		   	while ($db->more_results()) {

				$db->next_result();
				
				if ( $db == FALSE || $db->errno != 0 ) {
					error("MySQL : SQL : " . str_replace("\n","<br>",$sql) . "<br>" . "ERROR: MSG: " . $db->error . "<br>");
				}
				else {
					$results[] = $db->store_result();
				}
			}
			//Check there are no errors
			if ( $db->errno > 0 ) {
				error("MySQL : SQL : " . str_replace("\n","<br>",$sql) . "<br>" . "ERROR: MSG: " . $db->error . "<br>");
			}
		}
		else {
			error("MySQL : SQL : " . str_replace("\n","<br>",$sql) . "<br>" . "ERROR: MSG: " . $db->error . "<br>");
		}
	}
	else if ( $dbtype='mysql') {
	}
	return $results;
}
