<?php
require_once '../../admin/functions.php';

$req = get_param();
$db_name = @$req['db_name'];
$db_name || $db_name = 'default';
$table_name = @$req['table_name'];
$table_name || $table_name = 'default';
$data = @$req['data'];
$data || $data = [];

$table_root = table_root($db_name, $table_name);
$schema = object_read("{$table_root}/schema.json");
$mapper = object_read("{$table_root}/mapper.json");

if (empty($data)) {
	jsonp_nocache_exit(['status'=>'error', 'error'=>'input data is empty']);
}

if (empty($schema)) {
	jsonp_nocache_exit(['status'=>'error', 'error'=>'schema file error']);
}

$map_key = @$req['mapper'];
if (empty($map_key)) {
	jsonp_nocache_exit(['status'=>'error', 'error'=>'not mapper in parameter']);
}
$map_key= mapper_key($map_key);

$is_new_data = false;
if (empty($mapper)) {
	$is_new_data = true;
} else {
	$map_val = @$mapper[$map_key];
	if(empty($map_val)) {
		$is_new_data = true;
	} else {
		$map_file = "{$table_root}/{$map_val}.json";
		if (!file_exists($map_file)) {
			$is_new_data = true;
		} else {
			$ori_data = object_read($map_file);
			if (empty($ori_data)) {
				$is_new_data = true;
			}
			if (!set_data_id($data, $map_val)) {
				jsonp_nocache_exit(['status'=>'error', 'error'=>'no id field']);
			}
		}
	}
}

if ($is_new_data) {
	$output = create_new_data($db_name, $table_name, $data);
} else {
	$output = update_current_data($db_name, $table_name, $data);
}

jsonp_nocache_exit($output); 
