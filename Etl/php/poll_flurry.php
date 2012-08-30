<?php
	ini_set('memory_limit', '512M');
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
		$output_file = "$CSV/output.csv";
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

	$mdb2=db_connect();

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
						debugger("DEBUG: Report Running : Waiting : count=$count.");
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
			   $rc=parse_file($file, $row['game_id'],$row['device_id']);
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

function parse_file($file, $game_id, $device_id) {

    global $start_date;
    global $end_date;

    $offset=0;

    $gz = @gzopen($file, 'rb', $use_include_path);

    if ($gz) {
        $data = '';
        while (!gzeof($gz)) {
            $data .= gzread($gz, 4096);
        }
        gzclose($gz);
    } 

    # Check to make sure we have a valid file
    if ( strpos($data, "<head><title>302 Found</title></head>" ) ) {
	echo "NOTE: " . date("Y-m-d H:i:s") . ": 302 Redirect found for $start_date to $end_date. Waiting...\n";
	return FALSE;
    }

    $offset=strpos($data, 'sessionEvents', $offset);

    if ( $offset != FALSE ) { 
    	$user_start=strpos($data, '{"u":', $offset);
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
		parse_user($str, $game_id, $device_id);
             	$user_start=$user_end;   
        }
	echo "USERS Processed: $user_count users from $start_date to $end_date.\n";
    }
    return TRUE;
}

function parse_user($str, $game_id, $client_id) {

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
		foreach ($event as $key => $value) {
			foreach ($value['parameters'] as $parameter_key => $parameter_value) {
       		 		$record= "$game_id, $client_id,'EVENT','" . $user . "','" . $version . "','" . $device . "','" . $value['time'] . "','" . $value['event'] . "','" .
				         $parameter_key . "','" . $parameter_value . "'\n";
				fwrite($fh, $record);
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
	echo $values;
	echo $index;
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
