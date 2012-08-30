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

        $dau = run_report('dau',$AND);
	$install = run_report('installs',$AND);
	$retention = run_report('retention',$AND);

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

function run_report(metric, $filter)

if ( "$metric" eq "dau" ) {
	$sql = "SELECT game_name as 'Game',
                stat_date as 'Date',
		sum(value) as 'DAU'
		from star.s_game_day
		where metric='ActiveUsers'
		$filter
		group by 1,2
		order by 1,2";

	$results =& $mdb2->query($sql);
	
}
else if ( "$metric" eq "installs" ) { 
	$sql = "SELECT game_name as 'Game',
                stat_date as 'Date',
		sum(value) as 'DAU'
		from star.s_game_day
		where metric='NewUsers'
		$filter
		group by 1,2
		order by 1,2";
}
else if ( "$metric" eq "retention" ) {

	$sql = 'drop table if exists tmp.dau;';

	drop_table("tmp.dau");
	drop_table("tmp.installs");
	drop_table("tmp.retention_summary");
	drop_table("tmp.retention_results");

	$sql = "create temporary table tmp.dau(index(user_id))
		as 
		select user_id, 
		stat_date
		from star.s_user_day
		where game_id=1
		and client_id=1
		group by 1,2;";

	run_sql($sql);

	$sql="create temporary table tmp.installs(index(user_id))
	      as 
	      select user_id, 
	      min(stat_date) as install_date
	      from tmp.dau
	      group by 1;";

	run_sql($sql);

	$sql="create temporary table tmp.retention_results
	as
	SELECT install_date, 
	users,
	day1 *100.0 / users as D1,
	day2 *100.0 / users as D2,
	day3 *100.0 / users as D3,
	day4 *100.0 / users as D4,
	day5 *100.0 / users as D5,
	day6 *100.0 / users as D6,
	day7 *100.0 / users as D7,
	day8 *100.0 / users as D8,
	day9 *100.0 / users as D9,
	day10 *100.0 / users as D10,
	day11 *100.0 / users as D11,
	day12 *100.0 / users as D12,
	day13 *100.0 / users as D13,
	day14 *100.0 / users as D14,
	day15 *100.0 / users as D15,
	day16 *100.0 / users as D16,
	day17 *100.0 / users as D17,
	day18 *100.0 / users as D18,
	day19 *100.0 / users as D19,
	day20 *100.0 / users as D20,
	day21 *100.0 / users as D21,
	day22 *100.0 / users as D22,
	day23 *100.0 / users as D23,
	day24 *100.0 / users as D24,
	day25 *100.0 / users as D25,
	day26 *100.0 / users as D26,
	day27 *100.0 / users as D27,
	day28 *100.0 / users as D28,
	day29 *100.0 / users as D29,
	day30 *100.0 / users as D30
	FROM
	(
    	SELECT install_date, 
    	sum(case when ddiff=0 or ddiff is null then cnt else 0 end) users,
    	sum(case when ddiff = 1 then cnt else 0 end) day1,
    	sum(case when ddiff = 2 then cnt else 0 end) day2,
    	sum(case when ddiff = 3 then cnt else 0 end) day3,
    	sum(case when ddiff = 4 then cnt else 0 end) day4,
    	sum(case when ddiff = 5 then cnt else 0 end) day5,
    	sum(case when ddiff = 6 then cnt else 0 end) day6,
    	sum(case when ddiff = 7 then cnt else 0 end) day7,
    	sum(case when ddiff = 8 then cnt else 0 end) day8,
    	sum(case when ddiff = 9 then cnt else 0 end) day9,
    	sum(case when ddiff = 10 then cnt else 0 end) day10,
    	sum(case when ddiff = 11 then cnt else 0 end) day11,
    	sum(case when ddiff = 12 then cnt else 0 end) day12,
    	sum(case when ddiff = 13 then cnt else 0 end) day13,
    	sum(case when ddiff = 14 then cnt else 0 end) day14,
    	sum(case when ddiff = 15 then cnt else 0 end) day15,
    	sum(case when ddiff = 16 then cnt else 0 end) day16,
    	sum(case when ddiff = 17 then cnt else 0 end) day17,
    	sum(case when ddiff = 18 then cnt else 0 end) day18,
    	sum(case when ddiff = 19 then cnt else 0 end) day19,
    	sum(case when ddiff = 20 then cnt else 0 end) day20,
    	sum(case when ddiff = 21 then cnt else 0 end) day21,
    	sum(case when ddiff = 22 then cnt else 0 end) day22,
    	sum(case when ddiff = 23 then cnt else 0 end) day23,
    	sum(case when ddiff = 24 then cnt else 0 end) day24,
    	sum(case when ddiff = 25 then cnt else 0 end) day25,
    	sum(case when ddiff = 26 then cnt else 0 end) day26,
    	sum(case when ddiff = 27 then cnt else 0 end) day27,
    	sum(case when ddiff = 28 then cnt else 0 end) day28,
    	sum(case when ddiff = 29 then cnt else 0 end) day29,
    	sum(case when ddiff = 30 then cnt else 0 end) day30
    	FROM 
    	tmp.retention_summary
    	GROUP BY install_date
	) a
	ORDER BY install_date desc;";

	$results=run_sql($sql);

	return $results;

}

function drop_table($table) {

	$sql="drop table if exists $table cascade";

	$affected =& $mdb2->exec($sql);

	return $affected;

}


function run_sql($sql) {

	$results =& $mdb2->query($sql);
	if (PEAR::isError($results) || !$results->valid()) {
        $msg=$results->getMessage();
	echo "<p>$msg</p>";
	return FALSE;
}
