<?php
// Parameter class
// 
//
class ReportParameter {

	protected $type;
	protected $value;
	protected $name;
	protected $text;
	protected $display;
	protected $default;
	protected $options;
	protected $ndates;
	public $db;

	function __construct($parameter) {

		foreach ($parameter as $key => $value) {
			switch($key) {
			case 'name':
				$this->name=$value;
			break;
			case 'type':
				$this->type=$value;
			break;
			case 'text':
				$this->text=$value;
			break;
			case 'display':
				$this->display=$value;
			break;
			case 'value':
				$this->value=$value;
			break;
			case 'default':
				$this->default=$value;
			break;
			case '_content': 
				if ( $this->type == 'query' ) {
					$this->query=$value;
				}
			break;
			case 'option':
				if ( $this->type == 'select' ) {
					$this->option=$value;
				}
			break;

			}
		}
		
		// Check the parameter info to make sure its correct
		if ( $this->name == '' || $this->type == '' ) {
			// We should have a name and a type
			return FALSE;
		}
		if ( !in_array($this->type, array('select','query','date','text')) ) {
			return FALSE;
		}
	}

	// This function sets the parameter to HTML so it can be selected
	function toHTML() {

	        if ( !isset($this->name) || !isset($this->type) ) {
			echo "<br>ERROR: Name and/or type not set for this parameter.\n";
	                return FALSE;
	        }

		if ( $this->type == "query" ) {
                	echo "<td width='30%'>".$this->text.":</td><td><select name='" . $this->name."' style='{border: solid 1px}'>\n";
			
                	if ( $result = run_sql($this->db, $this->query) ) {

                       		while ($row = db_fetch_assoc($result[0])) {
                               		if ( isset($this->default) ) {
                                       		if ( $this->default == $row[$this->display] ) {
                                               		echo "<option value='" . $row[$this->value] . "' select='selected'>" . $row[$this->display] . "</option>\n";
                                       		}
                               		}
                               		else {
                                       		echo "<option value='" . $row[$this->value] . "'>" . $row[$this->display] . "</option>\n";
                               		}
                       		}
                       		echo "</select></td>\n";
                	}
        	}
        	elseif ( $this->type == "select" ) {
                	echo "<td>".$this->text.":</td><td>\n<select name='".$this->name."' style='{border: solid 1px}'>\n";
                	//Process option tags
			
                	if ( isset($this->option['0']) ) {
                       		foreach ($this->option as $option => $value) {
                               		if ( $value['_content'] == $this->default) {
                                       		echo "<option value='" . $value['value'] . "' select='selected'>" . $value['_content'] . "</option>\n";
                               		}
                               		else {
                                       		echo "<option value='" . $value['value'] . "'>" . $value['_content'] . "</option>\n";
                               		}
                       		}
                	}
                	else {
                       		echo "<option value='".$this->option['value']."'>".$this->option['content']."</option>\n";
                	}
                	echo "</select></td>\n";
       		 }
        	elseif ( $this->type == "date" ) {
                	//Process date parameters
                	echo "<td>".$this->text.":</td><td>\n";
                	echo "<input type='text' style='{border: solid 1px}' name='".$this->name."' id='cal1Date".$this->ndate."' autocomplete='off' size='20' value='' /></td>\n";
        	}
        	elseif ( $this->type == "edit" ) {
                	echo "<td>".$this->text.":</td><td>\n";
                	echo "<input type='text' style='{border: solid 1px}' name='".$this->name."' value='' /></td>\n";
		}
        	else {
                	return FALSE;
        	}
    
	}
	
	function getName() {
		return $this->name;
	}

	function getText() {
		return $this->text;
	}	


	function setDisplay() {
		return $this->display;
	}

	function getValue() {
		return $this->value;
	}

	function getType() {
		return $this->type;
	}

}

class QueryParameter
{
	public $column;
	public $value;

