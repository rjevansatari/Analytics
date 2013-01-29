<?php
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"> 
<html>
<head>
<title>Atari Analytics Portal</title>
<link type="text/css" rel="stylesheet" href="http://yui.yahooapis.com/2.9.0/build/treeview/assets/skins/sam/treeview.css">
<script src="http://yui.yahooapis.com/3.8.0/build/yui/yui-min.js"></script>
<script src="http://yui.yahooapis.com/2.9.0/build/yahoo-dom-event/yahoo-dom-event.js" ></script>
<script src="http://yui.yahooapis.com/2.9.0/build/animation/animation-min.js" ></script>
<script src="http://yui.yahooapis.com/2.9.0/build/json/json-min.js" ></script>
<script src="http://yui.yahooapis.com/2.9.0/build/treeview/treeview-min.js" ></script>
<script src="http://analytics.atari.com/js/tree.js"></script>
<script src='https://www.google.com/jsapi'></script>
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
