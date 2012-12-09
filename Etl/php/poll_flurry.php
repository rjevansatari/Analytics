<?php
	error_reporting(E_ALL);
	ini_set('memory_limit', '-1');
	//date_default_timezone_set('America/New_York');

	//Set up debugging

	require_once 'inc/db.php';
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

	debugger("getops : " . print_r($options,TRUE));

	if ( array_key_exists('o',$options) ) { 
		$doutput_file_sessions = "$CSV/" . $options['o'] . '_sessions.csv';
		$output_file_events = "$CSV/" . $options['o'] . '_events.csv';
		$report_file = "$REPORT/" . "report_" . $options['o'] . ".gz";
	}
	else {
		$output_file_sessions = "$CSV/output_sessions.csv";
		$output_file_events = "$CSV/output_events.csv";
		$report_file = "$REPORT/" . "report.gz";
	}
	
	//Get a list of events and parms
	$events=get_events();
	$parms=get_parms();
	$devices=get_devices();

	if ( array_key_exists('z',$options) ) {

		$fhs = fopen($output_file_sessions, 'w') or die ("ERROR: Could not open session output file.\n");
		$fhe = fopen($output_file_events,   'w') or die ("ERROR: Could not open events output file.\n");

		$input_file=$options['z'];
		debugger("Parsing passed file.");
		parse_file($input_file,$options['g'],$options['c'], $options);

		fclose($fhs);
		fclose($fhe);
		exit;
	}
	if ( array_key_exists('r',$options) ) {

		$fhs = fopen($output_file_sessions, 'w') or die ("ERROR: Could not open session output file.\n");
		$fhe = fopen($output_file_events,   'w') or die ("ERROR: Could not open events output file.\n");

		$resource=get_file($options['r']);
		parse_file($resource,$options['g'],$options['c'], $options);

		fclose($fhs);
		fclose($fhe);
		exit;
	}

	//Start and end dates
	if ( array_key_exists('s',$options) ) {
		$start_date = $options['s'];
	}
	else {
		die("ERROR: No start date passed. Exiting...\n");
	}

	if ( array_key_exists('e',$options) ) {
		$end_date = $options['e'];
	}
	else {
		die("ERROR: No end date passed. Exiting...\n");
	}

	note("Start date is: $start_date");
	note("End date is: $end_date");

	if ( array_key_exists('k',$options) ) { 
		$apiKey="AND apikey='" . $options['k'] . "'";
	}
	else { 
		$apiKey='';
	}
	
	$url_root = "http://api.flurry.com/rawData/Events?";
        $api_accesscode="apiAccessCode=";
        $api_key="&apiKey=";
	$start="&startDate=" . $start_date;
	$end="&endDate=" . $end_date;

	$db=db_connect();
	//Get a list of games and API keys
	$sql = "SELECT game_name, ref_name, game_id, device_id, apikey, apicode
		FROM lookups.l_flurry_game
		WHERE 1=1
		$apiKey";
	if ( !array_key_exists('k',$options) ) {
		$sql .= "AND raw_extract=1\n";
	}
	$sql .= "ORDER BY game_name\n;";

	# We are going to store the requests, reports and files in an array
	$urls=array();
	$reports=array();
	$files=array();

	# Get a list of games to pull
	$results = run_sql($db,$sql);

	// Loop through the results
	while ( $row = db_fetch_assoc($results[0]) ) {
	
		// Set URL
		$url = $url_root . $api_accesscode . $row['apicode'] . $api_key . $row['apikey'] . $start . $end;
		$urls[] = array('game_id' => $row['game_id'],
                                'client_id' => $row['device_id'],
                                'ref_name' => $row['ref_name'],
                                'url' => $url);

		note("Curl URL for Game: " . $row['game_id'] . ", Client: " . $row['device_id'] . " is:\n$url.");

	}

	mysqli_free_result($results[0]);
	mysqli_close($db);

	$count=1;
	$n_urls=count($urls);

	// Loop through the games making requests for data
	while ( $n_urls > count($reports) && $count < 100 ) { 

		get_requests($urls, $reports);

		// Only sleep if we have not finished.
		if ( $n_urls > count($reports) ) { \
			get_reports($reports, $files);
			note("Waiting five minutes for reports. Count=$count.");
			sleep(300);
			$count++;
		}

	}
	note("Got reports for all games as follows:");
	print_r($reports);

	if ( $count == 100 ) {
		die ("ERROR: " . date("Y-m-d H:i:s") . ": Could not get a valid reports for $start_date to $end_date.\n");
	}

	$count=0;

	$n_reports = count($reports);

	// Loop through the requests parsing data
	while ( $n_reports > count($files) && $count < 500 ) { 

		get_reports($reports, $files);

		// Only sleep if we have not finished
		if ( $n_reports > count($files) ) {
			note("Waiting one minute for file. Count=$count.");
			sleep(60);
			$count++;
		}
	}

	// Check to see if we hit the limit
	if ( $count == 500 ) {
		die ("ERROR: " . date("Y-m-d H:i:s") . ": Could not get valid files for $start_date to $end_date.\n");
	}

	// Lets hope we got some files
	if ( count($files) > 0 ) {
		note("Downloaded all reports for all games between $start_date and $end_date as follows:");
		print_r($files);
		echo "\n";
	}

