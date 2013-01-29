<?php

///// Replace with Yahoo Finance stock quotes of your choice //////
$raw_stock_data = array('AAPL 200 205',
'BIDU 500 510','RIMM 150 155', 'SNDK 70 72',
'GOOG 500 510','MSFT 30 32','AMD 9 10');

sort($raw_stock_data);
//////////////////////////////////////////////////////////////////////////
?>
<div align="center">
<marquee bgcolor="#000000" direction="left" loop="20" width="75%">
<strong>
<?php
foreach ($raw_stock_data as $value) {
$results = explode(' ',$value);

$ticker = $results[0];
$buy = $results[1];
$sell = $results[2];

echo "<font color=\"#ffffff\">$ticker </font>";
echo "<font color=\"#00ff00\">$buy </font>";
echo "<font color=\"#ff0000\">$sell </font>";
echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
}
?>
</strong>
</marquee>
</div>
