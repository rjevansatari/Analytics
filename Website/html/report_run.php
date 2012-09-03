<?php
	namespace gchart;

	ini_set('memory_limit', '128M');
	ini_set("display_errors", "1");
	error_reporting(E_ALL);
	date_default_timezone_set('America/Los_Angeles');

	define('__ROOT__', dirname(dirname(__FILE__))); 
	define('__DEBUG__', TRUE);

	//Google Chart API
	require (__ROOT__.'/inc/gChartInit.php');

	//HTML Table output
	require_once 'HTML/Table.php';

	//Set up debugging
	require_once 'Log.php';

	//XML Parser
	require_once 'XML/Unserializer.php';
	
	// Config
	require_once (__ROOT__.'/inc/db.php');
	require_once (__ROOT__.'/inc/config.php');
	//require_once (__ROOT__.'/inc/report_class.php');

	// If no parameters passed, set default report for testing
	if ( !isset($_GET['_report']) ) {

		$report_name="mobile_dau_by_date";
		$parms=array('gameid' =>1, 'clientid' => 1, 'startdate' => '2012-08-01', 'enddate' => '2012-08-25');
		//$report_name="mobile_retention_from_install_date";
		//$parms=array('gameid' =>1, 'clientid' => 1, 'startdate' => "'2012-08-20'");
	}
	else {

		$report_name=$_GET['_report'];
		$parms=report_parms($_GET);
		unset($parms['_report']);
	}

	// Parse the report
	$xml =new \XML_Unserializer();
	$xml->setOption(XML_UNSERIALIZER_OPTION_ATTRIBUTES_PARSE, TRUE);
	$xml->unserialize(__ROOT__ ."/reports/$report_name".".xml", TRUE);
	if (\PEAR::isError($xml)) {
                        die("ERROR: XML : Report Name: $report_name : MSG: " . $xml->getMessage());
        }

	$xml_result = $xml->getUnserializedData();
	if (\PEAR::isError($xml_result)) {
                        die("ERROR: XML : Report Name: $report_name : MSG: " . $xml_result->getMessage());
        }  

	$db = db_connect();

	process_report($xml_result, $parms, $report_name);

function get_report_parms($xml, $report) {

	//Number of date parms - we need to track this.

	$dates=0;
	if ( !isset($xml['parm']) ) { 
		return FALSE;
	}
	else {
		require_once(__ROOT__.'/tpl/parms_hdr.tpl');
		$parms = $xml['parm'];
		if ( isset($parms['0']) ) {

			foreach ( $parms as $key => $value ) {
				echo "<tr>";
				process_report_parm($value, $dates);
				echo "</tr>";
				
				
			}
		}
		else {
			echo "<tr>";
			process_report_parm($parms, $dates);
			echo "</tr>";
		}
		require_once(__ROOT__.'/tpl/parms_ftr.tpl');
	}
	return TRUE;
}


function process_report_parm($parm, &$dates) {
	
	global $db;

	if ( !isset($parm['name']) || !isset($parm['type']) ) {
		return FALSE;
	}

	if ( $parm['type'] == "query" ) {
		echo "<td>".$parm['text'].":</td><td>&nbsp;&nbsp;<select name='" . $parm['name'] ."' style='{border: solid 1px}'>\n"; 
		if ( $result = run_sql($db, $parm['_content']) ) {

        		while ($row = $result[0]->fetch_assoc()) {
				if ( isset($parm['default']) ) {
					if ( $parm['default'] == $row[$parm['display']] ) {
						echo "<option value='" . $row[$parm['value']] . "' select='selected'>" . $row[$parm['display']] . "</option>\n";
					} 	
				}
				else {
					echo "<option value='" . $row[$parm['value']] . "'>" . $row[$parm['display']] . "</option>\n";
				}
			}
			echo "</select></td>\n";
		}
	}
	elseif ( $parm['type'] == "select" ) {
		echo "<td>".$parm['text'].":</td><td>&nbsp;&nbsp;<select name='" . $parm['name'] ."' style='{border: solid 1px}'>\n"; 
		//Process option tags
		if ( isset($parm['option']['0']) ) {
			foreach ($parm['option'] as $option => $value) {
				if ( $value['_content'] == $parm['default']) { 
					echo "<option value='" . $value['value'] . "' select='selected'>" . $value['_content'] . "</option>\n";
				}
				else {
					echo "<option value='" . $value['value'] . "'>" . $value['_content'] . "</option>\n";
				}
			}
		}
		else {
			echo "<option value='" . $parm['value'] . "'>" . $parm['_content'] . "</option>\n";
		}
		echo "</select></td>\n";
	}
	elseif ( $parm['type'] == "date" ) {
		$dates++;
		//Process date parameters
		echo "<td>".$parm['text'].":</td><td>&nbsp;&nbsp;";
		echo "<input type='text' style='{border: solid 1px}' name='".$parm['name']."' id='cal1Date".$dates."' autocomplete='off' size='20' value='' /></td>\n";
	}
	else {
		return FALSE;
	}
}

