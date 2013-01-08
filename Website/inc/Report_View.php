<?php
//
class Report_View {

	public $db;
	public $levels;
	public $level;
	public $reportName;

	function __construct($report, $db) {

		$this->reportName = $report;
		$this->db = $db;
	}

	function showReportSQL($report) {
        	$xml=file_get_contents("../reports/".$report.".xml", TRUE);
        	$xml=str_replace("<","&lt;",$xml);
        	$xml=str_replace(">","&gt;",$xml);
        	$xml=str_replace("\n","<br>",$xml);
        	echo "$xml";
	}

	function showReport($report) {

		if ( isset($report) ) {

	                $xml =new \XML_Unserializer();
                	$xml->setOption(XML_UNSERIALIZER_OPTION_ATTRIBUTES_PARSE, TRUE);
                	$xml->unserialize(__ROOT__ ."/reports/".$report.".xml", TRUE);
                	if (\PEAR::isError($xml)) {
                        	die("ERROR: XML : Report Name: ".$report." : MSG: " . $xml->getMessage());
                	}

                	$this->XML=$xml->getUnserializedData();

                	if (\PEAR::isError($xml)) {
                        	die("ERROR: XML : Report Name: ".$report." : MSG: " . $xml->getMessage());
                	}

			//Now get the tags we need to show the report info
			foreach ( $this->XML as $key => $value ) {
                		switch($key) {
                		case 'title':
        				$value=str_replace("\n","<br>",$value);
					echo "<br><h1>$value</h1>\n";
				break;
                		case 'description':
        				$value=str_replace("\n","<br>",$value);
					echo "<p>$value</p>\n";
				break;
				}
			}

		}
		echo "<br><h1>Previous Report Runs:</h1><br>\n";

		if ( isset($report) ) {
			$sql = "SELECT * from reporting.report_log
				WHERE report_name='".$report."'
				ORDER by report_startts desc";	
		}
		else {
			$sql = "SELECT * from reporting.report_log
				ORDER by report_startts desc
				LIMIT 50";	
		}

		$result = run_sql($this->db, $sql);

		echo "\n<table class='log' width='1000px'>\n";
		echo "<tr>\n";
		if ( $report == '' ) {
			echo "<th width='15%'>&nbsp;Report Name</th>\n";
			echo "<th width='20%'>&nbsp;Time Stamp</th>\n";
			echo "<th width='50%'>&nbsp;Parameters</th>\n";
			echo "<th width='15%'>&nbsp;Status</th>\n";
		}
		else {
			echo "<th width='20%'>&nbsp;Time Stamp</th>\n";
			echo "<th width='65%'>&nbsp;Parameters</th>\n";
			echo "<th width='15%'>&nbsp;Status</th>\n";
			echo "</tr>\n";
		}

		while ( $row = db_fetch_assoc($result[0]) ) {

			echo "<tr>";

			if ( $report == '' ) {
				echo "<td><a href='report_view.php?_report=".$row['report_name']."'>".$row['report_name']."</a></td>\n";
				echo "<td><a href='report_log.php?_report=".$row['report_name']."&_cache=".$row['report_startts']."'>".$row['report_startts']."</a></td>\n";
			}
			else {
				echo "<td><a href='report_log.php?_report=".$report."&_cache=".$row['report_startts']."'>".$row['report_startts']."</a></td>\n";
			}

			echo "<td>".$row['report_parms']."</td>\n";
			if ( $row['report_code'] == 0 ) { 
				echo "<td style='{background-color: yellowgreen; vertical-align: top}'>Complete</td></tr>\n";
			}
			elseif ( $row['report_code'] == 4 ) {
				echo "<td style='background-color: orange; {vertical-align: top}'>Running</td></tr>\n";
			}
		}

		echo "</table>\n";
	}

	// This function pulls the info from daily_stats.html so we can display it in the main menu
	function getDailyStats() {
		$sql = "SELECT report_html, report_parms
			FROM reporting.report_log
			WHERE report_name='daily_stats'
			AND report_code=0
			AND report_endts IS NOT NULL
			ORDER BY report_endts DESC
			LIMIT 1";

		$result = run_sql($this->db, $sql);
		$row = db_fetch_assoc($result[0]);
		$html = $row['report_html'];
		//$date = substr($row['report_parms'],strpos($row['report_parms'],'date=')+5,10);
		
		// Get the google chart data
		$sp1 = strpos($html, "<!-- GOOGLE CHART DATA START -->")+strlen("<!-- GOOGLE CHART DATA START -->");
		$sp2 = strpos(substr($html, $sp1), "<!-- GOOGLE CHART DATA END -->");
		$chartData=substr($html, $sp1, $sp2);
		$sp1 = strpos($html, "<div class='parms_passed'>");
		$sp2 = strpos(substr($html, $sp1), "</body>")-1;
		$tableData=substr($html, $sp1, $sp2);
		
		return array('chart' => $chartData, 'table' => $tableData);

	}

	function toHTML() {


	        //Read through the result to create the navigation
		if ( $this->reportName == '') {
			$dailyStats=$this->getDailyStats();
		}

		require_once(__ROOT__.'/tpl/view_hdr_new.tpl');
		echo "<body class=\"yui-skin-sam\">";

		if ( $this->reportName != '') {
			echo "<table>\n";
			//echo "<tr><td style='{width:10%}'>\n";
			echo "<tr><td>\n";
			echo "<form action='report_run.php?_report=".$this->reportName."' method='get'>";
			echo "<input type='hidden' name='_report' value='".$this->reportName."'>\n";
			echo "<input type='submit' value='Run'>\n";
			echo "</form>\n";
			echo "</td></tr>\n<tr><td>";
			//This means we have a report to show!!
			$this->showReport($this->reportName);
			echo "</td></tr>\n";
			echo "</table>\n";
		}
		else {
			echo $dailyStats['table'];
		}
		echo "</td></tr>\n</table>\n";
		require_once(__ROOT__.'/tpl/view_ftr.tpl');
	}
	// This function sets the parameter to HTML so it can be selected
}

?>
