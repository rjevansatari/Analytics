var tree;
function treeInit() {
    tree = new YAHOO.widget.TreeView("treeDiv");
    tree.render();
}
YAHOO.util.Event.onDOMReady(treeInit);
