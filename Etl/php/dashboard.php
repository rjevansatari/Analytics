<?php
	ini_set('memory_limit', '128M');
	date_default_timezone_set('America/Los_Angeles');

	//Set up debugging

	require_once 'Log.php';
	require_once 'inc/db.php';
	require_once 'inc/config.php';

	$options = getopt("d");
        if ( array_key_exists('d',$options) ) {
                $debug = Log::singleton('console');
	}

?>
<html>
  <head>
<?php
	$AND="";
	if ( !empty($_GET)) {
		foreach ($_GET as $key => $value) {
			if ( $key == "gameid" ) {
				$gameid=$value;
				$AND = "AND game_id=$game_id";
			}
			if ( $key == "clientid" ) {
				$clientid=$value;
				$AND = " AND client_id=$client_id";
			}
			if ( $key == "startdate" ) {
				$startdate=$value;
			}
			if ( $key == "enddate" ) {
				$enddate=$value;
			}
		}
		if ( $start_date ) {
			$AND = " AND start_date between '" . $start_date . "' and '" . $end_date . "'";
		}
	}
	else {
		$AND="";
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

	debugger("SQL : $sql");

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
    <div id="chart_div" style="width: 900px; height: 500px;"></div>
  </body>
</html>

<?php

function debugger($msg) {

   global $debug;

   if ( $debug ) {
	$debug->log($msg, PEAR_LOG_DEBUG);
   }
}
