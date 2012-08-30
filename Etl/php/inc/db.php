<?php
	require_once 'PEAR.php';
	require_once 'MDB2.php';

function db_connect() {
	$dsn = array(
	'phptype'  => 'mysql',
	'username' => 'analytics',
	'password' => '4T4r!an@lyt5',
	'hostspec' => 'localhost');

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

function run_sql($mdb2, $sql) {
	
	$results = $mdb2->query($sql);

        if (PEAR::isError($results)) {
                die("ERROR: SQL: " . $sql . "\n" . "ERROR: MSG: " . $results->getMessage() . "\n");
        }
	
	return $results;
}
