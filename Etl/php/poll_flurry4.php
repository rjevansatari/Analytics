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
		$output_file_sessions = "$CSV/" . $options['o'] . '_sessions.csv';
		$output_file_events = "$CSV/" . $options['o'] . '_events.csv';
		$report_file = "$REPORT/" . "report_" . $options['o'] . ".gz";
	}
	else {
		$output_file_sessions = "$CSV/output_sessions.csv";
		$output_file_events = "$CSV/output_events.csv";
	}
	
	//Get a list of events and parms
	$events=get_events();
	$parms=get_parms();

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
	$start_date = $options['s'];
	$end_date = $options['e'];

	echo "NOTE: Start date is: $start_date\n";
	echo "NOTE: End date is: $end_date\n";

	if ( $options['k'] ) {
		$apiKey="AND apikey='" . $options['k'] . "'";
	}
	else { 
		$apiKey='';
	}
	
	$url_root = "http://api.flurry.com/rawData/Events?";
        $api_accesscode="apiAccessCode=D6ISF4C16B7HLN6XCVIH";
        $api_key="&apiKey=";
	$start="&startDate=" . $start_date;
	$end="&endDate=" . $end_date;

	$db=db_connect();
	//Get a list of games and API keys
	$sql = "SELECT game_name, game_id, device_id, apikey
		FROM lookups.l_flurry_game
		WHERE 1=1
		$apiKey
		ORDER BY game_name";

	$results = run_sql($db,$sql);

	while ( $row = $results[0]->fetch_assoc() ) {
	
		echo "NOTE: Memory Usage: while row: " . memory_get_usage() . "\n";
		//Check to see if we have a report
		$url = $url_root . $api_accesscode . $api_key . $row['apikey'] . $start . $end;

		echo "NOTE:" . date("Y-m-d H:i:s") . ":Curl URL is: $url.\n";
		$wait_flag=1;
		$count=1;
		$json=get_json($url);

		while ( $wait_flag == 1 ) {

			echo "NOTE: Memory Usage: while wait: " . memory_get_usage() . "\n";
		
			if ( $json != FALSE ) {
				foreach ($json as $key => $value) {
					//debugger("JSON KEY : $key, JSON VALUE : $value.");
					if ( $key == "code" ) {
						echo "NOTE: " . date("Y-m-d H:i:s") . ": Code returned from JSON. Waiting 5 mins... Count=$count.\n";
						print_r($value);
						sleep(300);
					}
					if ( $key == "message" ) {
						echo "NOTE: " . date("Y-m-d H:i:s") . ": Message returned from JSON. Waiting 5 mins... Count=$count.\n";
						print_r($value);
						sleep(300);
						$wait_flag=1;
						$json=get_json($url);
						$count++;
					}
					if ( $key == 'report' ) {
						echo "NOTE: " . date("Y-m-d H:i:s") . ": Report returned from JSON.\n";
						echo "NOTE: " . date("Y-m-d H:i:s") . ": Report as follows:\n";
						print_r($json);
						$wait_flag=0;
					}
				}
			}
			else {
				echo "NOTE: " . date("Y-m-d H:i:s") .  ": Returned JSON was FALSE. Waiting 5 mins...\n";
				sleep(300);
				$wait_flag=1;
				$json=get_json($url);
			}
		}

		$count=1;

		if ( $key == 'report' ) {
			debugger("Value : " . print_r($value,TRUE));
			debugger("Got report URL : " . $value['@reportUri']);
			echo "NOTE: " . date("Y-m-d H:i:s") . ": Got report to process for $start_date to $end_date. Waiting...\n";
			sleep(300);
			$report = get_json($value['@reportUri']);
			$count=1;
			
			$rc=FALSE;
			
			while ( $rc == FALSE ) { 
			   while ( $report['@reportReady'] == "false"  && $count <= 100 ) { 
			   	   //Wait 60 seconds
			   	   debugger("Report Not Ready : Waiting 5 mins. Count is $count.");
			   	   echo "NOTE: " . date("Y-m-d H:i:s") . ": Report Not Ready for $start_date to $end_date. Waiting 5 mins... Count is : $count.\n";
				   sleep(300);
				   $report = get_json($value['@reportUri']);
				   $count++;
			   }

			   $file=get_file($value['@reportUri'], $report_file);

			   echo "NOTE: " . date("Y-m-d H:i:s") . ": File $file parsing started for $start_date to $end_date.\n";

			   if ( !array_key_exists('x',$options) ) {

				$fhs = fopen($output_file_sessions, 'w') or die ("ERROR: Could not open session output file.\n");
				$fhe = fopen($output_file_events,   'w') or die ("ERROR: Could not open events output file.\n");

				$rc=parse_file($file, $row['game_id'],$row['device_id'], $options);
				fclose($fhs);
				fclose($fhe);
				
				echo "NOTE: " . date("Y-m-d H:i:s") . ": File $file parsing complete for $start_date to $end_date.\n";
			   }
			   else {
				echo "NOTE: " . date("Y-m-d H:i:s") . ": File $file was not parsed for $start_date to $end_date.\n";
				$rc = TRUE;
			   }
			}
		}
		else {
			echo "NOTE: " . date("Y-m-d H:i:s") . ": Could not get a valid report for $file for $start_date to $end_date.\n";
		}
	}

	mysqli_free_result($results[0]);
	mysqli_close($db);

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

    echo "NOTE: Memory Usage: parse_file: " . memory_get_usage() . "\n";

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
			echo "NOTE: " . date("Y-m-d H:i:s") . ": 302 Redirect found for $start_date to $end_date. Waiting...\n";
			return FALSE;
    		}

		# So, we must have a valid JSON
    		$offset=strpos($buffer, 'sessionEvents');
		if ( $offset == FALSE ) {
			return FALSE;
		}

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
		echo "ERROR: Could not open gzip file. Exiting...\n";
		exit (4);
	}
   }
   exit;

    if ( $offset != FALSE ) { 
        $user_count=0;
    	while ( $user_start != FALSE ) {
		$user_end=strpos($data, '{"u":', $user_start+5);
		//debugger("user_start=$user_start and user_end=$user_end.");
		if ( $user_end != FALSE ) { 
			$str=substr($data,$user_start,$user_end-$user_start-1);
		}
		else {
			$str=substr($data,$user_start);
		}
                $user_count++;
		parse_user($str, $game_id, $device_id, $options);
             	$user_start=$user_end;   
        }
	echo "NOTE: ".date("Y-m-d H:i:s").": Processed $user_count users from $start_date to $end_date.\n";
    }
    return TRUE;
}

