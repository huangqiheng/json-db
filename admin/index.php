<?php
require_once 'functions.php';

$user_info = denies_with_redirect();
$user_email = $user_info['user_email'];

/*
if (!preg_match('|@appgame\.com$|i', $user_email)) {
	header('Location: /index.php');
	exit();
}
*/

header('Content-Type: text/html; charset=utf-8');

$db_captions = get_db_captions();
$table_captions = get_table_captions(); 
$init_db = get_param('db', 'default');
$init_table = get_param('table', 'default');

?>
<html><head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<link rel="shortcut icon" type="image/ico" href="images/favicon.ico" />
<title>JsonDB</title>


<!--加载jquery-->
<script type="text/javascript" language="javascript" src="client/jquery/jquery-1.11.1.min.js"></script>
<script type="text/javascript" language="javascript" src="client/jquery/md5.min.js"></script>
<script type="text/javascript" language="javascript" src="client/jquery/jquery.fileDownload.js"></script>
<script type="text/javascript" language="javascript" src="client/contextMenu/jquery.contextMenu.js"></script>
<script type="text/javascript" language="javascript" src="client/contextMenu/jquery.ui.position.js"></script>


<!--加载gritter-->
<link rel="stylesheet" href="client/gritter/jquery.gritter.css" type="text/css" />
<script type="text/javascript" language="javascript" src="client/gritter/jquery.gritter.min.js"></script>

<!--加载jqwidgets-->
<link rel="stylesheet" href="client/jqwidgets/styles/jqx.base.css" type="text/css" />
<script type="text/javascript" language="javascript" src="client/jqwidgets/jqx-all.js"></script> 
<script type="text/javascript" language="javascript" src="client/jqwidgets/globalization/globalize.js"></script>
<script type="text/javascript" language="javascript" src="client/jqwidgets/globalization/globalize.culture.zh-CN.js"></script> 

<!--加载datatables-->
<style type="text/css" title="currentStyle">
	@import "client/datatables/jquery.dataTables.min.css";
	@import "client/datatables/dataTables.tableTools.min.css";
	@import "client/datatables/dataTables.responsive.css";
	@import "client/datatables/dataTables.jqueryui.css";
	@import "client/datatables/jquery-ui.css";
	@import "client/contextMenu/jquery.contextMenu.css";

	@import "client/fixed.css";
</style>
<script type="text/javascript" language="javascript" src="client/datatables/jquery.dataTables.min.js"></script>
<script type="text/javascript" language="javascript" src="client/datatables/dataTables.tableTools.min.js"></script>
<script type="text/javascript" language="javascript" src="client/datatables/dataTables.scroller.min.js"></script>
<script type="text/javascript" language="javascript" src="client/datatables/dataTables.pagination.input.js"></script>
<script type="text/javascript" language="javascript" src="client/datatables/dataTables.responsive.min.js"></script>
<script type="text/javascript" language="javascript" src="client/datatables/dataTables.jqueryui.js"></script>

<!--加载自己的js-->
<script type="text/javascript" language="javascript" src="client/functions.js"></script>
<script type="text/javascript" language="javascript" src="client/navi-bar.js"></script>
<script type="text/javascript" language="javascript" src="client/listview_table.js"></script>
<script type="text/javascript" language="javascript" src="client/schema-window.js"></script>
<script type="text/javascript" language="javascript" src="client/fields-window.js"></script>
<script type="text/javascript" language="javascript" src="client/data-window.js"></script>
<script type="text/javascript" language="javascript" src="client/language.js"></script>

<!--动态生成的js变量-->
<script type="text/javascript" charset="utf-8">
window.env = {};
set_language('cn');

var match = window.location.href.match(/^((https?\:\/\/[^\/]+)\/.*)\/admin\/index\.php$/);
if (match === null) {
	env.is_subpath = false;
	match = window.location.href.match(/^((https?\:\/\/[^\/]+))\/admin\/index\.php$/);
} else {
	env.is_subpath = true;
}
env.web_root = match[1];
env.www_root = match[2];
env.init_db = "<?php echo $init_db; ?>";
env.init_table = "<?php echo $init_table; ?>";
env.port_mode = <?php echo PORT_MODE?'true':'false'; ?>;

if ((env.init_db === 'default') && (env.init_table === 'default')) {
	env.init_db = get_cookie('init_db');
	env.init_table = get_cookie('init_table');
}

env.username = "<?php echo $user_info['user_name']; ?>";
env.logout = "<?php echo login_wrap_referer($user_info['logout'],'/index.php'); ?>";

env.field_types = <?php echo json_encode($field_types); ?>;

env.db_captions = <?php echo json_encode($db_captions); ?>;
env.table_captions = <?php echo json_encode($table_captions); ?>;
init_db_captions();
for (var db_name in env.table_captions) {init_table_captions(db_name);}

env.db_index = get_index(env.db_captions, env.init_db);
env.table_index = get_index(env.table_captions[env.init_db], env.init_table);
env.db_last_unselect = env.db_index;
env.table_last_unselect = env.table_index;
env.popup = function (title, content){$.gritter.add({title: title, text: content});};
env.db_cmd_count = 4;
env.table_cmd_count = 4;
env.last_refresh_time = 0;

var nav_btn_width = 30;
var nav_btn_height = 25;

$(document).ready(function(){
	$.ajaxSetup({ cache: false });
	build_navi_bar();
	trigger_refresh();
});


$(window).resize(function () {
	if (window.fixedheader !== undefined) {
		fixedheader._fnUpdateClones(true); // force redraw
		fixedheader._fnUpdatePositions();
	}
});


</script>
</head>
<body background="images/bg_tile.jpg">
<table style="width:100%; font-size:12px;">
<tr id="navibar_row"><td>
	<div id="db_captions" style="float:left;"></div>
	<div id="table_captions" style="float:left;"></div>
	<div id="config_btn" style="float:left; padding:0px"><img height="25" width="25" src="images/setting.png"/></div>
	<div id="refresh_btn" style="float:left; padding:0px"><img height="25" width="25" src="images/refresh.png"/></div>
	<div id="custom_btns" style="float:left; padding:0px;">
	</div>
	<div id="user_info" style="float:right; padding:4px; color: white;"></div>
</td></tr><tr><td>
	<div id="listview" style="width:100%; background-color: rgb(180, 180, 180);"></div>
</td></tr>
</table>
</body></html>