function get_requests(&$urls, &$reports){

	note("Starting report requests.");

	foreach ( $urls as $index => $game ) {

		$json=get_json($game['url']);

		note("Memory Usage: while wait: " . memory_get_usage());
	
		if ( $json != FALSE ) {
			foreach ($json as $key => $value) {
				//debugger("JSON KEY : $key, JSON VALUE : $value.");
				if ( $key == "code" ) {
					note("Code returned from JSON for Game: " . $game['game_id'] . ", Client: " . $game['client_id'] . " is:");
					print_r($value);	
					echo "\n";
				}
				if ( $key == "message" ) {
    					if ( $value == "APICodeCompanyNotFound" ) {
						die("ERROR: Incorrect API Code found for " . $game['game_id'] . ", Client: " . $game['client_id'] . ".\n");
					}
					note("Message returned from JSON for Game: " . $game['game_id'] . ", Client: " . $game['client_id'] . " is:");
					print_r($value);
					echo "\n";
				}
				if ( $key == 'report' ) {
					note("Report returned from JSON for Game: " . $game['game_id'] . ", Client: " . $game['client_id'] . " is:");
					print_r($json);
					echo "\n";
					$reports[] = array('game_id' => $game['game_id'],
                               			'client_id' => $game['client_id'],
						'ref_name' => $game['ref_name'],
                               			'request' => $value);
					// Remove this array entry
					unset($urls[$index]);
					note("Removing URL for Game: " . $game['game_id'] . ", Client: " . $game['client_id'] . ".  Array count now: " . count($urls));
				}
			}
		}
		else {
			note("Returned JSON for Game: " . $game['game_id'] . ", Client: " . $game['client_id'] . " was FALSE.");
		}
	}
}


function get_reports(&$reports, &$files){

	global $start_date;
	global $end_date;
	global $CSV;
	global $REPORT;

	note("Starting file requests.");

	// Work through reports until we have pulled all of them
	foreach ( $reports as $index => $report ) {

		$json = get_json($report['request']['@reportUri']);
		
		if ( $json['@reportReady'] != "false" ) {

		   //Set report file name
		   $report_file = "$REPORT/" . "report_" . $report['ref_name'] . "_" . $end_date . ".gz";
		   // Get the report
		   $file=get_file($report['request']['@reportUri'], $report_file);
		   note("Got file $file for Game: " . $report['game_id'] . ", Client: " . $report['client_id']);

		   // Are we writing this to a CSV?
		   if ( !array_key_exists('x',$options) ) {

			// Write to a CSV file - split sessions and events
		        $output_file_sessions = "$CSV/" . $report['ref_name'] . "_" . $end_date . '_sessions.csv';
		        $output_file_events = "$CSV/" . $report['ref_name'] . "_" . $end_date . '_events.csv';
			$fhs = fopen($output_file_sessions, 'w') or die ("ERROR: Could not open session output file.\n");
			$fhe = fopen($output_file_events,   'w') or die ("ERROR: Could not open events output file.\n");

		   	note("File $file parsing started for Game: " . $report['game_id'] . ", Client: " . $report['client_id'] . " from $start_date to $end_date.");
			$rc=parse_file($file, $report['game_id'],$report['client_id'], $options);
		   	// Remove this entry
		   	if ( $rc != FALSE ) { 
		   		note("File $file parsing complete for Game: " . $report['game_id'] . ", Client: " . $report['client_id'] . " from $start_date to $end_date.");
		   		$files[]= array('game_id' => $report['game_id'],
                               		'client_id' => $report['client_id'],
                                               'file' => $file);
				unset($reports[$index]);
		   	}
			else {
		   		note("File $file could not be parsed.");
			}
			fclose($fhs);
			fclose($fhe);
		   }
		   else {
		   	note("File $file was not parsed for Game: " . $report['game_id'] . ", Client: " . $report['client_id'] . " from $start_date to $end_date.");
			unset($reports[$index]);
		   }
		}
		else {
		   	note("File not ready for Game: " . $report['game_id'] . ", Client: " . $report['client_id'] . " from $start_date to $end_date.");
		}
	}
}

