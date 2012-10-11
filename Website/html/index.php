<?php
	ini_set('memory_limit', '128M');
	//ini_set("display_errors", "1");
	error_reporting(E_ALL);
	date_default_timezone_set('America/Los_Angeles');

	define('__ROOT__', dirname(dirname(__FILE__))); 
	define('__DEBUG__', TRUE);

	$type='';
	$parms='';

	if ( isset($_GET['_type']) ) {
		$type=$_GET['_type'];
		unset($_GET['_type']);

		foreach ( $_GET as $parm => $value ) {
			if ( isset($parms) ) {
				$parms .= "&"."$parm=$value";
			}
			else {
				$parms = "$parm=$value";
			}
		}
	}

	if ( $type == '' ) {
		$navHtml="report_menu.php";
		$reportHtml="report_view.php?_report=daily_stats";
	}
	else {
		$navHtml="report_menu.php?$parms";
		$reportHtml="report_view.php?$parms";
	}

	require_once(__ROOT__.'/tpl/index.tpl');
?>
