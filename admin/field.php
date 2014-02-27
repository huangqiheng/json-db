<?php
require_once 'functions.php';
denies_with_json();

$req = get_param();

switch($req['cmd']) {
    case 'combo2check': combobox_to_checkbox_exit($req);
    case 'combo2radio': combobox_to_radiobox_exit($req);
    case 'update_fields': update_fields_exit($req);
    default: jsonp_nocache_exit(['status'=>'error', 'error'=>'unknow command.']);
}

function update_fields_exit($req)
{
	$db_name = @$req['db_name'];
	$table_name = @$req['table_name'];
	$table_root = table_root($db_name, $table_name);
	$schema_file = "{$table_root}/schema.json";
	$schema = object_read($schema_file);

	$fields_req = @$req['fields'];
	$listview_req = @$req['listview'];
	$onebox_req = @$req['onebox'];
	$listview = $schema['listview'];

	$diff1 = array_diff($listview_req, $listview);
	$diff2 = array_diff($listview, $listview_req);
	$is_same = (empty($diff1) && empty($diff2));

	$schema['fields'] = $fields_req;
	$schema['listview'] = $listview_req;
	$schema['onebox'] = $onebox_req;
	object_save($schema_file, $schema);

	if (!$is_same) {
		refresh_listview($db_name, $table_name);
	}

	jsonp_nocache_exit(['status'=>'ok']); 
}

function combobox_to_radiobox_exit($req)
{
	$db_name = @$req['db_name'];
	$table_name = @$req['table_name'];
	$field = @$req['field'];

	if (all_empty([$db_name,$table_name,$field])) {
		jsonp_nocache_exit(['status'=>'error', 'error'=>'command parameter error.']);
	}

	$data_path = table_root($db_name, $table_name);
	$schema = object_read("{$data_path}/schema.json");
	$fields = $schema['fields'];

	$group_name = null;
	$field_type = null;
	foreach($fields as $group=>$items) {
		foreach($items as $name=>$value) {
			if ($name === $field) {
				$group_name = $group;
				$field_type = $value;
				break 2;
			}
		}
	}

	if (all_empty([$group_name,$field_type])) {
		jsonp_nocache_exit(['status'=>'error', 'error'=>'command parameter field name error.']);
	}

	if ($field_type !== 'jqxComboBox') {
		jsonp_nocache_exit(['status'=>'error', 'error'=>'the target field not a combobox.']);
	}

	$schema['fields'][$group_name][$field] = 'jqxRadioButton';
	object_save("{$data_path}/schema.json", $schema);

	$counter = refresh_listview($db_name, $table_name);
	jsonp_nocache_exit(['status'=>'ok', 'counter'=>$counter]); 
}


function combobox_to_checkbox_exit($req)
{
	$db_name = @$req['db_name'];
	$table_name = @$req['table_name'];
	$field = @$req['field'];
	$seperator = @$req['seperator'];

	if (all_empty([$db_name,$table_name,$field,$seperator])) {
		jsonp_nocache_exit(['status'=>'error', 'error'=>'command parameter error.']);
	}

	$data_path = table_root($db_name, $table_name);
	$schema = object_read("{$data_path}/schema.json");
	$fields = $schema['fields'];

	$group_name = null;
	$field_type = null;
	foreach($fields as $group=>$items) {
		foreach($items as $name=>$value) {
			if ($name === $field) {
				$group_name = $group;
				$field_type = $value;
				break 2;
			}
		}
	}

	if (all_empty([$group_name,$field_type])) {
		jsonp_nocache_exit(['status'=>'error', 'error'=>'command parameter field name error.']);
	}

	if ($field_type !== 'jqxComboBox') {
		jsonp_nocache_exit(['status'=>'error', 'error'=>'the target field not a combobox.']);
	}

	$schema['fields'][$group_name][$field] = 'jqxCheckBox';
	object_save("{$data_path}/schema.json", $schema);

	foreach(glob("{$data_path}/*.json") as $file) {
		if (is_dir($file)) {continue;}
		if (!preg_match('~/(\d+)\.json$~',$file, $matches)){continue;}
		$item_id = $matches[1];

		$data_obj = object_read($file);
		if (empty($data_obj)) {continue;}

		$combo_str = @$data_obj[$group_name][$field];
		if (empty($combo_str)) {continue;}

		$check_arr = explode($seperator, $combo_str);
		$data_obj[$group_name][$field] = $check_arr;

		object_save($file, $data_obj);
	}

	$counter = refresh_listview($db_name, $table_name);
	jsonp_nocache_exit(['status'=>'ok', 'counter'=>$counter]); 
}

function all_empty($array)
{
	foreach($array as $item){
		if (empty($item)) {
			return true;
		}
	}
	return false;
}