function note($msg) {
   echo "NOTE: " . date("Y-m-d H:i:s") . ": $msg\n";
}

function debugger($msg) {

   global $options;

   if ( array_key_exists('d',$options)) {
	echo "DEBUG: " . date("Y-m-d H:i:s") . " $msg\n"; 
   }
}

function parse_file($file, $game_id, $device_id, $options) {

    global $start_date;
    global $end_date;

    $user_count=0;

    note("Memory Usage: parse_file: " . memory_get_usage() . ".");

    # Do we have a zipped file or not?
    if ( substr($file, -3, 3) == '.gz' ) {
	debugger("Got file, attempting to open it.");
	$gz = @gzopen($file, 'rb');

	    if ($gz) {

		# Check to see if we got a report
	        $buffer = gzread($gz, 4096);
		//debugger("Buffer=$buffer\n");
    		# Check to make sure we have a valid file
    		if ( strpos($buffer, "<head><title>302 Found</title></head>" ) ) {
			note("302 Redirect found for Game: $game_id, Client: $device_id between $start_date and $end_date.");
			return FALSE;
    		}
    		if ( strpos($buffer, "APICodeCompanyNotFound" ) ) {
			die("ERROR: Incorrect API Code found for Game: $game_id, Client: $device_id between $start_date and $end_date.");
		}

		# So, we must have a valid JSON
    		$offset=strpos($buffer, 'sessionEvents');
		if ( $offset === FALSE ) {
			return FALSE;
		}

		$user_count=0;

		# Read the file
		while (!gzeof($gz)) {

    			$user_start=strpos($buffer, '{"u":');
			$user_end=strpos($buffer, '{"u":', $user_start+5);

			# Do we have one? If not, keep reading until we do
			if ( $user_end == FALSE  && !gzeof($gz) ) {

				$data=substr($buffer, $user_start);
				# Save current bufferr
				while ( $user_end == FALSE && !gzeof($gz) ) {
            				$buffer = gzread($gz, 4096);
					//debugger("Buffer While Loop=$buffer\n");
					$user_end=strpos($buffer, '{"u":');
					if ( $user_end == FALSE ) {
						$data .= $buffer;
					}
				}
				$str=$data . substr($buffer,0,$user_end-1);
				//debugger("Str1=$str\n");
			}
			else {
				$str=substr($buffer,$user_start,$user_end-$user_start-1);
				//debugger("Str2=$str\n");
			}
                	$user_count++;
			parse_user($str, $game_id, $device_id, $options);
			# Get new buffer string
			$buffer=substr($buffer, $user_end);
			# Get User start tag
    			$user_start=strpos($buffer, '{"u":');
        	}
        	gzclose($gz);
    	} 
	else {
		die("ERROR: Could not open gzip file. Exiting...");
	}
   }

   note("Processed $user_count users for Game: $game_id, Client: $device_id between $start_date and $end_date.");
   return TRUE;
}

