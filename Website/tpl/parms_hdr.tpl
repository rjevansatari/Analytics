<?php
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/2.9.0/build/calendar/assets/skins/sam/calendar.css">
<script src="http://yui.yahooapis.com/2.9.0/build/yahoo-dom-event/yahoo-dom-event.js"></script>
<script src="http://yui.yahooapis.com/2.9.0/build/calendar/calendar-min.js"></script>
<script type="text/javascript" src="https://analytics.atari.com/js/cal.js"></script>
<style>
.formcent {
    position:absolute;
    top: 50%;
    left: 50%;
    width:500px;
    height:<?php echo $this->getHeight(); ?>px;
    margin-top: <?php echo $this->getTopMargin(); ?>px;   /*set to a negative number 1/2 of your height */
    margin-left:-250px;                                           /*set to a negative number 1/2 of your width*/
    border: 2px solid;
    background-color: silver;
    text-align: center;
}
</style>
    <link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/2.6.0/build/reset-fonts-grids/reset-fonts-grids.css"> 
    <link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/2.6.0/build/assets/skins/sam/skin.css">     
    <link rel="stylesheet" type="text/css" href="http://developer.yahoo.com/yui/assets/dpSyntaxHighlighter.css">
    <link rel="stylesheet" type="text/css" href="https://analytics.atari.com/css/report.css">
</head>
<body class="yui-skin-sam" >
<div id="davdoc" class="yui-t7">
<form class='formcent' name='parameters' action='report_run.php' method='get'>
<input name="_report" value="<?php echo $this->getReportName(); ?>" type="hidden">
<p>Please enter the following parameters:</p>
<table width='500px' class='parms' >
<?php
