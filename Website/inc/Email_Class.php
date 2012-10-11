<?php
// Parameter class
// 
//

include 'Mail.php';
include 'Mail/mime.php' ;

class Subscription
{

	public $attachment;
	public $message;

	function __construct() { 

	}

	function run($row) {

		$get_parms='';
		$parms = explode(',',$row['subscription_parms']);

		for ( $i=0; $i<count($parms); $i++ ) {
			$get_parms.="&".$parms[$i];
		}

		$url="localhost/report_run.php?_report=".$row['subscription_name'].$get_parms;
		$ch = curl_init();
            	curl_setopt($ch, CURLOPT_URL, $url);
            	curl_setopt($ch, CURLOPT_HEADER, TRUE);
            	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            	curl_setopt($ch, CURLOPT_USERPWD, 'analytics:cl62wRjxsIZ');
		$ts=date('Y-m-d H:i:s');
            	$html = curl_exec($ch);
            	$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	    	curl_close($ch); 
		return $ts;
	}

	function eMail($row) {

		$lastRun=$this->getLastReportRun($row['subscription_name']);
		$html=$lastRun['report_html'];

		// Remove google chart data
		$css=file_get_contents('/var/www/html/css/mail.css');
		$message="<html>\n<head>\n<style>\n$css\n</style>\n</head>\n";
		$html = str_replace("<td class='chart'>","<td>", $html);

		$sp1 = strpos($html, "<body>");
		$sp2 = strpos($html, "<form");
		$sp3 = strpos($html, "</form>");

		$message .= substr($html,$sp1,$sp2-$sp1);
		$message .= "<p><a href='https://analytics.atari.com/report_log.php?_report=".$row['subscription_name']."&cache=".$lastRun['report_startts']."'>View This In A Browser</a></p>";
		$message .= substr($html,$sp3+strlen('</form>'));
		
		// Now Email the results
		$crlf = "\n";
		$hdrs = array(
              	'Subject' => "Atari Analytics: Report: ".$row['subscription_name'].". Run completed successfully at ".$lastRun['report_endts'].".",
              );

		$mime = new Mail_mime(array('eol' => $crlf));

		$mime->setHTMLBody($message);
		$mime->addAttachment("/tmp/".$lastRun['report_csv'], 'text/plain');

		$body = $mime->get();
		$hdrs = $mime->headers($hdrs);

		$mail =& Mail::factory('mail');
		$mail->send($row['subscription_list'], $hdrs, $body);
	
	}

	function getLastReportRun($report) {

		$db=db_connect();

		$sql = "SELECT *
                        FROM reporting.report_log
                        WHERE report_name='".$report."'
                        AND report_code=0
                        AND report_endts IS NOT NULL
			AND date(report_startts)=curdate()
                        ORDER BY report_endts DESC
                        LIMIT 1";

                $result = run_sql($db, $sql);
                $row = $result[0]->fetch_assoc();

		mysqli_close($db);

		return $row;
	}
}	

?>
