<?php
//
class Menu {

	public $db;
	public $levels;
	public $level;
	public $reportName;
	public $menu;

	function __construct($report, $menu, $db) {

		$this->reportName = $report;
		$this->menu = $menu;
		$this->db = $db;
		$this->level=$this->getLevel();

        	if ( $this->reportName != '' ) {
                	$this->level = $this->level-1;
                	$this->menu = $this->getParent($this->getMenuFromReport($this->reportName));
        	}


		// Create the levels
		$this->levels[] = new Level(1);
		$this->levels[] = new Level(2);
		$this->levels[] = new Level(3);

		foreach ( $this->levels as $key => $level ) {
			$level->setDB($this->db);
			$level->buildMenu();
		}

		$this->top = $this->getParent($this->menu);
	}

	function getParent($menu) {

	        $sql = "SELECT parent
                	FROM reporting.navigation
                	WHERE id=$menu";

        	$result = run_sql($this->db, $sql);
        	$row = db_fetch_assoc($result[0]);

        	if ( isset($row['parent']) ) {
                	return $row['parent'];
        	}
        	return FALSE;
	}

	function hasChild($menu) {

	        $sql = "SELECT count(*) as count
                	FROM reporting.navigation
                	WHERE parent=$menu";

        	$result = run_sql($this->db, $sql);
        	$row = db_fetch_assoc($result[0]);

        	if ( isset($row['count']) ) {
                	if ( $row['count'] > 0 ) {
                        	return TRUE;
                	}
                	else{
                        	return FALSE;
                	}
        	}
        	else {
                	return FALSE;
        	}
	}


	function getLevel() {


        	if ( $this->reportName !='' ) {
			$sql = "SELECT level
				FROM reporting.navigation
                		WHERE report='".$this->reportName."';";
        	}
	        elseif ( $this->menu !=0 )  {
                	$sql = "SELECT level
                	FROM reporting.navigation
                	WHERE id=$this->menu;";
        	}
        	else {
                	return 1;
        	}

	        // Process the results and return the level
        	$result = run_sql($this->db, $sql);
        	$row = db_fetch_assoc($result[0]);

	        if ( isset($row['level']) ) {
	        	return $row['level']+1;
	        }
	        else {
	                return 0;
	        }

	}


	function getMenuFromReport($report) {

        	$sql = "SELECT id
                	FROM reporting.navigation
                	WHERE report='".$report."';";

        	$result = run_sql($this->db, $sql);
        	$row = db_fetch_assoc($result[0]);

        	if ( !isset($row['id']) ) {
                	echo "<br>ERROR: Could not get report for menu item $menu.<br>\n";
        	}

        	return $row['id'];
	}