function process_report($xml, $parms, $report_name) {

	$report=array();
	$sql=array();
	$query=array();
	$format=array();
	$result=array();

	if ( count($parms) == 0 ) {
		if ( get_report_parms($xml, $report_name) == TRUE ) {
			exit;
		}
	} 

	foreach ( $xml as $key => $value ) {
		if ( $key == 'title' ) {
			$title=$value;
		}
		if ( $key == 'description' ) {
			$description=$value;
		}
		if ( $key == 'query' ) {

			if ( isset($value['_content']) ) {
				$sql[$value['name']] = $value['_content'];
				$query[$value['name']]=$value['title'];
			}
			else {
				foreach ( $value as $key_sql => $value_sql ) {
					$sql[$value_sql['name']] = $value_sql['_content'];
					$query[$value_sql['name']]=$value_sql['title'];
				}
			}
		}
		if ( $key == 'column' ) {
			$format = $value;
		}
	}

	require_once(__ROOT__.'/tpl/report_hdr.tpl');

	$timestamp=date('Ymdhis');

	$chartData='';

	foreach ( $sql as $sql_key => $sql_value ) {
		$result = run_query($sql_value, $parms);
		if ( $result ) {
			foreach ( $result as $key => $value ) {	 
				if ( $value ) {
					$html.=build_html($html, $chartData, $query, $sql_key, $value, $report_name, $title, $description, $parms, $format, $timestamp);
				}
			}
		}
	}
	echo $chartData;
	echo "</script>\n";
	echo $html;
	require_once(__ROOT__.'/tpl/report_ftr.tpl');
}

function report_parms($get) {
	
	$result = array();

	foreach ($_GET as $key=>$value) {
		if ( $key != "report" ) {
			$result[$key] = $value;
		}
	}

	return $result;

}

function run_query($sql, $parms) {

	global $db;
	$result=array();

	foreach ($parms as $key=>$value) {
		$sql=str_replace("$".$key, "$value", $sql);
	}	

	$result = run_sql($db, $sql);

	return $result;
}

