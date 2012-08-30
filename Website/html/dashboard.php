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
		$AND = "AND a.game_id=$gameid";
	}
	if ( isset($_GET['clientid']) ) {
		$clientid=$_GET['clientid'];
		$AND = $AND . " AND a.client_id=$clientid";
	}
	if ( isset($_GET['startdate']) ) {
		$startdate=$_GET['startdate'];
	}
	if ( isset($_GET['enddate']) ) {
		$enddate=$_GET['enddate'];
	}
	if ( isset($start_date) ) {
			$AND = $AND . " AND a.start_date between '" . $start_date . "' and '" . $end_date . "'";
	}

	//Query stuff
	if ( isset($gameid)) {
		$sql = "SELECT game_name, stat_date, sum(value) as value
		FROM star.s_game_day a, lookups.l_flurry_game g
		WHERE 1=1
		$AND
		AND a.game_id=g.game_id
		AND stat_date between '2012-01-01' and '2012-08-17'
		AND metric='". "ActiveUsersByDay' 
		GROUP by 1,2
		ORDER BY 1,2";
	}
	else {
		$sql = "SELECT 'All Games' as game_name, stat_date, sum(value) as value
		FROM star.s_game_day a
		WHERE 1=1
		$AND
		AND stat_date between '2012-01-01' and '2012-08-17'
		AND metric='". "ActiveUsersByDay' 
		GROUP by 1,2
		ORDER BY 1,2";
		
	}

	$results =& $mdb2->query($sql);
	if (PEAR::isError($results) || !$results->valid()) {
?>
  </head>
  <body>
	<p>SQL Error: 
<?php	
	$msg=$results->getMessage();
	echo "$msg<br>";
	echo "$sql<br>";
?>
	</p>
  </body>
  </html>
<?php
  		exit;
	}
	else {
?>
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart","table"]});
      google.setOnLoadCallback(drawChart);
      google.setOnLoadCallback(drawTable);
      function drawChart() {
        var chartdata = google.visualization.arrayToDataTable([
<?php

		$count=1;

		while ($row = $results->fetchRow(MDB2_FETCHMODE_ASSOC)) {
	
			if ( $count > 1 ) {
				echo ",\n['" . $row['stat_date'] . "', " . $row['value'] . "]";
			}
			else if ( $count == 1 ) {
				$game_name=$row['game_name'];
				echo "['Date', 'DAU'],\n";
				echo "['" . $row['stat_date'] . "', " . $row['value'] . "]";
			};
	
			$count++;

		}
?>
        ]);
        var options = {
<?php

		echo "title: 'Game : $game_name',\n";
?>
          hAxis: {title: 'Date'},
	  vAxis: {title: 'DAU'},
	  legend: {position: 'none'}
        };

        var chart = new google.visualization.AreaChart(document.getElementById('chart_div'));
        chart.draw(chartdata, options);
      }
	
      function drawTable() {
	var data = new google.visualization.DataTable();
<?php

		$results =& $mdb2->query($sql);

		$count=1;

		while ($row = $results->fetchRow(MDB2_FETCHMODE_ASSOC)) {
	
			if ( $count > 1 ) {
				echo ",\n['" . $row['stat_date'] . "', " . $row['value'] . "]";
			}
			else if ( $count == 1 ) {
				$game_name=$row['game_name'];
				echo "data.addColumn('string', 'Date');\n";
				echo "data.addColumn('number', 'DAU');\n";
				echo "data.addRows([['". $row['stat_date'] . "', " . $row['value'] . "]";
			};
	
			$count++;
		}
		
?>
        ]);

	var table = new google.visualization.Table(document.getElementById('table_div'));
        table.draw(data, {showRowNumber: true});
	}
  </script>
  </head>
  <body>

<?php
	debugger("SQL : $sql");
	debugger("AND : $AND");
	print_r($_GET);
?>
    <div id="chart_div" style="width: 1000px; height: 600px;"></div>
    <div id="table_div" style="width: 1000px; height: 600px;"></div>
  </body>
</html>

<?php
   }

function debugger($msg) {

   if ( __DEBUG__ ) {
	echo "<p>DEBUG: $msg<p>";
   }
}
