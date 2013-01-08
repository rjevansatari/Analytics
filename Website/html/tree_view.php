<?php
	ini_set('memory_limit', '128M');
	//ini_set("display_errors", "1");
	error_reporting(E_ALL);
	date_default_timezone_set('America/Los_Angeles');

	define('__ROOT__', dirname(dirname(__FILE__))); 
	define('__DEBUG__', TRUE);

	// Config
	require_once (__ROOT__.'/inc/db.php');
	require_once (__ROOT__.'/inc/config.php');

	// Include the report class
	require_once (__ROOT__.'/inc/Tree_Menu.php');

	// Connect to the DB
	$db = db_connect();
	// Build the menu
	require_once(__ROOT__.'/tpl/menu_hdr.tpl');
	$menu = new Menu($db);
?>
<script type="text/javascript">
var tree1;

(function() {
	var treeInit = function() {
		tree1 = new YAHOO.widget.TreeView("treeDiv1");
		tree1.render();
	};

	//Add an onDOMReady handler to build the tree when the document is ready
    	YAHOO.util.Event.onDOMReady(treeInit);
})();
</script>
<?php
	require_once(__ROOT__.'/tpl/view_ftr.tpl');
?>