# Get a list of device types
function get_devices() {

        note("Memory Usage: get_devices: " . memory_get_usage() . ".");

	$db=db_connect();

	$sql = "SELECT distinct device_gen, device_gen_id
                FROM lookups.l_device_gen
		ORDER by 1";

	$results = run_sql($db,$sql);
	$list=array();

	while ($row = db_fetch_assoc($results[0])) {
		$list[$row['device_gen']] = $row['device_gen_id'];
	}

	mysqli_free_result($results[0]);
	mysqli_close($db);
	return $list;
}

# Get a list of events so that we can map them pre database load
function get_events() {

        note("Memory Usage: get_events: " . memory_get_usage() . ".");

	$db=db_connect();

	$sql = "SELECT distinct lower(event_name) as event_name, event_id
                FROM lookups.l_event
		ORDER by 1";

	$results = run_sql($db,$sql);
	$list=array();

	while ($row = db_fetch_assoc($results[0])) {
		$list[$row['event_name']] = $row['event_id'];
	}

	mysqli_free_result($results[0]);
	mysqli_close($db);
	return $list;
}

function get_parms() {

        note("Memory Usage: get_parms: " . memory_get_usage() . ".");

	$db=db_connect();

	$sql = "SELECT distinct lower(parm_name) as parm_name, parm_id
                FROM lookups.l_parm
		ORDER by 1";

	$results = run_sql($db,$sql);
	$list=array();

	while ($row = db_fetch_assoc($results[0])) {
		$list[$row['parm_name']] = $row['parm_id'];
	}

	mysqli_free_result($results[0]);
	mysqli_close($db);
	return $list;
}

function add_parm($parm_name) {

        debugger("Memory: Usage add_parm: " . memory_get_usage() . "\n");

	$db=db_connect();
	$sql="INSERT INTO lookups.l_parm(parm_name) VALUES ('" . strtolower($parm_name). "'); COMMIT; ";
	$results = run_sql($db,$sql);

	note("New parm added : $parm_name.");

	$latest_parms=get_parms();

	mysqli_close($db);
	return $latest_parms;
	
}

function add_device($device_name) {

        debugger("Memory: Usage add_device: " . memory_get_usage() . "\n");

	$db=db_connect();
	$sql="INSERT INTO lookups.l_device_gen(device_gen) VALUES ('" .$device_name . "'); COMMIT; ";
	$results = run_sql($db,$sql);

	note("New device added : $device_name.");

	$latest_devicess=get_devices();

	mysqli_close($db);
	return $latest_devices;
	
}
function add_event($event_name) {

        debugger("Memory: Usage add_event: " . memory_get_usage() . "\n");

	$db=db_connect();
	$sql="INSERT INTO lookups.l_event(event_name) VALUES ('" . strtolower($event_name). "'); COMMIT; ";
	$results = run_sql($db,$sql);

	note("New event added : $event_name.");

	$latest_events=get_events();

	mysqli_close($db);
	return $latest_events;
	
}

function parse_user($str, $game_id, $client_id, $options) {

	global $fhs;
	global $fhe;
	global $events;
	global $parms;
	global $devices;

        debugger("Memory Usage: parse_user: " . memory_get_usage() . "\n");

	$json=json_decode($str);
	if ( $json != FALSE ) {	
		foreach ($json as $key => $value) {
				switch($key) {
				case 'u':
					$user=$value;
					break;
				case 'v':
					$version=$value;
					break;
				case 'dv':
					$device=$value;

					if ( !array_key_exists($device,$devices ) ) { 
						$devices=add_device($device);
					}
					break;
				case 't':
					$epoch=round($value/1000);
					$datetime = new DateTime("@$epoch", new DateTimeZone('EST')); 
					break;
				case 'l':
					$event=parse_events($epoch, $value);
					break;
			}
		}
		//SESSION
		$record= "$game_id,$client_id,'" . $user . "','" . $version . "'," . $devices[$device] . ",'" . $datetime->format('Y-m-d H:i:s') . "'\n";
		fwrite($fhs, $record);

		//EVENTS
		if ( !isset($options['u']) ) { 
	
			foreach ($event as $key => $value) {
                        	if ( array_key_exists(strtolower($value['event']), $events) ) { 
					$record="$game_id,$client_id,'" . $user . "','" . $version . "'," . $devices[$device] . ",'" . $value['time'] . "'," . $events[strtolower($value['event'])] . ",";
				}
				else {
					$events=add_event(strtolower($value['event']));
					$record="$game_id,$client_id,'" . $user . "','" . $version . "'," . $devices[$device] . ",'" . $value['time'] . "'," . $events[strtolower($value['event'])] . ",";
				}
                                if ( count($value['parameters']) > 0 ) {
                                        foreach ($value['parameters'] as $parameter_key => $parameter_value) {
						if ( $parameter_value == '' ) {
							$parameter_value='NULL';
						} 
						else { 
							$parameter_value="'".$parameter_value."'";
						}
                        			if ( array_key_exists(strtolower($parameter_key), $parms) ) { 
							fwrite($fhe, $record . $parms[strtolower($parameter_key)] . "," . $parameter_value . "\n");
						}
						else {
							$parms=add_parm(strtolower($parameter_key));
							fwrite($fhe, $record . $parms[strtolower($parameter_key)] . "," . $parameter_value . "\n");
						}
					}
				}
                                else {
					fwrite($fhe, $record . "NULL,NULL\n");
                                }
			}
		}
		unset($datetime);
	}
	unset($json);
}

