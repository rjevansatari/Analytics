<?php
	ini_set('memory_limit', '128M');
	error_reporting(E_ALL);
	date_default_timezone_set('America/Los_Angeles');

	define('__ROOT__', dirname(dirname(__FILE__))); 
	define('__DEBUG__', TRUE);


	//Set up debugging

	require_once 'Log.php';
	require_once (__ROOT__.'/inc/db.php');
	require_once (__ROOT__.'/inc/config.php');

?>
<html>
  <head>
<?php
	$AND="";
	if ( isset($_GET['gameid']) ) {
		$gameid=$_GET['gameid'];
		$AND = "AND game_id=$gameid";
	}
	if ( isset($_GET['clientid']) ) {
		$clientid=$_GET['clientid'];
		$AND = $AND . " AND client_id=$clientid";
	}
	if ( isset($_GET['startdate']) ) {
		$startdate=$_GET['startdate'];
	}
	if ( isset($_GET['enddate']) ) {
		$enddate=$_GET['enddate'];
	}
	if ( isset($start_date) ) {
			$AND = $AND . " AND start_date between '" . $start_date . "' and '" . $end_date . "'";
	}
?>
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable([
<?php
	//Get a list of games and API keys
	$sql = "SELECT stat_date, sum(value) as value
		FROM star.s_game_day
		WHERE 1=1
		$AND
		AND stat_date between '2012-01-01' and '2012-08-17'
		AND metric='". "ActiveUsersByDay' 
		GROUP by 1
		ORDER BY 1";


	$results =& $mdb2->query($sql);

	$count=1;

	while ($row = $results->fetchRow(MDB2_FETCHMODE_ASSOC)) {
	
		if ( $count > 1 ) {
			echo ",\n['" . $row['stat_date'] . "', " . $row['value'] . "]";
		}
		else if ( $count == 1 ) {
			echo "['Date', 'DAU'],\n";
			echo "['" . $row['stat_date'] . "', " . $row['value'] . "]";
		};

		$count++;

	}
?>
        ]);
        var options = {
          title: 'Company Performance',
          hAxis: {title: 'Year',  titleTextStyle: {color: 'red'}}
        };

        var chart = new google.visualization.AreaChart(document.getElementById('chart_div'));
        chart.draw(data, options);
      }
    </script>
  </head>
  <body>
<?php
	debugger("SQL : $sql");
	debugger("AND : $AND");
	print_r($_GET);
?>
    <div id="chart_div" style="width: 900px; height: 500px;"></div>
  </body>
</html>

<?php

function debugger($msg) {

   if ( __DEBUG__ ) {
	echo "<p>DEBUG: $msg<p>";
   }
}
