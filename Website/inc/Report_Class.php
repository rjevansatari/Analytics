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
                	echo "<td>".$this->text.":</td><td>&nbsp;&nbsp;<select name='" . $this->name."' style='{border: solid 1px}'>\n";
			
                	if ( $result = run_sql($this->db, $this->query) ) {

                       		while ($row = $result[0]->fetch_assoc()) {
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
                	echo "<td>".$this->text.":</td><td>&nbsp;&nbsp;\n<select name='".$this->name."' style='{border: solid 1px}'>\n";
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
                	echo "<td>".$this->text.":</td><td>&nbsp;&nbsp;";
                	echo "<input type='text' style='{border: solid 1px}' name='".$this->name."' id='cal1Date".$this->ndate."' autocomplete='off' size='20' value='' /></td>\n";
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
		return $this->nparms*60;
	}

	function getTopMargin() {
		return -round($this->nparms*(60/2));
	}

	function getreportName() {
		return $this->reportName;
	}
	
	function setreportName($name) {
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

		require_once(__ROOT__.'/tpl/parms_hdr_obj.tpl');

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

	function nParms() {
		return $this->nparms;
	}
}

class Query 
{
	public $parms;
	public $name;
	public $source;
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
			elseif ( $key == '_content' ) {
				$this->source = $value;
			}
		}
	}

	function setreportName($name) {
		$this->reportName=$name;
	}

	function setDB($db) {
		$this->db=$db;
	}

	function nParms() {
		return $this->parms->nparms;
	}

	function setParms($parms) {
		$this->parms = new QueryParms($parms);
	}

	function run() {
		$sql = $this->source;

		foreach ($this->parms->parameters as $index => $parameter) {
	        	$sql=str_replace("$".$parameter->column, $parameter->value, $sql);
		}
        	$result = run_sql($this->db, $sql);

		return $result;
	}
}

class Table extends Report
{
	public $name;
	public $title;
	public $HTML;

