<?php
	ini_set('memory_limit', '128M');
	date_default_timezone_set('America/Los_Angeles');

	//Set up debugging

	require_once 'Log.php';
	require_once 'inc/db.php';
	require_once 'inc/config.php';

	//check options
	$options = getopt("ds:e:f:r:k:g:c:o:");
	if ( array_key_exists('d',$options) ) {
		$debug = Log::singleton('console');
	}

	debugger("getops : " . print_r($options,TRUE));

	if ( $options['o'] ) { 
		$output_file = "$CSV/" . $options['o'];
		$report_file = "$REPORT/" . "report_" . basename($output_file, ".csv") . ".gz";
	}
	else {
		$output_file = "$CSV/flurry_game_monitor.csv";
	}
	$fh = fopen($output_file, 'w') or die ("ERROR: Could not open output file.\n");

	if ( $options['f'] ) {
		$input_file=$options['f'];
		parse_file($input_file,$options['g'],$options['c']);
		exit;
	}
	if ( $options['r'] ) {
		$resource=get_file($options['r']);
		parse_file($resource,$options['g'],$options['c']);
		exit;
	}

	//Start and end dates
	if ( ! $options['s'] ) { 
		$start_date=date("Y-m-d");
	}
	else {	
		$start_date = $options['s'];
	}
	if ( ! $options['e'] ) {
		$end_date = $start_date;
	}
	else {
		$end_date = $options['e'];
	}

	if ( $options['k'] ) {
		$apiKey="AND apikey='" . $options['k'] . "'";
	}
	else { 
		$apiKey='';
	}
	
	$url_root = "http://api.flurry.com/appMetrics/";
        $api_accesscode="apiAccessCode=D6ISF4C16B7HLN6XCVIH";
        $api_key="&apiKey=";
	$date=date("Y-m-d", mktime(0, 0, 0, date("m"),date("d")-1,date("Y")));
	$start="&startDate=" . $start_date;
	$end="&endDate=" . $end_date;

	//Get a list of games and API keys
	$sql = "SELECT name, game_id, device_id, apikey
		FROM lookups.l_flurry_game
		WHERE 1=1
		$apiKey
		ORDER BY name";

	$results =& $mdb2->query($sql);

	while ($row = $results->fetchRow(MDB2_FETCHMODE_ASSOC)) {
	
		//Check to see if we have a report
		$metric="ActiveUsers?";
		$url = $url_root . $metric . $api_accesscode . $api_key . $row['apikey'] . $start . $end;
		debugger("URL : $url");
		$json_active=get_json($url);
		sleep(2);

		$metric="NewUsers?";
		$url = $url_root . $metric . $api_accesscode . $api_key . $row['apikey'] . $start . $end;
		debugger("URL : $url");
		$json_new=get_json($url);
		sleep(2);

		$metric="Sessions?";
		$url = $url_root . $metric . $api_accesscode . $api_key . $row['apikey'] . $start . $end;
		debugger("URL : $url");
		$json_session=get_json($url);
		sleep(2);

		debugger("JSON Active : " . print_r($json_active, TRUE) );
		debugger("JSON Install : " . print_r($json_new, TRUE) );
		debugger("JSON Session : " . print_r($json_session, TRUE) );

		add_value($fh,$json_active,$row['game_id'],$row['device_id']);
		add_value($fh,$json_new,$row['game_id'],$row['device_id']);
		add_value($fh,$json_session,$row['game_id'],$row['device_id']);

	}

	fclose($fh);

function debugger($msg) {

   global $debug;

   if ( $debug ) {
	$debug->log($msg, PEAR_LOG_DEBUG);
   }
}

function add_value($fh, $metric, $game_id, $device_id) {
	if ( $metric['day']['@value'] == 0 ) { 
		return;
	}
	$record="$game_id,$device_id,'" . date("Y-m-d H:i:s") . "','" . $metric['@metric'] . "'," . $metric['day']['@value'] . "\n"; 
	$rc=fwrite($fh,$record);
	return $rc;
}

function get_json($url) {
	
    $ch = curl_init();

    curl_setopt_array($ch, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
	CURLOPT_ENCODING       => ""
    
    ));

    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "application/json"
    ));

    $json = curl_exec($ch);

    $err = '';
    $errmsg = '';
    $header = '';

    $err     = curl_errno( $ch );
    $errmsg  = curl_error( $ch );
    $header  = curl_getinfo( $ch );

    unset($ch);

    debugger("DEBUG : err : $err errmsg : $errmsg header : " . print_r($header,TRUE));

    if ($json != false) {
        $data = json_decode($json, TRUE);
       	return $data;
    }
    else {
        return FALSE;
    }
}

?>
