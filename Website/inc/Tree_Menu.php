<?php
//
class Menu {

	public $db;

	function __construct($db) {

		$this->db = $db;

	        $sql = "SELECT *
                	FROM reporting.report_tree
			ORDER by sequence, path;";

        	$result = run_sql($this->db, $sql);
		$prev_level[1]='';
		$prev_level[2]='';
		$prev_level[3]='';
		$n_uls=0;

		echo "<div id=\"treeDiv1\">\n";
		echo "<ul>\n";
        	while ( $row = db_fetch_assoc($result[0]) ) {
			$cols=explode("/",$row['path']);

			switch (count($cols)) {
				case 0:
				case 1:
				case 2:
					echo "<li><a href=\"/report_view_new.php?_report=" . $row['report'] . "\" target=\"content\" >" . $cols[1] . "</a></li>\n";
				break;
				case 3:
					if ( $prev_level[1] == $cols[1] ) {
					}
					else {
						$j=$n_uls;
						for ( $i=0; $i<$j; $i++) {
							echo "</ul>\n";
							$n_uls--;
						}
						
						#Close the old list
						echo "<li>" . $cols[1] . "\n";
						echo "<ul>\n";
						$n_uls++;
						$prev_level[1]=$cols[1];
					}
					echo "<li><a href=\"/report_view_new.php?_report=" . $row['report'] . "\" target=\"content\" >" . $cols[2] . "</a></li>\n";
				break;
				case 4:
					if ( $prev_level[1] == $cols[1] ) {
					}
					else {
						if ( $n_uls == 1 ) {
							echo "</ul>\n";
							$n_uls--;
						}
						#Close the old list
						echo "<li>" . $cols[1] . "\n";
						echo "<ul>\n";
						$n_uls++;
						$prev_level[1]=$cols[1];
					}
					if ( $prev_level[2] == $cols[2] ) {
					}
					else {
						if ( $n_uls == 2 ) {
							echo "</ul>\n";
							$n_uls--;
						}
						echo "<li>" . $cols[2] . "\n";
						echo "<ul>\n";
						$n_uls++;
						$prev_level[2]=$cols[2];
					}
					echo "<li><a href=\"/report_view_new.php?_report=" . $row['report'] . "\" target=\"content\" >" . $cols[3] . "</a></li>\n";
				break;
			}
		}
		for ( $i=0; $i<$n_uls; $i++) {
			echo "</ul>\n";
		}
		echo "</ul>\n";
		echo "</div>\n";
	}
}
?>
