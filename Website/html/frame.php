<?php
	ini_set('memory_limit', '128M');
	//ini_set("display_errors", "1");
	error_reporting(E_ALL);
	date_default_timezone_set('America/Los_Angeles');

	define('__ROOT__', dirname(dirname(__FILE__))); 
	define('__DEBUG__', TRUE);

	// These are default values if none passed
	$report='report_view_new.php';

	if ( isset($_GET['_report']) ) {
		$report=$report . "?_report=" . $_GET['_report'];
	};
	if ( isset($_GET['_cache']) ) {
		$report=$report . "&_cache=" . $_GET['_cache'];
	};
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"> 
<html>
<head>
<title>Atari Analytics Portal</title>
<link type="text/css" rel="stylesheet" href="http://yui.yahooapis.com/2.9.0/build/treeview/assets/skins/sam/treeview.css">
<script src="http://yui.yahooapis.com/2.9.0/build/yahoo-dom-event/yahoo-dom-event.js" ></script>
<script src="http://yui.yahooapis.com/2.9.0/build/animation/animation-min.js" ></script>
<script src="http://yui.yahooapis.com/2.9.0/build/json/json-min.js" ></script>
<script src="http://yui.yahooapis.com/2.9.0/build/treeview/treeview-min.js" ></script>
<script src="http://analytics.atari.com/js/tree.js"></script>
<script src='https://www.google.com/jsapi'></script>
<link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/2.6.0/build/reset-fonts-grids/reset-fonts-grids.css"> 
<link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/2.6.0/build/assets/skins/sam/skin.css">     
<link rel="stylesheet" type="text/css" href="http://developer.yahoo.com/yui/assets/dpSyntaxHighlighter.css">
<link rel="stylesheet" type="text/css" href="https://analytics.atari.com/css/report.css">
</head>
<frameset rows="100,*" frameborder="0" border="0" framespacing="0">
  <frame name="topNav" src="top_nav.html">
<frameset cols="200,*" frameborder="0" border="0" framespacing="0">
	<frame frameborder="1" name="menu" src="tree_view.php" marginheight="0" marginwidth="0" scrolling="auto" noresize>
	<frame frameborder="1" name="content" src="<?php echo $report; ?>" marginheight="0" marginwidth="0" scrolling="auto" noresize>
<noframes>
<p>Your browser does not support frames. :(</p>
</noframes>
</frameset>
</frameset>
</html>