function build_html(&$html, &$chartData, $query, $sql_key, $result, $report, $title, $description, $parms, $format='', $timestamp) {

	//Create EXCEL output object
	$file_name = "$report"."_".$timestamp.".csv";
	if ( file_exists("/tmp/$file_name") ) {
		$csv = fopen("/tmp/$file_name","a") or die ("ERROR: Could not open file...");
	}
	else {
		$csv = fopen("/tmp/$file_name","w") or die ("ERROR: Could not open file...");
	}

	fwrite($csv,"$title\n");
	fwrite($csv,"$report\n");
	fwrite($csv,"$description\n");
	fwrite($csv,"\n");
        fwrite($csv,"Parameters Passed:\n");
        foreach ( $parms as $key => $value ) {
        	fwrite($csv, "$key,$value\n");
        }
	fwrite($csv,"\n");

	$column_format = array();
	
	//$attrs = array('width' => '600', 'border' => '1', 'class' => 'report');
	$attrs = array('border' => '1', 'class' => 'report');
	$table = new \HTML_Table($attrs);
	$table->setAutoGrow(true);
	$hrAttrs = array('bgcolor' => 'silver', 'align' => 'center');
	$table->setRowAttributes(0, $hrAttrs, true);
	$hrAttrs = array('align' => 'right');

	foreach ($format as $key => $value) {
		
		if ( "$key" == "name") {
			$column_format[strtolower($value)]=$format['format'];
			break;
		}
		else {
			foreach ($value as $key2 => $value2) {
				$column_format[strtolower($value2)]=$value['format'];
				break;
			}
		}
	}

	$record=0;

	$chartData="function drawChart() {
                    var data = google.visualization.arrayToDataTable([\n";

	while ($row = $result->fetch_assoc()) {

		if ( $record > 0 ) {
			$col=0;
			$table->setCellContents($record+1, $col, $record+1);	
			$table->setRowAttributes($record+1, $hrAttrs, true);

			$chartData.=",\n";

			foreach ($row as $key => $value) {
				$col++;
				if ( isset($column_format[strtolower($key)]) ) {
					$fmt_value=format_column($value, $column_format[strtolower($key)]);
					$table->setCellContents($record+1, $col, $fmt_value);	

					// If we have a format, check its type
					if ( in_array($column_format[strtolower($key)], array('string','date')) ) {
						if ( $col == 1 ) { 
							$chartData.="['".$value."'";
						}
						else {
							$chartData.=",'".$value."'";
						}
					}
					else {
						if ( $col == 1 ) { 
							$chartData.="[".$value;
						}
						else {
							$chartData.=",".$value;
						}
					}
				}
				else {
					// If not format, we assume its a number
					$table->setCellContents($record+1, $col, $value);	
					if ( $col == 1 ) { 
						$chartData.=$value;
					}
					else {
						$chartData.=",".$value;
					}
				}
				//CSV
				if ( $col == 1 ) {
					fwrite($csv, "$value");
				}
				else {
					fwrite($csv,",$value");
				}
				//Chart
				
			}
			fwrite($csv,"\n");
			$chartData.="]";
                }
		else if ( $record == 0 ) {
			$col=0;
			$table->setHeaderContents(0, $col, '#');
			foreach ($row as $key => $value) {
				$col++;
				$table->setHeaderContents($record, $col, ucfirst($key));
				if ( $col == 1 ) {
					fwrite($csv, "$key");
					$chartData.="['".ucfirst($key)."'";
				}
				else { 
					fwrite($csv,",$key");
					$chartData.=",'".ucfirst($key)."'";
				}
			}
			fwrite($csv,"\n");
			$chartData.="],\n";

			$col=0;

			$table->setCellContents($record+1, $col, $record+1);	
			$table->setRowAttributes($record+1, $hrAttrs, true);
			foreach ($row as $key => $value) {
				$col++;
				if ( isset($column_format[strtolower($key)]) ) {
					$fmt_value=format_column($value, $column_format[strtolower($key)]);
					$table->setCellContents($record+1, $col, $fmt_value);	

					// If we have a format, check its type
					if ( in_array($column_format[strtolower($key)], array('string','date')) ) {
						if ( $col == 1 ) { 
							$chartData.="['".$value."'";
						}
						else {
							$chartData.=",'".$value."'";
						}
					}
					else {
						if ( $col == 1 ) { 
							$chartData.="[".$value;
						}
						else {
							$chartData.=",".$value;
						}
					}
				}
				else {
					// If not format, we assume its a number
					$table->setCellContents($record+1, $col, $value);	
					if ( $col == 1 ) { 
						$chartData.="[".$value;
					}
					else {
						$chartData.=",".$value;
					}
				}
				// CSV
				if ( $col == 1 ) { fwrite($csv, "$value"); }
				else { fwrite($csv,",$value"); }
			}
			fwrite($csv,"\n");
			$chartData.="]";
                }

		$record++;

	}

	$chartData.="\n]);\n";
        $chartData.="\n
var options = {
title: '".$sql_key."'
};

var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
   chart.draw(data, options);
}
</script>";

	fwrite($csv,"\n");
	fclose($csv);

	$html.="<body class='report'>\n";

      	$html.="<h1>$title</h1>\n";
	$html.="<p>".str_replace("\n","<br>",$description)."</p>\n";
	$html.="<h2>Report Name: $report</h2>\n";

	$html.= "<h3>Parameters Passed:</h3>\n";
	$html.="<table>\n";
	foreach ( $parms as $key => $value ) {
		$html.="<tr><td>$key</td><td>$value</td></tr>\n";
	}
	$html.="</table>\n";
	$html.="<br>\n";
	$html.="<form method='post' action='report_download.php'>\n";
       	$html.="<input name='report' type='hidden' value='" . $file_name . "'>\n";
	$html.="<input type='submit' value='Download CSV'>\n";
	$html.="</form>\n";
	$html.="<h3>".$sql_key." - ".$query[$sql_key]."</h3>\n";
	$html.="<table><tr><td style='{vertical-align:top}'>\n";
	$html.=$table->toHtml()."</td>";
	$html.="<td style='{vertical-align:top}'><div id='chart_div' style='width: 900px; height: 600px;'></div><td>";
	$html.="</tr>\n</table>";
}

function format_column($value, $format) {

	switch ($format) {
		case 'number':
			$value=number_format($value);
		break;
		case 'percent':
			$value=number_format($value,2)."%";
		break;
		case 'percent(1)':
			$value=number_format($value,1)."%";
		break;
		case 'percent(2)':
			$value=number_format($value,2)."%";
		break;
		case 'percent(3)':
			$value=number_format($value,3)."%";
		break;
		case 'decimal':
			$value=number_format($value,2,'.',',');
		break;
		case 'currency':
			$value="$".number_format($value,2,'.',',');
		break;
	}
	return $value;
}	
?>