	function getReportFromMenu($menu) {

        	$sql = "SELECT report
                	FROM reporting.navigation
                	WHERE id=$menu";

        	$result = run_sql($this->db, $sql);
        	$row = db_fetch_assoc($result[0]);

        	if ( !isset($row['report']) ) {
                	echo "<br>ERROR: Could not get report for menu item $menu.<br>\n";
        	}

        	return $row['report'];
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
				echo "<td><a href='report_log.php?_report=".$row['report_name']."&cache=".$row['report_startts']."'>".$row['report_startts']."</a></td>\n";
			}
			else {
				echo "<td><a href='report_log.php?_report=".$report."&cache=".$row['report_startts']."'>".$row['report_startts']."</a></td>\n";
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
		require_once(__ROOT__.'/tpl/view_hdr.tpl');

		echo "<tr><td style='{width:2%}'>&nbsp;</td>\n";
        	echo "<td style='{width:18%; border: solid 2px black; vertical-align: top}'> \n";
        	echo "<table id='nav' class='nav' style='{width:100%}'>\n";

		if ( $this->level == 1 ) {
			foreach( $this->levels[0]->menu as $key => $value ) {
				echo "<tr><td class='nav'><a href='report_view.php?_menu=";
				if ( $this->hasChild($value['id']) ) { 
					echo $value['id']."'>+ $key</a></td></tr>\n";
				}
				else {
					echo $value['id']."'>$key</a></td></tr>\n";
				}
			}
		}
		elseif ( $this->level == 2 ) {
			foreach( $this->levels[0]->menu as $key => $value ) {
				echo "<tr><td class='nav'><a href='report_view.php?_menu=";
				if ( $this->hasChild($value['id']) && $value['id'] !=  $this->menu ) { 
					echo $value['id']."'>+ $key</a></td></tr>\n";
				}
				elseif ( $this->hasChild($value['id']) && $value['id'] ==  $this->menu ) { 
					echo $value['id']."'>o $key</a></td></tr>\n";
				}
				else {
					echo $value['id']."'>$key</a></td></tr>\n";
				}
				foreach ( $this->levels[1]->menu as $key2 => $value2 ) {
					if ( $this->hasChild($value2['id']) && $value2['parent'] == $value['id'] && $value['id'] == $this->menu ) {
					// Check if this entry has a report if so, include the report in the URI
						echo "<tr><td class='nav'>&nbsp;&nbsp;&nbsp;&nbsp;<a href='report_view.php?_menu=".$value2['id']."'>+ $key2</a></td></tr>\n";
					}
					elseif ( $value2['parent'] == $value['id'] && $value['id'] == $this->menu ) {
						echo "<tr><td class='nav'>&nbsp;&nbsp;&nbsp;&nbsp;<a href='report_view.php?_report=".$this->getReportFromMenu($value2['id'])."'>$key2</a></td></tr>\n";
					}
				}
			}
		}
		elseif ( $this->level == 3 ) {
			foreach( $this->levels[0]->menu as $key => $value ) {
				echo "<tr><td class='nav'><a href='report_view.php?_menu=";
				if ( $this->hasChild($value['id']) && $this->getParent($this->menu) == $value['id']  ) { 
					echo $value['id']."'>o $key</a></td></tr>\n";
				}
				elseif ( $this->hasChild($value['id']) ) { 
					echo $value['id']."'>+ $key</a></td></tr>\n";
				}
				else {
					echo $value['id']."'>$key</a></td></tr>\n";
				}
				foreach ( $this->levels[1]->menu as $key2 => $value2 ) {
					if ( $this->hasChild($value2['id']) && $value2['parent'] == $value['id'] && $value['id'] == $this->top && $value2['id'] != $this->menu ) {
						echo "<tr><td class='nav'>&nbsp;&nbsp;&nbsp;&nbsp;<a href='report_view.php?_menu=".$value2['id']."'>+ $key2</a></td></tr>\n";
					}
					elseif ( $this->hasChild($value2['id']) && $value2['parent'] == $value['id'] && $value['id'] == $this->top && $value2['id'] == $this->menu ) {
						echo "<tr><td class='nav'>&nbsp;&nbsp;&nbsp;&nbsp;<a href='report_view.php?_menu=".$value2['id']."'>o $key2</a></td></tr>\n";
					}
					elseif ( $value2['parent'] == $value['id'] && $value['id'] == $this->top ) {
						echo "<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;<a href='report_view.php?_report=".$this->getReportFromMenu($value2['id'])."'>$key2</a></td></tr>\n";
					}
					foreach ( $this->levels[2]->menu as $key3 => $value3 ) {
						
						if ( $value3['parent'] == $this->menu && $value3['parent'] == $value2['id'] && $value2['parent'] == $value['id'] && $value['id'] == $this->top ) {
							echo "<tr><td class='nav'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href='report_view.php?_report=".$this->getReportFromMenu($value3['id'])."'>$key3</a></td></tr>\n";
						}
					}
				}
			}
		}
			
		echo "</table>\n";
		echo "</td>\n";
		echo "<td style='{width:2%}'>&nbsp;</td>\n";
		echo "<td style='{width:78%}'>\n";
	
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

class Level extends Menu
{
	public $menu;
	public $level;

	function __construct($level) {

		if ( !isset($level) ) {
			//Top Level
			$this->level=0;
		}
		else {
			$this->level=$level;
		}

	}

	function buildMenu() {
		//Build Menu for this level

	        $sql = "SELECT *
       	         FROM reporting.navigation
       	         WHERE level=$this->level
       	         ORDER by name";

	        $result = run_sql($this->db, $sql);

		while ($row = db_fetch_assoc($result[0])) {
                	$this->menu[$row['name']]=array('id' => $row['id'], 'parent' => $row['parent']);
        	}
	}

	function setDB($db) {
		$this->db = $db;
	}
}

?>
