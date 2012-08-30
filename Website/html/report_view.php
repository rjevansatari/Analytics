<?php
	ini_set('memory_limit', '128M');
	//ini_set("display_errors", "1");
	error_reporting(E_ALL);
	date_default_timezone_set('America/Los_Angeles');

	define('__ROOT__', dirname(dirname(__FILE__))); 
	define('__DEBUG__', TRUE);

	//HTML Table output
	require_once 'HTML/Table.php';

	//Set up debugging
	require_once 'Log.php';

	//XML Parser
	require_once 'XML/Unserializer.php';
	
	// Config
	require_once (__ROOT__.'/inc/db.php');
	require_once (__ROOT__.'/inc/config.php');
	require_once (__ROOT__.'/inc/report_class.php');

	$menu=0;

	// Check to see if we have a menu passed
	if ( isset($_GET['_menu']) ) {
		$menu=$_GET['_menu'];
	}

	$db = db_connect();
	build_nav($menu);

function get_name_from_parent($parent) {

	global $db;

	$sql = "SELECT name
		FROM reporting.navigation
		WHERE id=$parent";

	$result = run_sql($db, $sql);

	$row = $result[0]->fetch_assoc();

	if ( isset($row['name']) ) { 
		return $row['name'];
	}
	return FALSE;
}

function get_menus($level) {

	global $db;
	$menu = array();

	$sql = "SELECT *
		FROM reporting.navigation
		WHERE level=$level
		ORDER by name";

	$result = run_sql($db, $sql);
	while ($row = $result[0]->fetch_assoc()) {
		$menu[$row['name']]=array('id' => $row['id'], 'parent' => $row['parent']);
	}
	return $menu;
}

function get_menu_level($id) {

	global $db;

	$sql = "SELECT level
		FROM reporting.navigation
		WHERE id=$id";

	$result = run_sql($db, $sql);
	$row = $result[0]->fetch_assoc();

	if ( isset($row['level']) ) { 
		return $row['level'];
	}
	else {
		return 0;
	}
		
}

function get_parent($menu) {

	global $db;

	$sql = "SELECT parent
		FROM reporting.navigation
		WHERE id=$menu";

	$result = run_sql($db, $sql);
	$row = $result[0]->fetch_assoc();

	if ( isset($row['parent']) ) { 
		return $row['parent'];
	}
	return FALSE;
}

function has_child($menu) {

	global $db;

	$sql = "SELECT count(*) as count
		FROM reporting.navigation
		WHERE parent=$menu";

	$result = run_sql($db, $sql);
	$row = $result[0]->fetch_assoc();

	if ( isset($row['count']) ) { 
		return $row['count'];
	}
	else {
		return 0;
	}
}

function get_report_from_menu($menu) {

	global $db;

	$sql = "SELECT report
		FROM reporting.navigation
		WHERE id=$menu";

	$result = run_sql($db, $sql);
	$row = $result[0]->fetch_assoc();

	if ( !isset($row['report']) ) { 
		echo "<br>ERROR: Could not get report for menu item $menu.<br>\n";
	}
	
	return $row['report'];

}

function show_report($report) {
	$xml=file_get_contents("../reports/".$report.".xml", TRUE);
	$xml=str_replace("<","&lt;",$xml);
	$xml=str_replace(">","&gt;",$xml);
	$xml=str_replace("\n","<br>",$xml);
	echo "$xml";
}
	
function build_nav($menu=0) {

	global $db;

	// Read the navigation table
	$sql = "SELECT * from reporting.navigation
		ORDER BY level, name";

	$result = run_sql($db, $sql);
	$level = 1+get_menu_level($menu);

	//Read through the result to create the navigation
        require_once(__ROOT__.'/tpl/view_hdr.tpl');

	echo "<table width='100%' class='report'>\n";
	echo "<tr><td width='30%' style='{border: solid 1px; vertical-align: top}'>\n";
	echo "<table width='100%'>\n";
	$results1=get_menus(1);
	$results2=get_menus(2);
	$results3=get_menus(3);

	//Does the menu selected have any other reports?
	
	$top=get_parent($menu);
	if ( $level == 1 ) {
		foreach( $results1 as $key => $value ) {
			echo "<tr><td colspan='3'><a href='report_view.php?_menu=".$value['id']."'>$key</td></tr>\n";
		}
	}
	elseif ( $level == 2 ) {
		foreach( $results1 as $key => $value ) {
			//if ( $value['parent'] == 0 && $value['id'] == $menu ) {
			//	echo "<tr><td colspan='3'><a href='report_view.php'>$key</td></tr>\n";
			//}
			//else {
				echo "<tr><td colspan='3'><a href='report_view.php?_menu=".$value['id']."'>$key</td></tr>\n";
			//}
			foreach ( $results2 as $key2 => $value2 ) {
				if ( $value2['parent'] == $value['id'] && $value['id'] == $menu ) {
					echo "<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;</td><td colspan='2'>";
					//for ($i=0; $i<$level; $i++) {
					//	echo "&nbsp;&nbsp;";
					//}
					echo "<a href='report_view.php?_menu=".$value2['id']."'>$key2</td></tr>\n";
				}
			}
		}
	}
	elseif ( $level == 3 ) {
		foreach( $results1 as $key => $value ) {
			//if ( $value['parent'] == 0 && $value['id'] == $top ) {
			//	echo "<tr><td colspan='3'><a href='report_view.php'>$key</td></tr>\n";
			//}
			//else {
				echo "<tr><td colspan='3'><a href='report_view.php?_menu=".$value['id']."'>$key</td></tr>\n";
			//}
			foreach ( $results2 as $key2 => $value2 ) {
				if ( $value2['parent'] == $value['id'] && $value['id'] == $top ) {
					echo "<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;</td><td colspan='2'>";
					//for ($i=0; $i<$level; $i++) {
					//echo "&nbsp;&nbsp;";
					//}
					echo "<a href='report_view.php?_menu=".$value2['id']."'>$key2</td></tr>\n";
				}
				foreach ( $results3 as $key3 => $value3 ) {
					
					if ( has_child($menu) == 0 ) {
						break;
					}

					if ( $value3['parent'] == $value2['id'] && $value2['parent'] == $value['id'] && $value['id'] == $top ) {
						echo "<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;</td><td>&nbsp;&nbsp;&nbsp;&nbsp;</td><td>";
						//for ($i=0; $i<$level; $i++) {
						//echo "&nbsp;&nbsp;&nbsp;&nbsp;";
						//}
						echo "<a href='report_view.php?_menu=".$value3['id']."'>$key3</td></tr>\n";
					}
				}
			}
		}
	}
		
	echo "</table>\n";
	echo "</td>\n";

	// Report window
	if ( $menu != 0 ) {
		$report=get_report_from_menu($menu);
	}

	echo "<td width='70%' style='{border: solid 1px; vertical-align: top}'>";
	echo "<table>\n";
	if ( $report != '') {
		echo "<tr><td><form action='report_run.php?_report=".$report."' method='get'>
			      <input type='hidden' name='_report' value='".$report."'>
			      <input type='submit' value='Run Report'>
               	       </form><td><tr>";
	}
	echo "<tr><td>";
	
	if ( has_child($menu) == 0 ) {
		//This means we have a report to show!!
		show_report($report);
	}
	else {
		echo '&nbsp;';
	}
	echo "</td></tr>";
	echo "</table>";
	echo "</td></tr>";
	echo "</table>\n";
        require_once(__ROOT__.'/tpl/view_ftr.tpl');
}		
?>
