<?php
	error_reporting(E_ALL);
	ini_set('memory_limit', '3584M');
	//date_default_timezone_set('America/New_York');

	//Set up debugging

	require_once 'Log.php';
	require_once 'inc/db.php';
	require_once 'inc/config.php';

	//check options
	$options = getopt("ds:e:z:r:k:g:c:o:u");
	if ( array_key_exists('d',$options) ) {
		$debug = Log::singleton('console');
	}

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
	
	$fhs = fopen($output_file_sessions, 'w') or die ("ERROR: Could not open session output file.\n");
	$fhe = fopen($output_file_events,   'w') or die ("ERROR: Could not open events output file.\n");
	
	$mdb2=db_connect();
	$events=get_events();

	if ( array_key_exists('z',$options) ) {
		$input_file=$options['z'];
		debugger("Parsing passed file.");
		parse_file($input_file,$options['g'],$options['c'], $options);
		exit;
	}
	if ( array_key_exists('r',$options) ) {
		$resource=get_file($options['r']);
		parse_file($resource,$options['g'],$options['c'], $options);
		exit;
	}

	//Start and end dates
	$start_date = $options['s'];
	$end_date = $options['e'];

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

	//Get a list of games and API keys
	$sql = "SELECT game_name, game_id, device_id, apikey
		FROM lookups.l_flurry_game
		WHERE 1=1
		$apiKey
		ORDER BY game_name";

	$results = run_sql($mdb2,$sql);

	while ($row = $results->fetchRow(MDB2_FETCHMODE_ASSOC)) {
	
		//Check to see if we have a report
		$url = $url_root . $api_accesscode . $api_key . $row['apikey'] . $start . $end;

		debugger("URL : $url");
		$wait_flag=1;
		$count=1;
		$json=get_json($url);

		while ( $wait_flag == 1 ) {
		
			if ( $json != FALSE ) {
				foreach ($json as $key => $value) {
					debugger("JSON KEY : $key, JSON VALUE : $value.");
					if ( $key == "code" ) {
					}
					if ( $key == "message" ) {
						echo "NOTE: Report is already running.  Waiting... : count=$count.\n";
						sleep(120);
						$wait_flag=1;
						$json=get_json($url);
						$count++;
					}
					if ( $key == 'report' ) {
						$wait_flag=0;
					}
				}
			}
			else {
				echo "NOTE: " . date("Y-m-d H:i:s") .  ": Returned JSON was FALSE for $start_date to $end_date! Waiting...\n";
				sleep(120);
				$wait_flag=1;
				$json=get_json($url);
			}
		}

		$count=1;

		if ( $key == 'report' ) {
			debugger("DEBUG: value : " . print_r($value,TRUE));
			debugger("DEBUG : Got report URL : " . $value['@reportUri']);
			echo "NOTE: " . date("Y-m-d H:i:s") . ": Got report to process for $start_date to $end_date. Waiting...\n";
			sleep(60);
			$report = get_json($value['@reportUri']);
			$count=1;
			
			$rc=FALSE;
			
			while ( $rc == FALSE ) { 
			   while ( $report['@reportReady'] == "false"  && $count <= 50 ) { 
			   	   //Wait 60 seconds
			   	   debugger("DEBUG : Report Not Ready : Waiting 120 seconds. Count is $count.");
			   	   echo "NOTE: " . date("Y-m-d H:i:s") . ": Report Not Ready for $start_date to $end_date. Waiting... Count is : $count.\n";
				   sleep(120);
				   $report = get_json($value['@reportUri']);
				   $count++;
			   }

			   debugger("DEBUG : Got the report now.");
			   sleep(60);
			   $file=get_file($value['@reportUri'], $report_file);
			   $rc=parse_file($file, $row['game_id'],$row['device_id'], $options);
			}
			echo "NOTE: " . date("Y-m-d H:i:s") . ": File $file parsing complete for $start_date to $end_date!\n";
		}
		else {
			echo "NOTE: " . date("Y-m-d H:i:s") . ": Could not get a valid report for $file for $start_date to $end_date.\n";
		}
	}

	$mdb2->disconnect();
	fclose($fhs);
	fclose($fhe);

function debugger($msg) {

   global $debug;

   if ( $debug ) {
	$debug->log($msg, PEAR_LOG_DEBUG);
   }
}

function parse_file($file, $game_id, $device_id, $options) {

    global $start_date;
    global $end_date;

    $user_count=0;

    # Do we have a zipped file or not?
    if ( substr($file, -3, 3) == '.gz' ) {
	debugger("Got file, attempting to open it.");
	$gz = @gzopen($file, 'rb');

	    if ($gz) {

		# Check to see if we got a report
	        $buffer = gzread($gz, 4096);
		debugger("Buffer=$buffer\n");
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
					debugger("Buffer While Loop=$buffer\n");
					$user_end=strpos($buffer, '{"u":');
					if ( $user_end == FALSE ) {
						$data .= $buffer;
					}
				}
				$str=$data . substr($buffer,0,$user_end-1);
				debugger("Str1=$str\n");
			}
			else {
				$str=substr($buffer,$user_start,$user_end-$user_start-1);
				debugger("Str2=$str\n");
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
		debugger("user_start=$user_start and user_end=$user_end.");
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

	global $mdb2;

	$sql = "SELECT distinct event_name, event_id
                FROM lookups.l_event_test
		ORDER by 1";

	$results = run_sql($mdb2,$sql);
	$list=array();

	while ($row = $results->fetchRow(MDB2_FETCHMODE_ASSOC)) {
		$list[$row['event_name']] = $row['event_id'];
	}
	return $list;
}

function add_event($event_name) {

	global $mdb2;

	$sql="INSERT INTO lookups.l_event_test(event_name) VALUES ('" . $event_name . "');";
	$rc=$mdb2->exec($sql);

	if ( PEAR::isError($rc)) {
		die ("ERROR: Event update failed : " . $rc->getMessage()); 
	}

	echo "NOTE: New event added : $event_name.\n";
	$latest_events=get_events();

	return $latest_events;
	
}
function parse_user($str, $game_id, $client_id, $options) {

	global $fhs;
	global $fhe;
	global $events;

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
                        	if ( array_key_exists($value['event'], $events) ) { 
					$record="$game_id,$client_id,'" . $user . "','" . $version . "','" . $device . "','" . $value['time'] . "'," . $events[$value['event']] . ",";
				}
				else {
					$events=add_event($value['event']);
				}
                                if ( count($value['parameters']) > 0 ) {
                                        foreach ($value['parameters'] as $parameter_key => $parameter_value) {
						fwrite($fhe, $record . "'" . $parameter_key . "','" . $parameter_value . "'\n");
					}
				}
                                else {
					fwrite($fhe, $record . ",\n");
                                }
			}
		}
		unset($datetime);
	}
}

function parse_events($time, $object) {
	
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
	$result[] = array('event' => $event, 
                          'time' => $datetime->format("Y-m-d H:i:s"),
			  'parameters' => $parameters);
    }
    return $result;

}

function get_file($url, $file='') {

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

    debugger("DEBUG : err : $err errmsg : $errmsg");

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

    debugger("DEBUG : err : $err errmsg : $errmsg header : " . print_r($header,TRUE));

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