	function __construct($parameter) {
		if ( isset($parameter) ) {
			$this->column=$parameter[0];
			$this->value=$parameter[1];
		}
	}
}

class ReportParms 
{

	public $parameters;
	protected $reportName;
	protected $nparms;

	function __construct($parms) {
	
		if ( isset($parms['0']) ) {
			// We have multiple parms so process as such
			foreach ( $parms as $key => $value ) {
				$this->parameters[] = new ReportParameter($value);
			}
		}
		else {
			$this->parameters[]= new ReportParameter($parms);
		}
		$this->nparms = count($this->parameters);
	}

	function getNumberOfParms() {
		return $this->nparms;
	}

	function getHeight() {
		return ($this->nparms*50)+75;
	}

	function getTopMargin() {
		return -round((($this->nparms*50)+75)/2);
	}

	function getreportName() {
		return $this->reportName;
	}
	
	function setReportName($name) {
		// Set the name
		$this->reportName=$name;
	}

	function dump() {
		if ( $this->nparms > 0 ) {
			var_dump($this->parameters);
		}	
		else {
			echo "WARNING: No parameters set.\n";
		}
	}

	// Store the DB connection object
	function setDB($db) {
		foreach ($this->parameters as $value) {
			$value->db = $db;
		}
	}

	function toHTML() {

		require_once(__ROOT__.'/tpl/parms_hdr.tpl');

		$count=1;

		foreach ( $this->parameters as $parameter ) {
			if ( $parameter->getType() == 'date' ) {
				$parameter->ndate=$count;
				$count++;
			}
		}

		foreach ( $this->parameters as $parameter ) {
			echo "<tr>";
			$parameter->toHTML();
			echo "</tr>";
		}

		require_once(__ROOT__.'/tpl/parms_ftr.tpl');
        	return TRUE;
	}
}

class QueryParms 
{
	public $parameters;
	protected $nparms;

	function __construct($parms) {
		$this->nparms = 0;
		foreach ($parms as $key => $value) {
			if ( $key != '_report' ) {
				$this->parameters[] = new QueryParameter(array($key, $value));
			}
		}
		$this->nparms = count($this->parameters);
	}

	function dump() {
		
		if ( $this->nparms > 0 ) {
			echo "NOTE: Dumping parameters for query:\n";
			echo "NOTE: Number of paramerers is: ".$this->nparms.".\n";
			var_dump($this->parameters);
		}	
		else {
			echo "WARNING: No parameters set.\n";
		}
	}

	function getNumberOfParms() {
		return $this->nparms;
	}
}

class Query 
{
	public $parms;
	public $name;
	public $source;
	public $type;
	public $time;
	protected $db;
	protected $reportName;

	function __construct($query) {
		foreach ( $query as $key => $value ) {
			if ( $key == 'name' ) {
				$this->name = $value;
			}
			elseif ( $key == 'title' ) {
				$this->title = $value;
			}
			elseif ( $key == 'time' ) {
				$this->time = $value;
			}
			elseif ( $key == '_content' ) {
				$this->source = $value;
			}
		}
	}

	function setReportName($name) {
		$this->reportName=$name;
	}

	function setDB($db) {
		$this->db=$db;
	}

	function getNumberOfParms() {
		return $this->parms->getNumberOfParms();
	}

	function setParms($parms) {
		$this->parms = new QueryParms($parms);
	}

	function run() {
		$sql = $this->source;

		if ( $this->getNumberOfParms()>0 ) {	
			foreach ($this->parms->parameters as $index => $parameter) {
	        		$sql=str_replace("$".$parameter->column, $parameter->value, $sql);
			}
		}

		if ( isset($this->time) ) {
			set_time_limit($this->time*60);
        		$result = run_sql($this->db, $sql);
			set_time_limit(30);
		}
		else {
        		$result = run_sql($this->db, $sql);
		}

		return $result;
	}
}

