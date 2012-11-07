<?php
	ini_set('memory_limit', '512M');
	//date_default_timezone_set('America/New_York');

	//Set up debugging

	require_once 'Log.php';
	require_once 'inc/db.php';
	require_once 'inc/config.php';

	//check options
	$options = getopt("s:e:o:");
	//Start and end dates

	$start_date = $options['s'];
	$end_date = $options['e'];

	if ( $options['o'] ) { 
		$output_file = "$CSV/" . $options['o'];
	}
	else {
		$output_file = "$CSV/app_figures_day.csv";
	}

	$app_figures_request = "$CSV/app_figures_request.csv";

	$fh_r = fopen($app_figures_request, 'w') or die ("ERROR: Could not open app figures request file.\n");
	$fh_o = fopen($output_file, 'w') or die ("ERROR: Could not open output file.\n");

	$url = "https://api.appfigures.com/v1.1/sales/products+dates/$start_date/$end_date/?format=csv";

	$ch = curl_init();

	curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC ) ;
	curl_setopt($ch, CURLOPT_USERPWD, "linda.lee@atari.com:sharpie46"); 
	//curl_setopt($ch, CURLOPT_HEADER, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FILE, $fh_r);
	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
	curl_setopt($ch, CURLOPT_URL, $url);

	$json=curl_exec($ch);
	var_dump($json);

	fclose($fh_r);

	$fh_r = fopen($app_figures_request, 'r') or die ("ERROR: Could not open output file.\n");

	$d_found = FALSE;

	$row = fgets($fh_r);

	$row=str_replace(array("\n", "\r"), '', $row);

	while ( !feof($fh_r) ) {

		$cols=explode(",", $row);

		if ( $d_found ) {
			$stat_date=rtrim($cols[8],"\r");
			if ( in_array($cols[7],array(649835,650446,650464,653460)) ) {
				$client_id=1;
				$game_id=21;
			} 
			else if ( $cols[7] == 650448 ) {
				$client_id=1;
				$game_id=24;
			}
			$downloads=$cols[0];
			$updates=$cols[1];
			$revenue=$cols[5];
			$product_id=$cols[7];

			fwrite($fh_o, "'".$stat_date."',$game_id,$client_id,$product_id,$downloads,$updates,$revenue\n");
		}
		else {
			if ( $cols[0] == 'downloads' ) {
				$d_found = TRUE;
			}
		}
		$row = fgets($fh_r);
		$row=str_replace(array("\n", "\r"), '', $row);
	}

	fclose($fh_r);
	fclose($fh_o);

?>