# Get a list of events so that we can map them pre database load
function get_events() {

        echo "NOTE: Memory Usage: get_events: " . memory_get_usage() . "\n";

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

        echo "NOTE: Memory Usage: get_parms: " . memory_get_usage() . "\n";

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

        echo "NOTE: Memory: Usage add_parm: " . memory_get_usage() . "\n";

	$db=db_connect();
	$sql="INSERT INTO lookups.l_parm(parm_name) VALUES ('" . strtolower($parm_name). "'); COMMIT; ";
	$results = run_sql($db,$sql);

	echo "NOTE: New parm added : $parm_name.\n";

	$latest_parms=get_parms();

	mysqli_free_result($results[0]);
	mysqli_close($db);
	return $latest_parms;
	
}

function add_event($event_name) {

        echo "NOTE: Memory: Usage add_event: " . memory_get_usage() . "\n";

	$db=db_connect();
	$sql="INSERT INTO lookups.l_event(event_name) VALUES ('" . strtolower($event_name). "'); COMMIT; ";
	$results = run_sql($db,$sql);

	echo "NOTE: New event added : $event_name.\n";

	$latest_events=get_events();

	mysqli_free_result($results[0]);
	mysqli_close($db);
	return $latest_events;
	
}

function parse_user($str, $game_id, $client_id, $options) {

	global $fhs;
	global $fhe;
	global $events;

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
		$record= "$game_id,$client_id,'" . $user . "','" . $version . "','" . $device . "','" . $datetime->format('Y-m-d H:i:s') . "'\n";
		fwrite($fhs, $record);

		//EVENTS
		if ( !isset($options['u']) ) { 
	
			foreach ($event as $key => $value) {
                        	if ( array_key_exists(strtolower($value['event']), $events) ) { 
					$record="$game_id,$client_id,'" . $user . "','" . $version . "','" . $device . "','" . $value['time'] . "'," . $events[strtolower($value['event'])] . ",";
				}
				else {
					$events=add_event(strtolower($value['event']));
					$record="$game_id,$client_id,'" . $user . "','" . $version . "','" . $device . "','" . $value['time'] . "'," . $events[strtolower($value['event'])] . ",";
				}
                                if ( count($value['parameters']) > 0 ) {
                                        foreach ($value['parameters'] as $parameter_key => $parameter_value) {
                        			if ( array_key_exists(strtolower($parameter_key), $parms) ) { 
							fwrite($fhe, $record . "'" . $parms[strtolower($parameter_key)] . "','" . $parameter_value . "'\n");
						}
						else {
							$parms=add_parms(strtolower($parameter_key));
							fwrite($fhe, $record . "'" . $parms[strtolower($parameter_key)] . "','" . $parameter_value . "'\n");
						}
					}
				}
                                else {
					fwrite($fhe, $record . ",\n");
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

    echo "NOTE: Memory Usage: get_file: " . memory_get_usage() . "\n";

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
	
    echo "NOTE: Memory Usage: get_json: " . memory_get_usage() . "\n";
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

    echo "NOTE: JSON return codes: err : $err errmsg : $errmsg header : ";
    echo "NOTE: JSON header as follows:\n";
    print_r($header,TRUE);

    if ($json != false) {
        return json_decode($json, TRUE);
    }
    else {
        return FALSE;
    }
}

?>
