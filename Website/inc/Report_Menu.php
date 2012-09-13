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
        	$row = $result[0]->fetch_assoc();

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
        	$row = $result[0]->fetch_assoc();

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
        	$row = $result[0]->fetch_assoc();

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
        	$row = $result[0]->fetch_assoc();

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
        	$row = $result[0]->fetch_assoc();

        	if ( !isset($row['report']) ) {
                	echo "<br>ERROR: Could not get report for menu item $menu.<br>\n";
        	}

        	return $row['report'];
	}

	function showReport($report) {
        	$xml=file_get_contents("../reports/".$report.".xml", TRUE);
        	$xml=str_replace("<","&lt;",$xml);
        	$xml=str_replace(">","&gt;",$xml);
        	$xml=str_replace("\n","<br>",$xml);
        	echo "$xml";
	}


	function toHTML() {

	        //Read through the result to create the navigation
		require_once(__ROOT__.'/tpl/view_hdr.tpl');

        	echo "<table width='100%' class='report'>\n";
        	echo "<tr><td width='2%'>&nbsp;</td>\n";
        	echo "<td width='15%' style='{border: solid 1px; vertical-align: top}'>\n";
        	echo "<table width='100%'>\n";

		if ( $this->level == 1 ) {
			foreach( $this->levels[0]->menu as $key => $value ) {
				echo "<tr><td colspan='3'><a href='report_view_obj.php?_menu=";
				if ( $this->hasChild($value['id']) ) { 
					echo $value['id']."'>+ $key</td></tr>\n";
				}
				else {
					echo $value['id']."'>$key</td></tr>\n";
				}
			}
		}
		elseif ( $this->level == 2 ) {
			foreach( $this->levels[0]->menu as $key => $value ) {
				echo "<tr><td colspan='3'><a href='report_view_obj.php?_menu=";
				if ( $this->hasChild($value['id']) && $value['id'] !=  $this->menu ) { 
					echo $value['id']."'>+ $key</td></tr>\n";
				}
				elseif ( $this->hasChild($value['id']) && $value['id'] ==  $this->menu ) { 
					echo $value['id']."'>o $key</td></tr>\n";
				}
				else {
					echo $value['id']."'>$key</td></tr>\n";
				}
				foreach ( $this->levels[1]->menu as $key2 => $value2 ) {
					if ( $this->hasChild($value2['id']) && $value2['parent'] == $value['id'] && $value['id'] == $this->menu ) {
					// Check if this entry has a report if so, include the report in the URI
						echo "<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;</td><td colspan='2'>";
						echo "<a href='report_view_obj.php?_menu=".$value2['id']."'>+ $key2</td></tr>\n";
					}
					elseif ( $value2['parent'] == $value['id'] && $value['id'] == $this->menu ) {
						echo "<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;</td><td colspan='2'>";
						echo "<a href='report_view_obj.php?_report=".$this->getReportFromMenu($value2['id'])."'>$key2</td></tr>\n";
					}
				}
			}
		}
		elseif ( $this->level == 3 ) {
			foreach( $this->levels[0]->menu as $key => $value ) {
				echo "<tr><td colspan='3'><a href='report_view_obj.php?_menu=";
				if ( $this->hasChild($value['id']) && $this->getParent($this->menu) == $value['id']  ) { 
					echo $value['id']."'>o $key</td></tr>\n";
				}
				elseif ( $this->hasChild($value['id']) ) { 
					echo $value['id']."'>+ $key</td></tr>\n";
				}
				else {
					echo $value['id']."'>$key</td></tr>\n";
				}
				foreach ( $this->levels[1]->menu as $key2 => $value2 ) {
					if ( $this->hasChild($value2['id']) && $value2['parent'] == $value['id'] && $value['id'] == $this->top && $value2['id'] != $this->menu ) {
						echo "<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;</td><td colspan='2'>";
						echo "<a href='report_view_obj.php?_menu=".$value2['id']."'>+ $key2</td></tr>\n";
					}
					elseif ( $this->hasChild($value2['id']) && $value2['parent'] == $value['id'] && $value['id'] == $this->top && $value2['id'] == $this->menu ) {
						echo "<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;</td><td colspan='2'>";
						echo "<a href='report_view_obj.php?_menu=".$value2['id']."'>o $key2</td></tr>\n";
					}
					elseif ( $value2['parent'] == $value['id'] && $value['id'] == $this->top ) {
						echo "<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;</td><td colspan='2'>";
						echo "<a href='report_view_obj.php?_report=".$this->getReportFromMenu($value2['id'])."'>$key2</td></tr>\n";
					}
					foreach ( $this->levels[2]->menu as $key3 => $value3 ) {
						
						if ( $value3['parent'] == $value2['id'] && $value2['parent'] == $value['id'] && $value['id'] == $this->top ) {
							echo "<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;</td><td>&nbsp;&nbsp;&nbsp;&nbsp;</td><td>";
							echo "<a href='report_view_obj.php?_report=".$this->getReportFromMenu($value3['id'])."'>$key3</td></tr>\n";
						}
					}
				}
			}
		}
			
		echo "</table>\n";
		echo "</td>\n";
		echo "<td width='2%'>&nbsp;</td>\n";
		echo "<td style='{border: solid 1px; vertical-align: top}'>";
		echo "<table>\n";
	
		if ( $this->reportName != '') {
			echo "<tr><td width='10%'><form action='report_run.php?_report=".$this->reportName."' method='get'>
				<input type='hidden' name='_report' value='".$this->reportName."'>
				<input type='submit' value='Run'>
				</form>
				<td width='10%'><form action='report_log.php?_report=".$this->reportName."' method='get'>
				<input type='hidden' name='_report' value='".$this->reportName."'>
				<input type='submit' value='History'>
				</td><td></td><tr>";
		}
		echo "<tr><td colspan='3'>";
	
		if ( $this->reportName != '' )  {
			//This means we have a report to show!!
			$this->showReport($this->reportName);
		}
		else {
			echo '&nbsp;';
		}
		echo "</td></tr>";
		echo "</table>";
		echo "</td><td width='2%'>&nbsp;</td></tr>\n";
		echo "</table>\n";
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

		while ($row = $result[0]->fetch_assoc()) {
                	$this->menu[$row['name']]=array('id' => $row['id'], 'parent' => $row['parent']);
        	}
	}

	function setDB($db) {
		$this->db = $db;
	}
}

?>
