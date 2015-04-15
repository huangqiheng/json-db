<?php
require_once '../../admin/functions.php';

$req = get_param();
$db_name = @$req['db_name'];
$db_name || $db_name = 'default';
$table_name = @$req['table_name'];
$table_name || $table_name = 'default';

$data = @$req['data'];

if (empty($data)) {
	jsonp_nocache_exit(array('status'=>'error', 'error'=>'input data is empty'));
}

$table_root = table_root($db_name, $table_name);
$schema = object_read("{$table_root}/schema.json");
$mapper = object_read("{$table_root}/mapper.json");

if (empty($schema)) {
	jsonp_nocache_exit(array('status'=>'error', 'error'=>'schema file error']);
}

if (!api_valid($db_name, $table_name, @$req['apikey'])) {
	jsonp_nocache_exit(array('status'=>'error', 'error'=>'api key error'));
}

$map_key = mapper_key(@$req['mapper']);
if (empty($map_key)) {
	jsonp_nocache_exit(array('status'=>'error', 'error'=>'not mapper in parameter'));
}

$is_new_data = true;
do {
	if (empty($mapper)) break;

	$map_val = @$mapper[$map_key];
	if(empty($map_val)) break;

	$map_file = "{$table_root}/{$map_val}.json";
	if (!file_exists($map_file)) break;

	$ori_data = object_read($map_file);
	if (empty($ori_data)) break;

	if (!set_data_id($data, $map_val)) {
		jsonp_nocache_exit(array('status'=>'error', 'error'=>'no id field'));
	}
	$is_new_data = false;
} while(false);

if ($is_new_data) {
	$output = create_new_data($db_name, $table_name, $data);
} else {
	$output = update_current_data($db_name, $table_name, $data);
}

jsonp_nocache_exit($output); 