class Table extends Report
{
	public $name;
	public $title;
	public $isHidden;
	public $HTML;

	function __construct($table) {

		$this->isHidden = FALSE;

		foreach ( $table as $key => $value ) {
			if ( $key == 'name' ) {
				$this->name = $value;
			}
			elseif ( $key == 'title' ) {
				$this->title = $value;
			}
			elseif ( $key == 'hidden' ) {
				// Should be only hidden
				if ( $value == 'true' ) {
					$this->isHidden = TRUE;
				}
			}
		}
	}

	function setData($result) {

		// Reset results pointer
		mysqli_data_seek($result, 0);

		//Create EXCEL output object
		$file_name = $this->csv;
		if ( file_exists("/tmp/".$this->csv) ) {
			$fh = fopen("/tmp/".$this->csv,"a") or die ("ERROR: Could not open file...");
		}
		else {
			$fh = fopen("/tmp/".$this->csv,"w") or die ("ERROR: Could not open file...");
		}

		//$attrs = array('width' => '600', 'border' => '1', 'class' => 'report');
		$attrs = array('class' => 'report');
		$rAttrs=array();

		$table = new \HTML_Table($attrs);
		$table->setAutoGrow(true);
		$table->setRowAttributes(0, $rAttrs, true);

		$record=0;

		while ($row = db_fetch_assoc($result)) {

			if ( $record > 0 ) {
				$col=0;
				$table->setCellContents($record+1, $col, $record+1);	
				$table->setRowAttributes($record+1, $rAttrs, true);
	
				foreach ($row as $key => $value) {
					$col++;
					if ( isset($this->formats[strtolower($key)]) ) {
						$table->setCellContents($record+1, $col, $this->formatValue($this->formats[strtolower($key)], $value));	
					}
					else {
						// If not format, we assume its a number
						$table->setCellContents($record+1, $col, $value);	
					}
					//CSV
					if ( $col == 1 ) {
						fwrite($fh, "$value");
					}
					else {
						fwrite($fh,",$value");
					}
					
				}
				fwrite($fh,"\n");
       	         	}
			else if ( $record == 0 ) {
				$col=0;
				$table->setHeaderContents(0, $col, '#');
				foreach ($row as $key => $value) {
					$col++;
					$table->setHeaderContents($record, $col, ucfirst($key));
					if ( $col == 1 ) {
						fwrite($fh, "$key");
					}
					else { 
						fwrite($fh,",$key");
					}
				}
				fwrite($fh,"\n");
	
				$col=0;
	
				$table->setCellContents($record+1, $col, $record+1);	
				$table->setRowAttributes($record+1, $rAttrs, true);
				foreach ($row as $key => $value) {
					$col++;
					if ( isset($this->formats[strtolower($key)]) ) {
						$table->setCellContents($record+1, $col, $this->formatValue($this->formats[strtolower($key)], $value));	
					}
					else {
						// If not format, we assume its a number
						$table->setCellContents($record+1, $col, $value);	
					}
					// CSV
					if ( $col == 1 ) {
						fwrite($fh, "$value");
					}
					else {
						fwrite($fh,",$value");
					}
				}
				fwrite($fh,"\n");
			}
	
			$record++;
		}
		fwrite($fh,"\n");
		fclose($fh);
	
		// Get the HTML for the table
		if ( $this->isHidden == FALSE ) { 
			$this->HTML = $table->toHTML();
		}
		else {
			$this->HTML = '';
		}
		
	}
}

class Chart extends Report
{

	public $name;
	public $break;
	public $position;
	public $type;
	public $query;
	public $title;
	public $haxis;
	public $vaxis;
	public $legend;
	public $options;
	public $height;
	public $width;
	public $columns;
	public $div;
	public $HTML;

