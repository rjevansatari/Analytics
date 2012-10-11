<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head><title>Atari Analytics Portal</title>
<meta http-equiv="Content-Type" content="application/xhtml+xml; charset=iso-8859-1" />
<meta name="Author" content="Rick Evans" />
<meta name="Description" content="Atari's strategic analytics portal" />
<meta name="KeyWords" content="Atari, analytics" />
<script type="text/javascript" src="http://yui.yahooapis.com/2.6.0/build/utilities/utilities.js"></script> 
<script type="text/javascript" src="http://yui.yahooapis.com/2.6.0/build/calendar/calendar-min.js"></script> 
<script type="text/javascript" src="http://developer.yahoo.com/yui/assets/dpSyntaxHighlighter.js"></script>
<script type="text/javascript" src="../js/cal.js"></script>
<script type='text/javascript' src='https://www.google.com/jsapi'></script>
<link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/2.6.0/build/reset-fonts-grids/reset-fonts-grids.css"> 
<link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/2.6.0/build/assets/skins/sam/skin.css">     
<link rel="stylesheet" type="text/css" href="http://developer.yahoo.com/yui/assets/dpSyntaxHighlighter.css">
<link rel="stylesheet" type="text/css" href="https://analytics.atari.com/css/report.css">
</head>
<frameset rows="20%, 80%">
        <frame src="/menu_hdr.html" name="title" scrolling="no" frameborder="0" />
        <frameset cols="20%, 80%">
                <frame src="<?php echo $navHtml; ?>" name="menu" scrolling="no" frameborder="1" />
                <frame src="<?php echo $reportHtml; ?>" name="main" scrolling="yes" frameborder="0" />
        </frameset>
        <noframes>
                <body>
                        <p>This document is contained within a frameset. Your web browser does not support frames, please upgrade to more recent version of
                        your browser.</p>
                </body>
        </noframes>
</frameset>
</html>