	function __construct($table) {
		foreach ( $table as $key => $value ) {
			if ( $key == 'name' ) {
				$this->name = $value;
			}
			elseif ( $key == 'title' ) {
				$this->title = $value;
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
		$attrs = array('border' => '1', 'class' => 'report');
		$table = new \HTML_Table($attrs);
		$table->setAutoGrow(true);
		$hrAttrs = array('bgcolor' => 'silver', 'align' => 'center');
		$table->setRowAttributes(0, $hrAttrs, true);
		$hrAttrs = array('align' => 'right');

		$record=0;

		while ($row = $result->fetch_assoc()) {

			if ( $record > 0 ) {
				$col=0;
				$table->setCellContents($record+1, $col, $record+1);	
				$table->setRowAttributes($record+1, $hrAttrs, true);
	
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
				$table->setRowAttributes($record+1, $hrAttrs, true);
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
		$this->HTML = $table->toHTML();
	}
}

class Chart extends Report
{

	public $name;
	public $type;
	public $query;
	public $title;
	public $div;
	public $HTML;

	function __construct($chart) {
		foreach ( $chart as $key => $value ) {
			if ( $key == 'name' ) {
				$this->name = $value;
			}
			elseif ( $key == 'title' ) {
				$this->title = $value;
			}
			elseif ( $key == 'type' ) {
				$this->type = $value;
			}
			elseif ( $key == 'query' ) {
				$this->query = $value;
			}
		}
	}

	function setData($result, $index) {
		// Reset result pointer
		mysqli_data_seek($result, 0);

		$chartData="google.setOnLoadCallback(drawChart".$index.");\n";
		$chartData.="function drawChart".$index."() {
                    var data".$index." = google.visualization.arrayToDataTable([\n";

		$record=0;
		while ($row = $result->fetch_assoc()) {

			if ( $record > 0 ) {
				$col=0;
	
				$chartData.=",\n";
	
				foreach ($row as $key => $value) {
					$col++;
					if ( isset($this->formats[strtolower($key)]) ) {
	
						// If we have a format, check its type
						if ( in_array($this->formats[strtolower($key)], array('string','date')) ) {
							if ( $col == 1 ) { 
								$chartData.="['".$value."'";
							}
							else {
								$chartData.=",'".$value."'";
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
					else {
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
					if ( $col == 1 ) {
						$chartData.="['".ucfirst($key)."'";
					}
					else { 
						$chartData.=",'".ucfirst($key)."'";
					}
				}
				$chartData.="],\n";
	
				$col=0;
	
				foreach ($row as $key => $value) {
					$col++;
					if ( isset($this->formats[strtolower($key)]) ) {
	
						// If we have a format, check its type
						if ( in_array($this->formats[strtolower($key)], array('string','date')) ) {
							if ( $col == 1 ) { 
								$chartData.="['".$value."'";
							}
							else {
								$chartData.=",'".$value."'";
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
					else {
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
		$this->HTML.="var options = {
title: '".$this->title."'
};\n";
		$this->HTML.="var chart = new google.visualization.LineChart(document.getElementById('chart_div".$index."'));
   chart.draw(data".$index.", options);
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
	public $parms;
	public $passedParms;
	public $nPassedParms;
	public $queries;
	public $charts;
	public $tables;
	public $formats;
	public $xml;
	public $HTML;
	public $title;
	public $description;
	public $csv;

	function __construct($name, $parms) {

		$this->reportName=$name;
		$this->csv=$this->reportName."_".date('Ymdhis').".csv";

        	// Parse the report
        	$xml =new \XML_Unserializer();
        	$xml->setOption(XML_UNSERIALIZER_OPTION_ATTRIBUTES_PARSE, TRUE);
        	$xml->unserialize(__ROOT__ ."/reports/".$this->reportName.".xml", TRUE);
        	if (\PEAR::isError($xml)) {
                        die("ERROR: XML : Report Name: ".$this->reportName." : MSG: " . $xml->getMessage());
        	}
		$this->xml=$xml->getUnserializedData();
	
		// Check if it has a query	
		if ( !isset($this->xml['query']) ) { 
                        die("ERROR: XML : Report Name: ".$this->reportName." : MSG: This report has no query");
		}
		else {
			foreach ( $this->xml as $key => $value ) {
				switch($key) {
				case 'query':
					$query = new Query($value);
					$query->setreportName($name);

					$table = new Table($value);
					$table->csv=$this->csv;
					$table->reportName=$this->reportName;

					$this->queries[$query->name] = $query;
					$this->tables[$table->name] = $table;
				break;
				case 'chart':
					$chart = new Chart($value);
					$this->charts[$chart->query] = $chart;
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
					$this->parms = new ReportParms($this->xml['parm']);
					$this->parms->setreportName($name);
				break;
				}
			}
		}

		foreach ( $this->tables as $key => $value ) {
			$value->formats=$this->formats;
		}

		foreach ( $this->charts as $key => $value ) {
			$value->formats=$this->formats;
		}


		//Check if the report has any parameters passed

		unset($parms['_report']);

		$this->passedParms=$parms;
		$this->nPassedParms = count($this->passedParms);

		foreach ( $this->queries as $index => $query ) {
			$query->parms = new QueryParms($parms);	
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

	function getNumberPassedParms() {
		return $this->nPassedParms;
	}

	function dump() {
		print_r($this->queries);
	}

	function setHeader() {
		require_once(__ROOT__.'/tpl/report_hdr_obj.tpl');
	}

	function setFooter() {
		require_once(__ROOT__.'/tpl/report_ftr_obj.tpl');
	}

	function formatValue($format, $value)
	{

	       	switch ($format) {
       		case 'number':
                	return number_format($value);
               	break;
      		case 'percent':
              		return number_format($value,2)."%";
      		break;
              	case 'percent(1)':
              		return number_format($value,1)."%";
              	break;
              	case 'percent(2)':
                     	return number_format($value,2)."%";
              	break;
               	case 'percent(3)':
                       	return number_format($value,3)."%";
               	break;
               	case 'decimal':
                       	return number_format($value,2,'.',',');
               	break;
               	case 'currency':
                       	return "$".number_format($value,2,'.',',');
               	break;
		default:
			return $value;
		}
       	}

	function toHTML() {

		foreach ( $this->queries as $index => $query ) {
			$results=$query->run();

			foreach ( $results as $index => $result ) {
                                if ( $result ) {
					$this->charts[$query->name]->setData($result, $index+1);
					$this->tables[$query->name]->setData($result);
                                }
                        }
		}

		// Now set all of the HTML
		$this->setHeader();
	
		foreach ( $this->charts as $name => $chart ) {
			$this->HTML.=$chart->HTML;
		}
		$this->HTML.="</script>\n</head>\n";
		$count=1;
		foreach ( $this->charts as $name => $chart ) {
			$this->HTML.="<div id='chart_div".$count."' style='width: 900px; height: 600px;'></div>\n";
			$count++;
		}
		

		foreach ( $this->tables as $name => $table ) {
			$this->HTML.=$table->HTML;
		}
		echo $this->HTML;
		$this->setFooter();
	}

	function setDB($db) {
		foreach ($this->queries as $index => $query ) {
			$query->setDB($db);
		}
		$this->parms->setDB($db);
	}

	function buildHTML($result) {
		
	}
}	

?>