	function __construct($chart) {
		$this->break = FALSE;
		foreach ( $chart as $key => $value ) {

			switch(strtolower($key)) {
			case 'name':
				$this->name = $value;
			break;
			case 'query':
				$this->query = $value;
			break;
			case 'title':
				$this->title = $value;
			break;
			case 'type':
				$this->type = $value;
			break;
			case 'position':
				$this->position = $value;
			break;
			case 'width':
				$this->width = str_replace("%","",$value);
			break;
			case 'break':
				if ( $value == "true" ) {
					$this->break = TRUE;
				}
			break;
			case 'options':
				$this->options = $value;
			break;
			case 'haxis':
				$this->haxis = $value['options'];
			break;
			case 'vaxis':
				$this->vaxis = $value['options'];
			break;
			case 'legend':
				$this->legend = $value['options'];
			break;
			case 'column':
				foreach ( $value as $index => $info ) {
					$this->columns[$info['name']] = TRUE ;
				}
			break;
			}
		}
	}

	function setData($result) {
		// Reset result pointer
		mysqli_data_seek($result, 0);

		$chartData="google.setOnLoadCallback(drawChart".$this->name.");\n";
		$chartData.="function drawChart".$this->name."() {
                    var data".$this->name." = google.visualization.arrayToDataTable([\n";

		$record=0;
		while ($row = db_fetch_assoc($result)) {

			if ( $record > 0 ) {
				$col=0;
	
				$chartData.=",\n";
	
				foreach ($row as $key => $value) {
					$col++;
					if ( isset($this->columns[$key]) && isset($this->formats[strtolower($key)]) ) {
	
						// If we have a format, check its type
						if ( in_array($this->formats[strtolower($key)], array('string','date')) ) {
							if ( $col == 1 ) { 
								$chartData.="['".$value."'";
							}
							else {
								$chartData.=",'".$value."'";
							}
						}
						elseif ( in_array($this->formats[strtolower($key)], array('date')) ) {
							if ( $col == 1 ) { 
								$chartData.="[new date(".substr($value,0,4).",".substr($value,5,2).",".substr($value, 8,2).")";
							}
							else {
								$chartData.=", new date(".substr($value,0,4).",".substr($value,5,2).",".substr($value, 8,2).")";
							}
						}
						elseif ( in_array($this->formats[strtolower($key)], array('number')) ) {
							if ( $col == 1 ) { 
								$chartData.="[".round($value);
							}
							else {
								$chartData.=",".round($value);
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
					elseif ( isset($this->columns[$key]) )  {
						// If not format, we assume its a number
						if ( $col == 1 ) { 
							$chartData.="[".$value;
						}
						else {
							$chartData.=",".$value;
						}
					}
				}
				$chartData.="]";
       	         	}
			else if ( $record == 0 ) {
				$col=0;
				foreach ($row as $key => $value) {
					$col++;
					if ( $col == 1 && isset($this->columns[$key]) ) {
						$chartData.="['".ucfirst($key)."'";
					}
					elseif ( isset($this->columns[$key]) ) { 
						$chartData.=",'".ucfirst($key)."'";
					}
				}
				$chartData.="],\n";
	
				$col=0;
	
				foreach ($row as $key => $value) {
					$col++;
					if ( isset($this->columns[$key]) && isset($this->formats[strtolower($key)]) ) {
	
						// If we have a format, check its type
						if ( in_array($this->formats[strtolower($key)], array('string','date')) ) {
							if ( $col == 1 ) { 
								$chartData.="['".$value."'";
							}
							else {
								$chartData.=",'".$value."'";
							}
						}
						elseif ( in_array($this->formats[strtolower($key)], array('date')) ) {
							if ( $col == 1 ) { 
								$chartData.="[new date(".substr($value,0,4).",".substr($value,5,2).",".substr($value, 8,2).")";
							}
							else {
								$chartData.=", new date(".substr($value,0,4).",".substr($value,5,2).",".substr($value, 8,2).")";
							}
						}
						elseif ( in_array($this->formats[strtolower($key)], array('number')) ) {
							if ( $col == 1 ) { 
								$chartData.="[".round($value);
							}
							else {
								$chartData.=",".round($value);
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
					elseif ( isset($this->columns[$key]) )  {
						// If not format, we assume its a number
						if ( $col == 1 ) { 
							$chartData.="[".$value;
						}
						else {
						$chartData.=",".$value;
						}
					}
				}
				$chartData.="]";
                	}

			$record++;

		}

		$chartData.="\n]);\n";
		$this->HTML=$chartData;
		$this->HTML.="var options = {";
		if ( isset($this->options) ) {
			$this->HTML.=$this->options.",";
		}
		$this->HTML.="title:  '".$this->title."',
hAxis:  ".$this->haxis.",
vAxis:  ".$this->vaxis.",
legend: ".$this->legend."
};\n";
		$this->HTML.="var chart = new google.visualization.".$this->type."(document.getElementById('chart_div".$this->name."'));
   chart.draw(data".$this->name.", options);
}\n";
		

	}

}

class Format 
{

	public $columName;
	public $type;

	function __construct($format) {

		$this->columnName=strtolower($format['name']);
		$this->type=strtolower($format['format']);
	}

}

class Report
{

	public $reportName;
	public $reportTitle;
	public $parms;
	public $passedParms;
	public $nPassedParms;
	public $queries;
	public $charts;
	public $tables;
	public $formats;
	public $XML;
	public $HTML;
	public $batch;
	public $title;
	public $description;
	public $csv;

	function __construct($name, $parms) {

		$this->reportName=$name;
		$this->setReportTitle();
		$this->csv=$this->reportName."_".date('Ymdhis').".csv";

        	// Parse the report
        	$xml =new \XML_Unserializer();
        	$xml->setOption(XML_UNSERIALIZER_OPTION_ATTRIBUTES_PARSE, TRUE);
        	$xml->unserialize(__ROOT__ ."/reports/".$this->reportName.".xml", TRUE);
        	if (\PEAR::isError($xml)) {
                        die("ERROR: XML : Report Name: ".$this->reportName." : MSG: " . $xml->getMessage());
        	}

		$this->XML=$xml->getUnserializedData();

        	if (\PEAR::isError($xml)) {
                        die("ERROR: XML : Report Name: ".$this->reportName." : MSG: " . $xml->getMessage());
        	}
	
		// Check if it has a query	
		if ( !isset($this->XML['query']) ) { 
                        die("ERROR: XML : Report Name: ".$this->reportName." : MSG: This report has no query");
		}
		else {
			$this->batch = FALSE;
			foreach ( $this->XML as $key => $value ) {
				switch($key) {
				case 'query':
					if ( isset($value[0]) ) {
						foreach ( $value as $index => $query ) {

							$q = new Query($query);
							$q->setReportName($name);

							$t = new Table($query);
							$t->csv=$this->csv;
							$t->reportName=$this->reportName;

							$this->queries[$q->name] = $q;
							$this->tables[$t->name] = $t;
						}
					}
					else {
						$q = new Query($value);
						$q->setReportName($name);

						$t = new Table($value);
						$t->csv=$this->csv;
						$t->reportName=$this->reportName;

						$this->queries[$q->name] = $q;
						$this->tables[$t->name] = $t;

					}
				break;
				case 'chart':
					if ( isset($value[0]) ) {
						foreach ( $value as $index => $chart ) {
							$c = new Chart($chart);
							$this->charts[$c->name] = $c;
						}
					}
					else {
						$c = new Chart($value);
						$this->charts[$c->name] = $c;
					}
				break;
				case 'title':
					$this->title=$value;
				break;
				case 'description':
					$this->description=$value;
				break;
				case 'column':
					if ( isset($value[0]) ) {
						foreach ( $value as $index => $format ) {
							$f = new Format($format);
							$this->formats[$f->columnName]=$f->type;
						} 
					}
					else {
						$f = new Format($value);
						$this->formats[$f->columnName]=$f->type;
					}
				break;
				case 'parm':
					$this->parms = new ReportParms($this->XML['parm']);
					$this->parms->setReportName($name);
				break;
				case 'batch':
					$this->batch = TRUE;
				break;
				}
			}
		}

		foreach ( $this->tables as $key => $value ) {
			$value->formats=$this->formats;
		}

		if ( isset($this->charts) ) {
			foreach ( $this->charts as $key => $value ) {
				$value->formats=$this->formats;
			}
		}


		//Check if the report has any parameters passed

		unset($parms['_report']);

		$this->passedParms=$parms;
		$this->nPassedParms = count($this->passedParms);

       	 	foreach ( $this->passedParms as $key => $value ) {
			$this->passedParms[$key]=str_ireplace('today',date('Y-m-d'),$this->passedParms[$key]);
			$this->passedParms[$key]=str_ireplace('yesterday-29d',date('Y-m-d', mktime(0, 0, 0, date("m")  , date("d")-30, date("Y"))),$this->passedParms[$key]);
			$this->passedParms[$key]=str_ireplace('yesterday',date('Y-m-d', mktime(0, 0, 0, date("m")  , date("d")-1, date("Y"))),$this->passedParms[$key]);
		}

		foreach ( $this->queries as $index => $query ) {
			$query->parms = new QueryParms($this->passedParms);	
		}

		$fh = fopen("/tmp/".$this->csv,"w") or die ("ERROR: Could not open file...");

		fwrite($fh,$this->title."\n");
		fwrite($fh,$this->reportName."\n");
		fwrite($fh,"$this->description\n");
		fwrite($fh,"\n");
       	 	fwrite($fh,"Parameters Passed:\n");
       	 	foreach ( $this->passedParms as $key => $value ) {
       	 		fwrite($fh, "$key,$value\n");
       	 	}
		fwrite($fh,"\n");
		fclose($fh);
	}

	function setReportTitle() {
	
		// Also get the title
		$db=db_connect();
		$sql="SELECT title
		      FROM reporting.navigation
		      WHERE report='".$this->reportName."'
		      LIMIT 1;";

		$result=run_sql($db, $sql);

		while ($row = db_fetch_assoc($result[0])) {
			$this->reportTitle=$row['title'];
		}
	}

	function getNumberPassedParms() {
		return $this->nPassedParms;
	}

	function dump() {
		print_r($this->queries);
	}

	function setHeader() {
		require_once(__ROOT__.'/tpl/report_hdr.tpl');
	}

	function setFooter() {
		require_once(__ROOT__.'/tpl/report_ftr.tpl');
	}

	function formatValue($format, $value)
	{

	       	switch ($format) {
       		case 'number':
                	return number_format($value);
               	break;
      		case 'percent':
              		if (is_numeric($value)) { 
				return number_format($value,2)."%";
			}
			else {
				return $value;
			}
      		break;
              	case 'percent(1)':
              		if (is_numeric($value)) {
				return number_format($value,1)."%";
			}
			else {
				return $value;
			}
              	break;
              	case 'percent(2)':
                     	if (is_numeric($value)) {
				return number_format($value,2)."%";
			}
			else {
				return $value;
			}
              	break;
               	case 'percent(3)':
                       	if (is_numeric($value)) {
				return number_format($value,3)."%";
			}
			else {
				return $value;
			}
               	break;
               	case 'decimal':
                       	return number_format($value,2,'.',',');
               	break;
               	case 'currency':
                       	if (is_numeric($value)) {
				return "$".number_format($value,2,'.',',');
			}
			else {
				return $value;
			}
               	break;
		default:
			return $value;
		}
       	}

	function toHTML() {

		//Log that this job has started
		$ts=date("Y-m-d H:i:s");

		// Third parm is HTML, at this point we have none
		$this->reportLog(4, $ts, '');

		foreach ( $this->queries as $index => $query ) {
			$results=$query->run();

			foreach ( $results as $index => $result ) {
                                if ( $result ) {
					// See if we have a chart that users this query
					if ( isset($this->charts) ) { 
						foreach ( $this->charts as $index => $chart ) {
						
							if ( $chart->query == $query->name ) {
								// We have a chart that uses this query
								$this->charts[$chart->name]->setData($result);
							}
						}
					}
					$this->tables[$query->name]->setData($result);
                                }
                        }
		}

		// Turn on output buffering
		ob_start();
	
		// Now set all of the HTML
		$this->setHeader();

		// Chart stuff has to appear in the HEAD sectioin
		if ( isset($this->charts) ) {
    			$this->HTML.="
<!-- GOOGLE CHART DATA START -->
<script type='text/javascript'>
google.load('visualization', '1', {packages:['corechart']});\n";
			foreach ( $this->charts as $name => $chart ) {
				$this->HTML.=$chart->HTML;
			}
		}

		$this->HTML.="</script>\n";
		$this->HTML.="<!-- GOOGLE CHART DATA END -->\n";
		$this->HTML.="</head>\n";

		// Main body - Report info
	        $this->HTML.="<body>\n";

		// In case we have any parms in the title
	        foreach ( $this->passedParms as $key => $value ) {
	        	$this->title=str_replace("$".$key, $value, $this->title);
	        }

		if ( $this->batch == FALSE ) { 
	        	$this->HTML.="<div class='parms_passed'>\n<h1>$this->title</h1>\n";
	        	$this->HTML.="<p>".str_replace("\n","<br>",$this->description)."</p>\n";
	        	$this->HTML.="<h2>Report Title: $this->reportTitle</h2>\n";
	       		$this->HTML.="<p>Parameters Passed:\n";
	        	$this->HTML.="<table>\n";
	        	foreach ( $this->passedParms as $key => $value ) {
	                	$this->HTML.="<tr><td>$key:&nbsp;</td><td>$value</td></tr>\n";
	        	}
	        	$this->HTML.="</table>\n";
	        	$this->HTML.="</p>\n";
	        	$this->HTML.="<form method='post' action='report_download.php'>\n";
	        	$this->HTML.="<input name='report' type='hidden' value='" . $this->csv . "'>\n";
	        	$this->HTML.="<input type='submit' value='Download CSV'>\n";
	        	$this->HTML.="</form>\n</div>\n";
		}
		else {
			$this->HTML.="<h1>$this->title</h1>\n";
	        	$this->HTML.="<br>\n";
	        	$this->HTML.="<form method='post' action='report_download.php'>\n";
	        	$this->HTML.="<input name='report' type='hidden' value='" . $this->csv . "'>\n";
	        	$this->HTML.="<input type='submit' value='Download CSV'>\n";
	        	$this->HTML.="</form>\n";
		}

		// Results

		$c_found = FALSE;

		// Do we want our charts at the top?
		if ( isset($this->charts) ) {
			foreach ( $this->charts as $name => $chart ) {
				if ( $chart->position == "top") {
					if ( !$c_found ) {
						$this->HTML.="<table id='charts'>\n<tr>\n";
						$c_found = TRUE;
					}
					if ( $chart->break ) {
						$this->HTML.="</tr>\n<tr>\n";
					}
					if ( isset($chart->width) ) {
						$this->HTML.="<td class='chart' width='".$chart->width."%'><div id='chart_div".$chart->name."'></div></td>\n";
					}
					else {
						$this->HTML.="<td class='chart'><div id='chart_div".$chart->name."'></div></td>\n";
					}
				}
			}
			if ( $c_found ) {
				// Finish off table
				$this->HTML.="</tr></table>\n";
			}
		}

		$t_found = FALSE;
		$c_found = FALSE;

		foreach ( $this->tables as $name => $table ) {
			if ( !$table->isHidden ) {
				if ( !$t_found ) {
					$this->HTML.="<br>\n<h1>".$table->title."</h1><br>\n";
					$this->HTML.="<table>\n";
					$this->HTML.="<tr><td>\n";
					$this->HTML.=$table->HTML;
					$this->HTML.="</td><td>&nbsp;</td>";
					$t_found = TRUE;
				}
				else {
					$this->HTML.="<tr><td>\n<br>";
					$this->HTML.="<h1>".$table->title."</h1>\n<br>\n";
					$this->HTML.=$table->HTML;
					$this->HTML.="</td><td>&nbsp;</td>";
				}
			}
			
			$c_found = FALSE;

			if ( isset($this->charts) ) {
				foreach ( $this->charts as $index => $chart ) {
					if ( $chart->query == $name && $chart->position == "below") {
						if ( !$c_found ) {
							if ( $t_found) { 
								$this->HTML.="</tr>\n<tr><td>&nbsp;</td><td>&nbsp;</td></tr>\n<tr><td><table id='charts'>\n<tr>\n";
							}
							else {
								$this->HTML.="<table id='charts'>\n<tr>\n";
							}
							$c_found = TRUE;
						}
						if ( $chart->break ) {
							$this->HTML.="</tr>\n<tr>\n";
						}
						if ( isset($chart->width) ) {
							$this->HTML.="<td class='chart' width='".$chart->width."%'><div id='chart_div".$chart->name."'></div></td>\n";
						}
						else {
							$this->HTML.="<td class='chart'><div id='chart_div".$chart->name."'></div></td>\n";
						}
					}
					else if ( $chart->query == $name && $chart->position != "top") {
						$this->HTML.="<td class='chart'><div id='chart_div".$chart->name."'></div></td></tr>";
					}
				}
				if ( $c_found && $t_found ) {
					// Finish off table
					$this->HTML.="<td>&nbsp;</td></tr></table>\n";
				}
				else if ( $c_found ) {
					$this->HTML.="</tr></table>\n";
				}
			}
		}

		if ( $t_found ) {
			$this->HTML.="</table>\n";
		}
		echo $this->HTML;
		$this->setFooter();

		//Store output buffer
		$ob = ob_get_contents();
		$this->reportLog(0,$ts, $ob);

		ob_end_flush();
	}

	function reportLog($code, $ts, $html) {

		// We store the HTML used for this page and save it for a day

		if ( $code == 4 ) {
			$sql = "insert into reporting.report_log(report_name,
				report_startts,
				report_endts,
				report_parms,
				report_html,
				report_sql,
				report_csv,
				report_code) 
				values('".$this->reportName."','".$ts."',NULL,";
			$i=0;
			foreach ( $this->passedParms as $key => $value ) {
				if ( $i>0 ) {
					$sql.=",$key=$value";
				}
				else {
					$sql.="'$key=$value";
				}
				$i++;
			}
			// This code is the start, so HTML should be empty
			if ( $i>0 ) {
				$sql.="',";
			}
			else {
				$sql.='NULL,';
			}
			$sql.="NULL,NULL,'".$this->csv."', $code);";
		}
		elseif ( $code == 0 ) {
			$now=date("Y-m-d H:i:s");
			$html=str_replace("'","\'", $html);
			$sql = "update reporting.report_log
				set report_endts='".$now."',
				    report_code=0,
				    report_html='".$html."'
				where report_startts='".$ts."';";
			
		}
		run_sql($this->db, $sql);
	}

	function setDB($db) {
		foreach ($this->queries as $index => $query ) {
			$query->setDB($db);
		}
		if ( isset($this->parms) ) {
			$this->parms->setDB($db);
		}

		$this->db=$db;
	}

	function buildHTML($result) {
		
	}
}	

?>