function parse_events($time, $object) {
	
    debugger("Memory Usage: parse_events: " . memory_get_usage() . "\n");

    $result = array();
    $parameters=array();

    foreach ($object as $object_key => $event) {
    	foreach ($event as $event_key => $event_value) {
		switch($event_key) {
			case 'e':
   				$event=$event_value;
				break;
			case 'o';
				$event_time=$time+round($event_value/100);
				break;
			case 'p';
				$parameters=$event_value;
				break;
		}
	}
	//Now we have the event and the event time
	$datetime = new DateTime("@$event_time", new DateTimeZone('EST')); 
	$result[] = array('event' => strtolower($event), 
                          'time' => $datetime->format("Y-m-d H:i:s"),
			  'parameters' => $parameters);
    }
    unset($datetime);
    return $result;

}

function get_file($url, $file='') {

    note("Memory Usage: get_file: " . memory_get_usage() . ".");

    $report_id = substr($url,strrpos($url,'=',-1)+1);

    $ch = curl_init();

    if ( $file == '' ) {
	$file="report_" . "$report_id" . ".gz";
    }

    $fp = fopen($file, "w");

    curl_setopt_array($ch, array(
        CURLOPT_URL => $url,
	CURLOPT_ENCODING  => "",
	CURLOPT_HEADER => 0,
	CURLOPT_FILE => $fp
    
    ));

    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "application/xml"
    ));

    $rc = curl_exec($ch);

    $err = '';
    $errmsg = '';

    $err     = curl_errno( $ch );
    $errmsg  = curl_error( $ch );
    $header  = curl_getinfo( $ch );

    debugger("err : $err errmsg : $errmsg");

    curl_close($ch);
    fclose($fp);
    return $file;

}

function get_xml($url) {
	
    $ch = curl_init();

    curl_setopt_array($ch, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
	CURLOPT_ENCODING       => ""
    
    ));

    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "application/xml"
    ));

    $xml = curl_exec($ch);

    $err = '';
    $errmsg = '';
    $header = '';

    $err     = curl_errno( $ch );
    $errmsg  = curl_error( $ch );
    $header  = curl_getinfo( $ch );

    unset($ch);

    debugger("err : $err errmsg : $errmsg header : " . print_r($header,TRUE));

    if ($xml != false) {
	$p = xml_parser_create();
	xml_parse_into_struct($p, $xml, $vals, $index);
	xml_parser_free($p);
        return $data;
    } else {
        return FALSE;
    }
}

function get_json($url) {
	
    note("Memory Usage: get_json: " . memory_get_usage() . ".");
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

    curl_close($ch);


    if ($json != false) {
        return json_decode($json, TRUE);
    }
    else {
    	$msg="JSON return codes: err : $err errmsg : $errmsg header : ";
    	if (!$header && $header != '') {
    		$msg.=": JSON header as follows:";
		note($msg);
		print_r($header,TRUE);
		echo "\n";
    	}
        return FALSE;
    }
}

?>
