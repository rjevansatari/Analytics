<?php
	ini_set('memory_limit', '512M');
	//date_default_timezone_set('America/New_York');

	//Set up debugging

	require_once 'Log.php';
	require_once 'inc/db.php';
	require_once 'inc/config.php';

	//check options
	$options = getopt("ds:e:f:r:k:g:c:o:u");
	if ( array_key_exists('d',$options) ) {
		$debug = Log::singleton('console');
	}

	debugger("getops : " . print_r($options,TRUE));

	if ( $options['o'] ) { 
		$output_file = "$CSV/" . $options['o'];
	}
	else {
		$output_file = "$CSV/fx_rates.csv";
	}

	// Open the output file
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
	$date=$start_date;

	$url_root = "http://openexchangerates.org/api/historical/";
        $app_id="?app_id=71eb67954af746d59393c25d19b35dcb";

	$mdb2=db_connect();

	if ( $date == $end_date ) {
		$url = $url_root . $date . ".json/$app_id";
		$json=get_json($url);
		sleep(1);
		parse_json($json, $output_file, $fh);
	}
	else {

		while ($date != $end_date) {
	
    			$date = date ("Y-m-d", strtotime ("+1 day", strtotime($date)));
			$url = $url_root . $date . ".json/$app_id";
			$json=get_json($url);
			sleep(1);
			parse_json($json, $output_file, $fh);
		}  
	}
	exit;

function parse_json($json, $output_file, $fh) {

	foreach ($json as $key => $value) {
		switch($key) {
			case 'disclaimer':
				break;
			case 'license':
				break;
			case 'timestamp':
				$date=date("Y-m-d",$value);
				break;
			case 'base':
				$base=$value;
				break;
			case 'rates':
				foreach ( $value as $currency => $rate ) {
					fwrite($fh,"$date,$currency,$rate\n");
				}
				break;
		}
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

    debugger("err : $err errmsg : $errmsg header : " . print_r($header,TRUE));

    if ($json != false) {
        $data = json_decode($json, TRUE);
       	return $data;
    }
    else {
        return FALSE;
    }
}

?>
