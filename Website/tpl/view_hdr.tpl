<?php
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"> 
<html>
<head>
<title>Atari Analytics Portal</title>
    <script type="text/javascript" src="http://yui.yahooapis.com/2.6.0/build/utilities/utilities.js"></script> 
    <script type="text/javascript" src="http://yui.yahooapis.com/2.6.0/build/calendar/calendar-min.js"></script> 
    <script type="text/javascript" src="http://developer.yahoo.com/yui/assets/dpSyntaxHighlighter.js"></script>
    <script type="text/javascript" src="../js/cal.js"></script>
    <script type='text/javascript' src='https://www.google.com/jsapi'></script>
    <!-- GOOGLE CHART DATA --> 
<?php
	if ( isset($dailyStats) ) {
		echo $dailyStats['chart'];
	}
?>
    <link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/2.6.0/build/reset-fonts-grids/reset-fonts-grids.css"> 
    <link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/2.6.0/build/assets/skins/sam/skin.css">     
    <link rel="stylesheet" type="text/css" href="http://developer.yahoo.com/yui/assets/dpSyntaxHighlighter.css">
    <link rel="stylesheet" type="text/css" href="https://analytics.atari.com/css/report.css">
</head>
<body>
<table style='{width:100%}' id='header'>
<tr>
<td style='{width:2%}'>&nbsp;</td>
<td style='{width:18%}'>
<a href="report_view.php"><img class='report' width='150' height='168' src='images/Atari_NoBadge_NoURL_RGB.jpg' alt='Atari Stats Portal'/></a>
</td>
<td style='{width:2%}'>&nbsp;</td>
<td style='{width:78%}'>&nbsp;</td>
</tr>
<?php
