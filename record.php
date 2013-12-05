<?php
require_once 'functions.php';

header('Content-Type: text/html; charset=utf-8');

list($database,$table,$id) = get_selected_db();
$web_root = "http://{$_SERVER['SERVER_NAME']}";
$target = json_file("{$id}.json");
$schema = json_file('schema.json');
$options = json_file('valid-options.json');
$is_new = empty($target);

?>
<html><head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<link rel="shortcut icon" type="image/ico" href="<?php echo $web_root; ?>/images/favicon.ico" />
<title>JsonDB</title>

<!--加载css-->
<link rel="stylesheet" href="jqwidgets/styles/jqx.base.css" type="text/css" />

<!--加载js-->
<script type="text/javascript" language="javascript" src="js/jquery-2.0.3.min.js"></script>
<script type="text/javascript" language="javascript" src="js/md5.min.js"></script>

<script type="text/javascript" language="javascript" src="jqwidgets/jqx-all.js"></script> 
<script type="text/javascript" language="javascript" src="jqwidgets/globalization/globalize.js"></script>
<script type="text/javascript" language="javascript" src="jqwidgets/globalization/globalize.culture.zh-CN.js"></script> 
<script type="text/javascript" language="javascript" src="js/json-db-lib.js"></script>

<!--动态生成的js变量-->
<script type="text/javascript" charset="utf-8">
window.env = {};
env.web_root = "<?php echo $web_root; ?>";
env.database = "<?php echo $database ?>";
env.table = "<?php echo $table ?>";
env.id = <?php echo $id; ?>;
env.is_new = <?php echo var_export($is_new, true); ?>;
env.target = <?php echo json_encode($target); ?>;
env.schema = <?php echo json_encode($schema); ?>;
env.options = <?php echo json_encode($options); ?>;
$(document).ready(ui_main);

function ui_main() 
{
	var fields = env.schema.fields;
	env.container = 'tab_container';
	init_tab(fields, env.container);

	for (var tab_name in fields) {
		var val_obj = fields[tab_name];
		var tabitem_id = get_tabitem_id(env.container, tab_name);
		var table_id = tabitem_id+'_table';
		for (var field_name in val_obj) {
			var field_value = val_obj[field_name];
			var field_id = get_tableitem_id(tab_name, field_name);
			var p = [tabitem_id, table_id, field_id, field_name];
			var init_val = undefined;
			var option_val = undefined;

			if (field_value === 'jqxInput-id') {
				addItem_jqxInput_id(p, env.id);
			}
			if (field_value === 'jqxInput') {
				addItem_jqxInput(p, init_val);
			}
			if (field_value === 'jqxInput-text') {
				addItem_jqxInput_text(p, init_val);
			}
			if (field_value === 'jqxInput-name') {
				addItem_jqxInput_name(p, init_val);
			}
			if (field_value === 'jqxListBox') {
				addItem_jqxListBox(p, init_val);
			}
			if (field_value === 'jqxListBox-name') {
				addItem_jqxListBox_name(p, init_val);
			}
			if (field_value === 'jqxListBox-onebox') {
				addItem_jqxListBox(p, init_val, render_onebox, 250);
			}
			if (field_value === 'jqxListBox-images') {
				addItem_jqxListBox_images(p, init_val);
			}
			if (field_value === 'jqxComboBox') {
				addItem_jqxComboBox(p, init_val, option_val);
			}
			if (field_value === 'jqxNumberInput-price') {
				addItem_jqxNumberInput(p, init_val, 2);
			}
			if (field_value === 'jqxNumberInput-size') {
				addItem_jqxNumberInput(p, init_val, 0, ' KB');
			}
			if (field_value === 'jqxDateTimeInput') {
				addItem_jqxDateTimeInput(p, init_val);
			}
		}
	}
};

</script>
</head>
<body background="images/bg_tile.jpg">
<div id="tab_container" style="margin:auto;"></div>
</body></html>

