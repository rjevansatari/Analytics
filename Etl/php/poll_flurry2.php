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
		$output_file = "$CSV/" . $options['o'];
		$report_file = "$REPORT/" . "report_" . basename($output_file, ".csv") . ".gz";
	}
	else {
		$output_file = "$CSV/output.csv";
	}
	$fh = fopen($output_file, 'w') or die ("ERROR: Could not open output file.\n");

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
	if ( array_key_exists('s',$options) ) {
		$start_date = $options['s'];
	}
	else {
		$start_date=date("Y-m-d", strtotime("-1 day"));;
		// Set default start
	}
	if ( array_key_exists('e',$options) ) {
		$end_date = $options['e'];
	}
	else {
		// Set default end
		$end_date=$start_date;
	}

	if ( array_key_exists('k',$options) ) {
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

	$mdb2=db_connect();

	//Get a list of games and API keys
	$sql = "SELECT game_name, game_id, device_id, apikey
		FROM lookups.l_flurry_game
		WHERE 1=1
		AND raw_extract=1
		$apiKey
		ORDER BY game_name";

	$results = run_sql_all($mdb2,$sql);

	# Make All The Requets For Data
	$requests=$results;
	$json_requests=array();

	while ( count($requests) > 0 ) {
	foreach ( $requests as $index => $row ) {

		//Check to see if we have a report
		$url = $url_root . $api_accesscode . $api_key . $row['apikey'] . $start . $end;

		debugger("URL : $url");
		$json=get_json($url);

		if ( $json != FALSE ) {
			foreach ($json as $key => $value) {
				debugger("JSON KEY : $key, JSON VALUE : $value.");
				if ( $key == "code" ) {
				}
				if ( $key == "message" ) {
					echo "NOTE: Report for game " . $row['game_id'] . " already running.\n";
					unset($requests[$index]);
				}
				if ( $key == 'report' ) {
					echo "NOTE: Got report for game " . $row['game_id'] . ".\n";
					$json_requests[]=$json;
					unset($requests[$index]);
				}
			}
		}
		sleep(1);
	}
	}
	print_r($json_requests);
	exit;

	# Now Check for Valid Report Ids
	foreach ( $json_request as $index => $value ) {

		$count=1;

		if ( array_key_exists($value['report']) ) {
			debugger("DEBUG: value : " . print_r($value,TRUE));
			debugger("DEBUG : Got report URL : " . $value['report']['@reportUri']);
			echo "NOTE: " . date("Y-m-d H:i:s") . ": Got report to process for $start_date to $end_date. Waiting...\n";
			$report = get_json($value['report']['@reportUri']);
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

	fclose($fh);

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

function parse_user($str, $game_id, $client_id, $options) {

	global $fh;

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
       		$record= "$game_id, $client_id,'SESSION','" . $user . "','" . $version . "','" . $device . "','" . $datetime->format('Y-m-d H:i:s') . "','" . 'session' . "',,,\n";
		fwrite($fh, $record);

		//EVENTS
		if ( !isset($options['u']) ) { 
			foreach ($event as $key => $value) {
                        	$record="$game_id, $client_id,'EVENT','" . $user . "','" . $version . "','" . $device . "','" . $value['time'] . "','" . $value['event'] . "',";
                                if ( count($value['parameters']) > 0 ) {
                                        foreach ($value['parameters'] as $parameter_key => $parameter_value) {
						fwrite($fh, $record . "'" . $parameter_key . "','" . $parameter_value . "'\n");
					}
				}
                                else {
					fwrite($fh, $record . ",\n");
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
